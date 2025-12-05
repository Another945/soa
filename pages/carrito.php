<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = 'carrito.php';
    header('Location: login.php');
    exit;
}
?>
<?php include '../includes/header.php'; ?>

<h1 class="section-title">Mi Carrito de Compras</h1>

<?php $cart = getCart(); ?>

<?php if (empty($cart)): ?>
    <div class="text-center p-5 item-card">
        <h2 class="text-warning">Tu carrito está vacío</h2>
        <a href="productos.php" class="btn btn-custom-yellow mt-3">Ir a Productos</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark table-borderless cart-table align-middle">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Precio</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart as $id => $item):
                    // Recuperar imagen de la sesión, o usar default si es un carrito viejo
                    $img = $item['imagen'] ?? '../assets/images/productos/default.jpg';
                ?>
                    <tr id="row-<?php echo $id; ?>">
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($img); ?>"
                                    alt="Producto"
                                    class="rounded me-3"
                                    style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #444;">
                                <div>
                                    <h5 class="mb-0" style="font-size: 1rem;"><?php echo htmlspecialchars($item['nombre']); ?></h5>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="quantity-selector">
                                <button type="button" class="quantity-btn minus-btn" data-id="<?php echo $id; ?>"><i class="fas fa-minus"></i></button>
                                <input type="number" class="form-control quantity-input" value="<?php echo $item['cantidad']; ?>" min="1" readonly>
                                <button type="button" class="quantity-btn plus-btn" data-id="<?php echo $id; ?>"><i class="fas fa-plus"></i></button>
                            </div>
                        </td>
                        <td class="text-end">S/ <?php echo number_format($item['precio'], 2); ?></td>
                        <td class="text-end fw-bold" id="subtotal-<?php echo $id; ?>">S/ <?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                        <td class="text-center">
                            <a href="../process/remove_from_cart.php?id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <hr class="my-4" style="border-color: rgba(255, 193, 7, 0.2);">

    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4 text-end">
            <h3 class="mb-3">Total del Pedido</h3>
            <h2 class="text-warning fw-bold mb-4" id="grand-total">S/ <?php echo number_format(cartTotal(), 2); ?></h2>

            <button id="checkout-button" class="btn btn-custom-yellow w-100">
                <i class="fas fa-credit-card me-2"></i> Pagar con Tarjeta
            </button>
        </div>
    </div>
<?php endif; ?>

<script src="https://js.stripe.com/v3/"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- LÓGICA PARA ACTUALIZAR CANTIDAD (AJAX) ---
        function updateCartWithAJAX(productId, newQty) {
            if (newQty < 1) return;

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', productId);
            formData.append('qty', newQty);

            fetch('../process/handle_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subtotalElement = document.getElementById(`subtotal-${productId}`);
                        if (subtotalElement) subtotalElement.textContent = data.newSubtotal;

                        const grandTotalElement = document.getElementById('grand-total');
                        if (grandTotalElement) grandTotalElement.textContent = data.newGrandTotal;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Botón Menos (-)
        document.querySelectorAll('.minus-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const input = this.nextElementSibling; // El input está después del botón menos
                let newQty = parseInt(input.value) - 1;
                if (newQty >= 1) {
                    input.value = newQty;
                    updateCartWithAJAX(productId, newQty);
                }
            });
        });

        // Botón Más (+)
        document.querySelectorAll('.plus-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const input = this.previousElementSibling; // El input está antes del botón más
                let newQty = parseInt(input.value) + 1;
                input.value = newQty;
                updateCartWithAJAX(productId, newQty);
            });
        });


        // --- LÓGICA DE PAGO STRIPE ---
        const checkoutButton = document.getElementById('checkout-button');
        if (checkoutButton) {
            // IMPORTANTE: TU CLAVE PÚBLICA DE STRIPE
            const stripe = Stripe('pk_test_51STaglL0CpOiDccy9CPHICEfw0qKoSKcfzwtNTtfEw4Rb7ejSrxd9TykC20DpKfvfMvmhVkd8JMHD6vv1ZWg5WMu00PpNfkGvL');

            checkoutButton.addEventListener('click', function() {
                checkoutButton.disabled = true;
                checkoutButton.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin"></i>';

                fetch('../process/process_payment.php', {
                        method: 'POST',
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.id) {
                            return stripe.redirectToCheckout({
                                sessionId: data.id
                            });
                        } else {
                            let msg = data.error || 'Error al iniciar el pago.';
                            Swal.fire('Error', msg, 'error');
                            checkoutButton.disabled = false;
                            checkoutButton.innerHTML = 'Pagar con Tarjeta';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        Swal.fire('Error de conexión', 'No se pudo conectar con el servidor.', 'error');
                        checkoutButton.disabled = false;
                        checkoutButton.innerHTML = 'Pagar con Tarjeta';
                    });
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>