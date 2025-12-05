<?php
require_once '../includes/functions.php';
include '../includes/header.php';

// Lógica de paginación PHP
$productos_por_pagina = 6;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE ActivoProductos = 1")->fetchColumn();
$total_paginas = ceil($total_productos / $productos_por_pagina);
?>

<style>
    .product-modal-img {
        width: 100%; height: 300px; object-fit: contain;
        background-color: #fff; border-radius: 10px; padding: 10px;
    }
    .quantity-selector {
        display: inline-flex; align-items: center;
        background-color: #212529; border: 1px solid #495057; border-radius: 5px;
    }
    .quantity-btn {
        background: none; border: none; color: #fff; font-size: 1.2rem; padding: 5px 15px; cursor: pointer;
    }
    .quantity-btn:hover { color: #FFC107; }
    .quantity-input {
        width: 50px; text-align: center; background: transparent; border: none; color: #fff; font-weight: bold;
    }
    /* Estilo oscuro para el modal */
    .modal-content { background-color: #1e1e1e; color: #fff; border: 1px solid #444; }
    .modal-header { border-bottom: 1px solid #444; }
    .btn-close-white { filter: invert(1); }
</style>

<div class="container mt-5">
    <h1 class="section-title">Nuestros Productos</h1>

    <div class="row" id="productList">
        <?php
        $stmt_prods = $pdo->prepare("SELECT p.*, c.NombreCategorias, i.RutaImagen FROM productos p JOIN categorias c ON p.IdCategorias = c.IdCategorias LEFT JOIN imagenes i ON p.IdProductos = i.IdRelacionado AND i.Tipo = 'producto' WHERE p.ActivoProductos = 1 ORDER BY p.NombreProductos LIMIT :limit OFFSET :offset");
        $stmt_prods->bindValue(':limit', $productos_por_pagina, PDO::PARAM_INT);
        $stmt_prods->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt_prods->execute();
        
        while ($prod = $stmt_prods->fetch(PDO::FETCH_ASSOC)):
            $rutaImagen = $prod['RutaImagen'] ?? '../assets/images/productos/default.jpg';
        ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card item-card h-100">
                    <img src="<?php echo htmlspecialchars($rutaImagen); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod['NombreProductos']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($prod['NombreProductos']); ?></h5>
                        <p class="card-text small text-muted"><?php echo htmlspecialchars($prod['NombreCategorias']); ?></p>
                        <h4 class="price mb-3">S/ <?php echo number_format($prod['PrecioProductos'], 2); ?></h4>
                        
                        <div class="mt-auto d-grid gap-2">
                            <button class="btn btn-outline-warning btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalProducto"
                                    data-id="<?php echo $prod['IdProductos']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($prod['NombreProductos']); ?>"
                                    data-desc="<?php echo htmlspecialchars($prod['DescripcionProductos']); ?>"
                                    data-price="<?php echo $prod['PrecioProductos']; ?>"
                                    data-stock="<?php echo $prod['StockProductos']; ?>"
                                    data-img="<?php echo htmlspecialchars($rutaImagen); ?>">
                                Ver Detalles
                            </button>
                            <button class="btn btn-custom-yellow btn-sm add-to-cart-ajax" data-product-id="<?php echo $prod['IdProductos']; ?>">
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if ($total_paginas > 1): ?>
    <nav aria-label="Paginación" class="d-flex justify-content-center mt-4">
        <ul class="pagination">
            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a></li>
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>"><a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a></li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning fw-bold" id="modalProdTitle">Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <img src="" id="modalProdImg" class="product-modal-img shadow-sm">
                    </div>
                    <div class="col-md-6 d-flex flex-column justify-content-center">
                        <h2 class="text-warning fw-bold mb-3" id="modalProdPrice">S/ 0.00</h2>
                        <p class="text-light mb-4" id="modalProdDesc">Descripción...</p>
                        
                        <div id="modalStockAlert" class="alert alert-danger d-none py-2 mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>¡Quedan <span id="modalStockVal"></span> unidades!
                        </div>

                        <div class="mt-auto">
                            <div class="d-flex align-items-center mb-4">
                                <label class="form-label text-muted me-3 mb-0">Cantidad:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn" id="modalMinus"><i class="fas fa-minus"></i></button>
                                    <input type="text" class="form-control quantity-input" id="modalQty" value="1" readonly>
                                    <button type="button" class="quantity-btn" id="modalPlus"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <button id="modalAddToCartBtn" class="btn btn-custom-yellow w-100 btn-lg fw-bold shadow-sm">
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales para el modal
    let currentModalId = null;
    let currentModalStock = 0;

    const qtyInput = document.getElementById('modalQty');
    const modalStockAlert = document.getElementById('modalStockAlert');
    const btnAdd = document.getElementById('modalAddToCartBtn');

    // 1. AL ABRIR EL MODAL
    const modalElement = document.getElementById('modalProducto');
    modalElement.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        // Guardamos datos en variables globales
        currentModalId = button.getAttribute('data-id');
        currentModalStock = parseInt(button.getAttribute('data-stock'));
        
        // Llenar UI
        document.getElementById('modalProdImg').src = button.getAttribute('data-img');
        document.getElementById('modalProdTitle').textContent = button.getAttribute('data-nombre');
        document.getElementById('modalProdDesc').textContent = button.getAttribute('data-desc') || 'Sin descripción.';
        document.getElementById('modalProdPrice').textContent = 'S/ ' + parseFloat(button.getAttribute('data-price')).toFixed(2);
        
        // Reset cantidad
        qtyInput.value = 1;

        // Validar Stock
        if (currentModalStock < 10) {
            modalStockAlert.classList.remove('d-none');
            document.getElementById('modalStockVal').textContent = currentModalStock;
        } else {
            modalStockAlert.classList.add('d-none');
        }

        if (currentModalStock <= 0) {
            btnAdd.disabled = true;
            btnAdd.textContent = "Agotado";
        } else {
            btnAdd.disabled = false;
            btnAdd.textContent = "Agregar al Carrito";
        }
    });

    // 2. BOTONES + / -
    document.getElementById('modalPlus').addEventListener('click', function() {
        let val = parseInt(qtyInput.value);
        if (val < currentModalStock) {
            qtyInput.value = val + 1;
        } else {
            Swal.fire({ icon: 'warning', title: 'Stock Máximo', text: 'No hay más unidades disponibles.', timer: 1500, showConfirmButton: false });
        }
    });

    document.getElementById('modalMinus').addEventListener('click', function() {
        let val = parseInt(qtyInput.value);
        if (val > 1) {
            qtyInput.value = val - 1;
        }
    });

    // 3. BOTÓN AGREGAR (AJAX)
    btnAdd.addEventListener('click', function() {
        if (!currentModalId) return;

        const qty = parseInt(qtyInput.value);
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('id', currentModalId);
        formData.append('qty', qty);

        // Bloquear botón para evitar doble clic
        btnAdd.disabled = true;
        btnAdd.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Agregando...';

        fetch('../process/handle_cart.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            btnAdd.disabled = false;
            btnAdd.textContent = 'Agregar al Carrito';
            
            if (data.success) {
                // Actualizar contador del header
                const cartCount = document.getElementById('cart-count');
                if (cartCount) cartCount.textContent = data.cartCount;

                // Cerrar modal
                const bsModal = bootstrap.Modal.getInstance(modalElement);
                bsModal.hide();

                // Alerta Éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Añadido!',
                    text: data.message,
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 2000
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            btnAdd.disabled = false;
            btnAdd.textContent = 'Agregar al Carrito';
            Swal.fire('Error', 'Error de conexión.', 'error');
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>