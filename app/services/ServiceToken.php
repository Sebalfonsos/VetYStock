<?php

class ServiceToken
{
    public static function issue(array $claims, ?int $ttlSeconds = 3600): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => config('app.name'),
            'iat' => $now,
            'exp' => $ttlSeconds ? $now + $ttlSeconds : null,
        ], $claims);

        $encodedPayload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', $encodedPayload, (string) config('app.service_secret'), true);

        return $encodedPayload . '.' . self::base64UrlEncode($signature);
    }

    public static function validate(string $token): ?array
    {
        [$encodedPayload, $encodedSignature] = array_pad(explode('.', $token, 2), 2, '');
        if ($encodedPayload === '' || $encodedSignature === '') {
            return null;
        }

        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', $encodedPayload, (string) config('app.service_secret'), true));
        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return null;
        }

        $payload = json_decode((string) self::base64UrlDecode($encodedPayload), true);
        if (!is_array($payload)) {
            return null;
        }

        if (($payload['exp'] ?? null) !== null && time() > (int) $payload['exp']) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
