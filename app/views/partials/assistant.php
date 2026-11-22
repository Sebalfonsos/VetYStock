<style>
    #assistant-toggle { position: fixed; right: 1.5rem; bottom: 1.5rem; z-index: 1080; width: 3.5rem; height: 3.5rem; }
    #assistant-panel { position: fixed; right: 1.5rem; bottom: 6rem; z-index: 1080; width: min(26rem, calc(100vw - 2rem)); height: min(36rem, calc(100vh - 8rem)); flex-direction: column; }
    #assistant-panel:not([hidden]) { display: flex; }
    #assistant-messages { overflow-y: auto; }
    .assistant-msg { max-width: 88%; white-space: pre-wrap; word-wrap: break-word; }
    .assistant-msg.user { margin-left: auto; }
</style>

<button id="assistant-toggle" type="button" class="btn btn-success rounded-circle shadow" aria-controls="assistant-panel" aria-expanded="false" title="Asistente veterinario">
    <i class="bi bi-chat-dots fs-4"></i>
</button>

<div id="assistant-panel" class="card shadow-lg" hidden>
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <strong>Asistente veterinario</strong>
            <div class="small text-muted">Recomienda productos de nuestro catálogo</div>
        </div>
        <div>
            <button id="assistant-reset" type="button" class="btn btn-sm btn-outline-secondary" title="Nueva conversación"><i class="bi bi-arrow-counterclockwise"></i></button>
            <button id="assistant-close" type="button" class="btn btn-sm btn-outline-secondary" title="Cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
    </div>
    <div id="assistant-messages" class="card-body flex-grow-1 d-flex flex-column gap-2"></div>
    <form id="assistant-form" class="card-footer d-flex gap-2">
        <input id="assistant-input" class="form-control" maxlength="2000" autocomplete="off" placeholder="Describe qué le pasa a tu animal..." required>
        <button id="assistant-send" class="btn btn-success" type="submit" title="Enviar"><i class="bi bi-send"></i></button>
    </form>
</div>

<script>
(() => {
    const endpoint = <?= json_encode(url('assistant/chat')) ?>;
    const csrf = <?= json_encode(Csrf::token()) ?>;
    const storageKey = 'vetystock-assistant';
    const greeting = 'Hola, cuéntame qué le pasa a tu animal (qué animal es y qué síntomas notas) y te sugiero productos de nuestro catálogo.';

    const panel = document.getElementById('assistant-panel');
    const toggle = document.getElementById('assistant-toggle');
    const list = document.getElementById('assistant-messages');
    const form = document.getElementById('assistant-form');
    const input = document.getElementById('assistant-input');
    const send = document.getElementById('assistant-send');

    let history = [];
    try { history = JSON.parse(sessionStorage.getItem(storageKey) || '[]'); } catch (e) { history = []; }

    const save = () => { try { sessionStorage.setItem(storageKey, JSON.stringify(history)); } catch (e) {} };

    const escapeHtml = (text) => text.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const format = (text) => escapeHtml(text).replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/^#{1,4}\s*/gm, '');
    const money = (value) => '$' + Math.round(value).toLocaleString('es-CO');

    function bubble(role, text, products = [], isError = false) {
        const div = document.createElement('div');
        div.className = 'assistant-msg rounded-3 px-3 py-2 ' + (role === 'user' ? 'user bg-success text-white' : (isError ? 'bg-warning-subtle' : 'bg-light border'));
        div.innerHTML = format(text);

        if (products.length) {
            const box = document.createElement('div');
            box.className = 'mt-2 pt-2 border-top small';
            box.style.whiteSpace = 'normal';
            box.innerHTML = '<div class="fw-semibold mb-1">Disponible en el catálogo</div>' + products.map((p) =>
                '<div class="d-flex justify-content-between gap-2"><span>' + escapeHtml(p.name) + '</span>'
                + '<span class="text-nowrap ' + (p.stock > 0 ? 'text-success' : 'text-danger') + '">'
                + (p.stock > 0 ? p.stock + ' und · ' + money(p.price) : 'Agotado') + '</span></div>'
            ).join('');
            div.appendChild(box);
        }

        list.appendChild(div);
        list.scrollTop = list.scrollHeight;
        return div;
    }

    function renderAll() {
        list.innerHTML = '';
        bubble('assistant', greeting);
        history.forEach((m) => bubble(m.role, m.content, m.products || []));
    }

    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) { input.focus(); list.scrollTop = list.scrollHeight; }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    document.getElementById('assistant-close').addEventListener('click', () => setOpen(false));
    document.getElementById('assistant-reset').addEventListener('click', () => { history = []; save(); renderAll(); input.focus(); });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        history.push({ role: 'user', content: text });
        save();
        bubble('user', text);
        input.value = '';
        input.disabled = send.disabled = true;
        const typing = bubble('assistant', 'Revisando el catálogo...');

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ messages: history.map(({ role, content }) => ({ role, content })) }),
            });
            const result = await response.json();
            typing.remove();

            if (result.success) {
                const products = result.data.products || [];
                history.push({ role: 'assistant', content: result.data.reply, products });
                save();
                bubble('assistant', result.data.reply, products);
            } else {
                history.pop();
                save();
                bubble('assistant', result.message || 'No se pudo obtener respuesta.', [], true);
                input.value = text;
            }
        } catch (error) {
            typing.remove();
            history.pop();
            save();
            bubble('assistant', 'No se pudo conectar con el asistente.', [], true);
            input.value = text;
        } finally {
            input.disabled = send.disabled = false;
            input.focus();
        }
    });

    renderAll();
})();
</script>
