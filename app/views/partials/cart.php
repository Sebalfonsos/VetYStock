<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090">
    <div id="cart-toast" class="toast align-items-center border-0" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>

<script>
// Carrito compartido por la tienda y el asistente: agrega sin recargar y actualiza el contador del menú.
window.VetCart = (() => {
    const csrf = <?= json_encode(Csrf::token()) ?>;
    const addUrl = <?= json_encode(url('cart/add')) ?>;
    const itemsUrl = <?= json_encode(url('cart/items')) ?>;
    const cartUrl = <?= json_encode(url('cart/index')) ?>;

    function setCount(count) {
        document.querySelectorAll('.cart-count').forEach((badge) => {
            badge.textContent = count;
            badge.hidden = !(count > 0);
        });
    }

    function notify(message, ok) {
        const toast = document.getElementById('cart-toast');
        toast.className = 'toast align-items-center border-0 text-white ' + (ok ? 'bg-success' : 'bg-danger');
        const body = toast.querySelector('.toast-body');
        body.textContent = message + ' ';
        if (ok) {
            const link = document.createElement('a');
            link.href = cartUrl;
            link.className = 'link-light fw-semibold';
            link.textContent = 'Ver carrito';
            body.appendChild(link);
        }
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 3500 }).show();
    }

    // items: [{ product_id, quantity }]. Devuelve la respuesta para que quien llama marque sus botones.
    async function add(items) {
        try {
            const response = await fetch(addUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({ items }),
            });
            const result = await response.json();
            if (result.success) setCount(result.count);
            const extra = result.errors && result.errors.length ? ' (' + result.errors.join(' ') + ')' : '';
            notify((result.message || 'No se pudo agregar al carrito.') + extra, !!result.success);
            return result;
        } catch (error) {
            notify('No se pudo conectar con el carrito.', false);
            return { success: false };
        }
    }

    fetch(itemsUrl).then((r) => r.json()).then((r) => setCount(r.data ? r.data.count : 0)).catch(() => {});

    return { add };
})();
</script>
