<?php 
require_once '../includes/functions.php';
include '../includes/header.php'; 
?>

<style>
    /* Estilos del Modal de Producto */
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
    .modal-content { background-color: #1e1e1e; color: #fff; border: 1px solid #444; }
    .modal-header { border-bottom: 1px solid #444; }
    .btn-close-white { filter: invert(1); }

    /* Estilos del Carrusel */
    .carousel-control-prev-icon, .carousel-control-next-icon {
        background-color: var(--primary-yellow); border-radius: 50%; background-size: 50% 50%;
        width: 3rem; height: 3rem; box-shadow: 0 4px 10px rgba(0,0,0,0.5);
    }
    .carousel-control-prev { left: -5%; }
    .carousel-control-next { right: -5%; }
    @media (max-width: 992px) {
        .carousel-control-prev { left: 0; }
        .carousel-control-next { right: 0; }
    }
</style>

<div class="hero-section">
    <div class="container">
        <h1 class="display-4">Barbería M BARBER</h1>
        <p class="lead">Donde el estilo y la precisión se encuentran. Tu look, nuestra pasión.</p>
        <a href="servicios.php" class="btn btn-custom-yellow">Nuestros Servicios</a>
    </div>
</div>

<h2 class="section-title">Productos Destacados</h2>
<div class="row mb-5" id="featuredProductsList">
    <?php
    $stmt = $pdo->query("SELECT * FROM productos WHERE ActivoProductos = 1 AND IdCategorias = 8 ORDER BY RAND() LIMIT 3");
    while ($prod = $stmt->fetch(PDO::FETCH_ASSOC)):
        $stmt_img = $pdo->prepare("SELECT RutaImagen FROM imagenes WHERE Tipo = 'producto' AND IdRelacionado = ?");
        $stmt_img->execute([$prod['IdProductos']]);
        $imagen = $stmt_img->fetchColumn();
        $rutaImagen = $imagen ?: '../assets/images/productos/default.jpg';
    ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card item-card h-100">
                <img src="<?php echo htmlspecialchars($rutaImagen); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prod['NombreProductos']); ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($prod['NombreProductos']); ?></h5>
                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($prod['DescripcionProductos'], 0, 80)); ?>...</p>
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

<h2 class="section-title mt-5">Nuestros Estilos</h2>

<div id="carouselServicios" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-inner">
        <?php
        $stmt = $pdo->query("SELECT * FROM servicios WHERE ActivoServicios = 1 ORDER BY RAND() LIMIT 12");
        $todosLosServicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $gruposDeServicios = array_chunk($todosLosServicios, 4); // Grupos de 4
        
        foreach ($gruposDeServicios as $indice => $grupo):
            $activo = ($indice === 0) ? 'active' : '';
        ?>
            <div class="carousel-item <?php echo $activo; ?>">
                <div class="row">
                    <?php foreach ($grupo as $serv): 
                        $stmt_img = $pdo->prepare("SELECT RutaImagen FROM imagenes WHERE Tipo = 'servicio' AND IdRelacionado = ?");
                        $stmt_img->execute([$serv['IdServicios']]);
                        $rutaImagen = $stmt_img->fetchColumn() ?: '../assets/images/servicios/default.jpg';
                    ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card item-card h-100 text-center">
                                <img src="<?php echo htmlspecialchars($rutaImagen); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($serv['TipoServicios']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($serv['TipoServicios']); ?></h5>
                                    <h4 class="price mb-2">S/ <?php echo number_format($serv['PrecioServicios'], 2); ?></h4>
                                    <p class="card-text small flex-grow-1"><?php echo htmlspecialchars(substr($serv['DescripcionServicios'], 0, 60)); ?>...</p>
                                    <a href="reservas.php?service_id=<?php echo $serv['IdServicios']; ?>" class="btn btn-custom-yellow mt-auto">Reservar</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselServicios" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselServicios" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
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
    let currentModalId = null;
    let currentModalStock = 0;

    const qtyInput = document.getElementById('modalQty');
    const btnAdd = document.getElementById('modalAddToCartBtn');
    const modalStockAlert = document.getElementById('modalStockAlert');

    // 1. AL ABRIR EL MODAL
    const modalElement = document.getElementById('modalProducto');
    modalElement.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        currentModalId = button.getAttribute('data-id');
        currentModalStock = parseInt(button.getAttribute('data-stock'));
        
        document.getElementById('modalProdImg').src = button.getAttribute('data-img');
        document.getElementById('modalProdTitle').textContent = button.getAttribute('data-nombre');
        document.getElementById('modalProdDesc').textContent = button.getAttribute('data-desc') || 'Sin descripción.';
        document.getElementById('modalProdPrice').textContent = 'S/ ' + parseFloat(button.getAttribute('data-price')).toFixed(2);
        
        qtyInput.value = 1;

        if (currentModalStock < 10) {
            modalStockAlert.classList.remove('d-none');
            document.getElementById('modalStockVal').textContent = currentModalStock;
        } else {
            modalStockAlert.classList.add('d-none');
        }

        if (currentModalStock <= 0) {
            btnAdd.disabled = true; btnAdd.textContent = "Agotado";
        } else {
            btnAdd.disabled = false; btnAdd.textContent = "Agregar al Carrito";
        }
    });

    // 2. BOTONES +/-
    document.getElementById('modalPlus').addEventListener('click', () => {
        if (parseInt(qtyInput.value) < currentModalStock) qtyInput.value = parseInt(qtyInput.value) + 1;
        else Swal.fire({ icon: 'warning', title: 'Stock Máximo', timer: 1500, showConfirmButton: false });
    });
    document.getElementById('modalMinus').addEventListener('click', () => {
        if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
    });

    // 3. AGREGAR AL CARRITO (AJAX)
    btnAdd.addEventListener('click', () => {
        if (!currentModalId) return;
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('id', currentModalId);
        formData.append('qty', qtyInput.value);

        btnAdd.disabled = true;
        fetch('../process/handle_cart.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            btnAdd.disabled = false;
            if (data.success) {
                document.getElementById('cart-count').textContent = data.cartCount;
                bootstrap.Modal.getInstance(modalElement).hide();
                Swal.fire({ icon: 'success', title: '¡Añadido!', text: data.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>