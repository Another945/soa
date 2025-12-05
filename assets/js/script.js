// Filtros y búsqueda en productos
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const productCards = document.querySelectorAll('.product-card');

    if (searchInput && categoryFilter) {
        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const category = categoryFilter.value;

            productCards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const cat = card.dataset.category;

                let show = true;
                if (searchTerm && !title.includes(searchTerm)) show = false;
                if (category && category !== 'all' && cat !== category) show = false;

                card.style.display = show ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
    }

    // AJAX para agregar a carrito (opcional, para no recargar página)
    const addToCartBtns = document.querySelectorAll('.add-to-cart');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            fetch('../process/add_to_cart.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Producto agregado al carrito!');
                        // Actualiza contador carrito en nav
                        location.reload(); // Simple, o actualiza DOM
                    }
                });
        });
    });

    // Validación formulario reservas
    const reservaForm = document.getElementById('reservaForm');
    if (reservaForm) {
        reservaForm.addEventListener('submit', function(e) {
            if (!document.getElementById('fecha').value || !document.getElementById('hora').value) {
                e.preventDefault();
                alert('Selecciona fecha y hora');
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    
    // Función para mostrar alertas con SweetAlert2
    function showAlert(icon, message) {
        Swal.fire({
            icon: icon,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }

    // Escuchar clics en todos los botones "Agregar al Carrito"
    document.body.addEventListener('click', function(e) {
        if (e.target && e.target.matches('.add-to-cart-ajax')) {
            e.preventDefault(); // Prevenir la navegación a otra página

            const button = e.target;
            const productId = button.dataset.productId;

            // Crear los datos que se enviarán al servidor
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('id', productId);

            // Enviar la solicitud AJAX
            fetch('../process/handle_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Si el servidor confirma el éxito, actualizar el contador del carrito
                    const cartCountElement = document.getElementById('cart-count');
                    if (cartCountElement) {
                        cartCountElement.textContent = data.cartCount;
                    }
                    // Mostrar la alerta de éxito
                    showAlert('success', data.message);
                } else {
                    showAlert('error', 'No se pudo agregar el producto.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Ocurrió un error de red.');
            });
        }
    });
    function updateCartQuantity(productId, newQty) {
        if (newQty < 1) return;

        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', productId);
        formData.append('qty', newQty);

        fetch('../process/handle_cart.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar subtotal de la fila
                const subtotalEl = document.getElementById(`subtotal-${productId}`);
                if (subtotalEl) subtotalEl.textContent = data.newSubtotal;
                
                // Actualizar total general
                const totalEl = document.getElementById('grand-total');
                if (totalEl) totalEl.textContent = data.newGrandTotal;
            }
        })
        .catch(err => console.error('Error updating cart:', err));
    }

    // Event Delegation para los botones + y -
    document.body.addEventListener('click', function(e) {
        
        // Botón MÁS (+)
        if (e.target.closest('.plus-btn')) {
            const btn = e.target.closest('.plus-btn');
            const productId = btn.dataset.id;
            const input = btn.parentElement.querySelector('.quantity-input');
            let val = parseInt(input.value);
            
            // Opcional: Validar stock máximo si tienes ese dato (data-stock)
            // const max = parseInt(input.dataset.stock || 999);
            // if (val < max) { ... }

            val++;
            input.value = val;
            updateCartQuantity(productId, val);
        }

        // Botón MENOS (-)
        if (e.target.closest('.minus-btn')) {
            const btn = e.target.closest('.minus-btn');
            const productId = btn.dataset.id;
            const input = btn.parentElement.querySelector('.quantity-input');
            let val = parseInt(input.value);

            if (val > 1) {
                val--;
                input.value = val;
                updateCartQuantity(productId, val);
            }
        }

        // Botón AGREGAR AL CARRITO (Catálogo)
        if (e.target.matches('.add-to-cart-ajax')) {
            // ... (tu código existente de agregar) ...
        }
    });
    
});