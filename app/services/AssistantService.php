<?php

class AssistantService
{
    private const MAX_HISTORY = 10;
    private const MAX_MESSAGE_LENGTH = 2000;

    private const DISCLAIMER = "Las recomendaciones presentadas son únicamente sugerencias basadas en la información que nos has proporcionado y no constituyen un diagnóstico profesional. Cada animal puede presentar condiciones particulares que requieren una evaluación específica.\n\n"
        . "Para obtener una valoración adecuada y recomendaciones más precisas, te recomendamos consultar con un médico veterinario o especialista en salud animal.\n\n"
        . "Si deseas mayor información o asesoría personalizada, puedes comunicarte al número: +57 311 4291601.";

    private const ANIMAL_GROUPS = [
        'domestic' => 'mascotas / domésticos',
        'farm' => 'granja',
        'wild' => 'fauna silvestre',
    ];

    // $onEvent recibe ('thinking'|'delta', $texto) mientras el modelo genera.
    public function chat(array $input, ?callable $onEvent = null): array
    {
        $messages = $this->cleanMessages($input['messages'] ?? []);
        if (!$messages || end($messages)['role'] !== 'user') {
            return ['success' => false, 'message' => 'Escribe tu consulta.'];
        }

        $catalog = (new MicroserviceClient())->get('catalog', 'products');
        if (!($catalog['success'] ?? false)) {
            return ['success' => false, 'message' => 'El catálogo no está disponible en este momento. Intenta más tarde.'];
        }

        $products = array_values(array_filter(
            $catalog['data'] ?? [],
            fn (array $product) => ($product['status'] ?? 'active') === 'active'
        ));

        $reply = $this->complete(array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt($products)]],
            $messages
        ), $onEvent);

        if ($reply === null) {
            return ['success' => false, 'message' => 'El asistente no está disponible. Verifica que LM Studio esté encendido con el modelo cargado.'];
        }

        $mentioned = $this->mentionedProducts($reply, $products);

        Logger::audit('consultation', 'assistant', null, [
            'question' => mb_substr(end($messages)['content'], 0, 500),
            'products' => array_column($mentioned, 'name'),
        ]);

        $final = $this->ensureDisclaimer($reply, $mentioned);
        if ($onEvent && $final !== $reply) {
            $onEvent('delta', substr($final, strlen($reply)));
        }

        return [
            'success' => true,
            'data' => [
                'reply' => $final,
                'products' => $mentioned,
            ],
        ];
    }

    // Los modelos locales pequeños a veces omiten el aviso; si hay recomendación, se garantiza aquí.
    private function ensureDisclaimer(string $reply, array $mentioned): string
    {
        $isRecommendation = $mentioned
            || stripos($reply, 'Productos recomendados') !== false
            || stripos($reply, 'Recomendación adicional') !== false;

        if (!$isRecommendation || str_contains($reply, '4291601')) {
            return $reply;
        }

        return $reply . "\n\n" . self::DISCLAIMER;
    }

    private function cleanMessages(mixed $messages): array
    {
        if (!is_array($messages)) {
            return [];
        }

        $clean = [];
        foreach ($messages as $message) {
            $role = $message['role'] ?? null;
            $content = trim((string) ($message['content'] ?? ''));
            if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
                continue;
            }
            $clean[] = ['role' => $role, 'content' => mb_substr($content, 0, self::MAX_MESSAGE_LENGTH)];
        }

        return array_slice($clean, -self::MAX_HISTORY);
    }

    private function systemPrompt(array $products): string
    {
        $lines = array_map(fn (array $product) => sprintf(
            '- %s (categoría: %s; para: %s; precio: $%s; disponibles: %d) — %s',
            $product['name'],
            $product['category_name'] ?? '',
            self::ANIMAL_GROUPS[$product['animal_group'] ?? ''] ?? '',
            number_format((float) ($product['sale_price'] ?? 0), 0, ',', '.'),
            (int) ($product['current_stock'] ?? 0),
            $product['description'] ?? ''
        ), $products);

        return file_get_contents(__DIR__ . '/../../services/assistant/prompt.md')
            . "\n\n## Catálogo disponible (datos reales del inventario en este momento)\n\n"
            . "Estos son los ÚNICOS productos que existen. Escribe su nombre exactamente como aparece aquí. "
            . "Los productos de los ejemplos anteriores solo sirven como guía de formato. "
            . "Si un producto tiene 0 disponibles, indica que no está disponible por ahora. "
            . "En tu respuesta escribe solo el nombre del producto y, debajo, el motivo con tus palabras; "
            . "no copies la categoría, el precio ni las unidades, porque el sistema los muestra aparte.\n\n"
            . implode("\n", $lines);
    }

    private function complete(array $messages, ?callable $onEvent): ?string
    {
        $content = '';
        $pending = '';
        $thinking = false;

        // Siempre se pide en streaming; si nadie escucha los eventos, simplemente se acumula el texto.
        $handleLine = function (string $line) use (&$content, &$thinking, $onEvent): void {
            if (!str_starts_with($line, 'data:')) {
                return;
            }
            $delta = json_decode(trim(substr($line, 5)), true)['choices'][0]['delta'] ?? [];

            if (($delta['content'] ?? '') !== '') {
                $content .= $delta['content'];
                $onEvent && $onEvent('delta', $delta['content']);
            } elseif (!$thinking && (($delta['reasoning'] ?? '') !== '' || ($delta['reasoning_content'] ?? '') !== '')) {
                $thinking = true;
                $onEvent && $onEvent('thinking', '');
            }
        };

        $curl = curl_init(rtrim((string) config('app.llm.base_url'), '/') . '/chat/completions');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: text/event-stream'],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => config('app.llm.model'),
                'messages' => $messages,
                'temperature' => 0.3,
                'max_tokens' => 2500,
                'stream' => true,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => (int) config('app.llm.timeout', 180),
            CURLOPT_WRITEFUNCTION => function ($curl, string $chunk) use (&$pending, $handleLine): int {
                $pending .= $chunk;
                while (($position = strpos($pending, "\n")) !== false) {
                    $handleLine(trim(substr($pending, 0, $position)));
                    $pending = substr($pending, $position + 1);
                }
                return strlen($chunk);
            },
        ]);

        $ok = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if (!$ok || $status !== 200) {
            return null;
        }

        return trim($content) !== '' ? trim($content) : null;
    }

    private function mentionedProducts(string $reply, array $products): array
    {
        $text = $this->normalize($reply);
        $found = [];
        $claimed = [];

        // Los nombres largos van primero para que "Unguento cicatrizante fauna" no marque también "Unguento Cicatrizante".
        usort($products, fn (array $a, array $b) => strlen($b['name']) <=> strlen($a['name']));

        foreach ($products as $product) {
            $name = $this->normalize($product['name']);
            $offset = 0;
            while (($position = strpos($text, $name, $offset)) !== false) {
                $end = $position + strlen($name);
                foreach ($claimed as [$start, $stop]) {
                    if ($position < $stop && $end > $start) {
                        $offset = $position + 1;
                        continue 2;
                    }
                }
                break;
            }

            if ($position !== false) {
                $claimed[] = [$position, $position + strlen($name)];
                $found[$position] = [
                    'id' => (int) $product['id'],
                    'name' => $product['name'],
                    'category' => $product['category_name'] ?? '',
                    'price' => (float) ($product['sale_price'] ?? 0),
                    'stock' => (int) ($product['current_stock'] ?? 0),
                ];
            }
        }

        ksort($found);
        return array_values($found);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        return strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }
}
