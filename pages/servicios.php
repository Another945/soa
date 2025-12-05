<?php
require_once '../includes/functions.php';
include '../includes/header.php';

// 1. Obtener TODOS los servicios activos para que JS haga la paginación
$stmt = $pdo->query("SELECT * FROM servicios WHERE ActivoServicios = '1' ORDER BY TipoServicios ASC");
$servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Clasificar servicios por categorías
$categorias = ['Cortes y Estilos' => [], 'Cuidado de Barba' => [], 'Servicios Premium' => [], 'Otros' => []];
foreach ($servicios as $serv) {
    $tipo = strtolower($serv['TipoServicios']);
    if (str_contains($tipo, 'corte') || str_contains($tipo, 'piercing')) { $categorias['Cortes y Estilos'][] = $serv; }
    elseif (str_contains($tipo, 'barba') || str_contains($tipo, 'afeitado')) { $categorias['Cuidado de Barba'][] = $serv; }
    elseif (str_contains($tipo, 'masaje') || str_contains($tipo, 'facial')) { $categorias['Servicios Premium'][] = $serv; }
    else { $categorias['Otros'][] = $serv; }
}

// 3. Lógica de filtrado
$categoriaSeleccionada = $_GET['categoria'] ?? '';
$categoriasAMostrar = $categoriaSeleccionada ? [$categoriaSeleccionada => $categorias[$categoriaSeleccionada]] : $categorias;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .service-modal-img {
        width: 100%; height: 300px; object-fit: cover;
        border-radius: 10px; border: 2px solid var(--primary-yellow);
    }
    .modal-content { background-color: #1e1e1e; color: #fff; border: 1px solid #444; }
    .modal-header { border-bottom: 1px solid #444; }
    .btn-close-white { filter: invert(1); }
</style>

<div class="container mt-5">
    <h1 class="section-title"><i class="bi bi-scissors"></i>Nuestros Servicios</h1>

    <div class="filtro-container">
        <form method="GET" class="w-100 d-flex justify-content-center">
            <select class="form-select" name="categoria" onchange="this.form.submit()">
                <option value="">Ver todos los servicios</option>
                <option value="Cortes y Estilos" <?php if($categoriaSeleccionada === 'Cortes y Estilos') echo 'selected'; ?>>Cortes y Estilos</option>
                <option value="Cuidado de Barba" <?php if($categoriaSeleccionada === 'Cuidado de Barba') echo 'selected'; ?>>Cuidado de Barba</option>
                <option value="Servicios Premium" <?php if($categoriaSeleccionada === 'Servicios Premium') echo 'selected'; ?>>Servicios Premium</option>
                <option value="Otros" <?php if($categoriaSeleccionada === 'Otros') echo 'selected'; ?>>Otros</option>
            </select>
        </form>
    </div>

    <div class='row justify-content-center' id="serviceList">
        <?php
        $hayServicios = false;
        foreach ($categoriasAMostrar as $lista) { if (!empty($lista)) { $hayServicios = true; break; } }

        if (!$hayServicios) {
             echo '<p class="text-center text-muted">No se encontraron servicios en esta categoría.</p>';
        } else {
            foreach ($categoriasAMostrar as $nombreCat => $lista):
                if (empty($lista)) continue;
                
                if (!$categoriaSeleccionada) { 
                    echo "<div class='col-12'><h2 class='section-title mt-5 w-100'>$nombreCat</h2></div>"; 
                }
            
                foreach ($lista as $serv):
                    $stmt_img = $pdo->prepare("SELECT RutaImagen FROM imagenes WHERE Tipo = 'servicio' AND IdRelacionado = ?");
                    $stmt_img->execute([$serv['IdServicios']]);
                    $rutaImagen = $stmt_img->fetchColumn() ?: '../assets/images/servicios/default.jpg';
                ?>
                    <div class="col-lg-4 col-md-6 mb-4 service-item">
                        <div class="card item-card h-100 text-center">
                            <img src="<?php echo htmlspecialchars($rutaImagen); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($serv['TipoServicios']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-warning"><?php echo htmlspecialchars($serv['TipoServicios']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($serv['DescripcionServicios'], 0, 80)); ?>...</p>
                                
                                <div class="mt-3">
                                    <button class="btn btn-outline-light btn-sm w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalServicio"
                                            data-id="<?php echo $serv['IdServicios']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($serv['TipoServicios']); ?>"
                                            data-desc="<?php echo htmlspecialchars($serv['DescripcionServicios']); ?>"
                                            data-price="<?php echo $serv['PrecioServicios']; ?>"
                                            data-duration="<?php echo $serv['DuracionMinutos']; ?>"
                                            data-img="<?php echo htmlspecialchars($rutaImagen); ?>">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                endforeach;
            endforeach;
        } ?>
    </div>
    
    <h2 class="section-title mt-5"><i class="bi bi-person-lines-fill"></i>Nuestros Barberos</h2>
    <div class="row justify-content-center">
        <?php
        $stmt_barberos = $pdo->query("SELECT * FROM empleados WHERE RolEmpleados = 'barbero' AND Activo = 1");
        while ($emp = $stmt_barberos->fetch(PDO::FETCH_ASSOC)):
            $stmt_img_emp = $pdo->prepare("SELECT RutaImagen FROM imagenes WHERE Tipo = 'barbero' AND IdRelacionado = ?");
            $stmt_img_emp->execute([$emp['IdEmpleados']]);
            $imgBarbero = $stmt_img_emp->fetchColumn();
            if (!$imgBarbero) {
                $rutaFisica = "../assets/img/barberos/barbero" . $emp['IdEmpleados'] . ".jpg";
                $imgBarbero = file_exists(__DIR__ . "/" . $rutaFisica) ? $rutaFisica : "../assets/img/barberos/default.jpg";
            }
        ?>
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card item-card h-100 text-center barber-card">
                    <img src="<?php echo htmlspecialchars($imgBarbero); ?>" class="card-img-top mx-auto mt-4" style="width: 150px; height: 150px; object-fit: cover; border-radius: 15px;" alt="<?php echo htmlspecialchars($emp['NombreEmpleado']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-warning"><?php echo htmlspecialchars($emp['NombreEmpleado'] . ' ' . $emp['ApellidoEmpleados']); ?></h5>
                        <p class="card-text text-muted flex-grow-1">Especialista en estilos urbanos.</p>
                        <a href="reservas.php?barber_id=<?php echo $emp['IdEmpleados']; ?>" class="btn btn-custom-yellow mt-auto">Reservar con <?php echo htmlspecialchars($emp['NombreEmpleado']); ?></a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="modalServicio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning fw-bold" id="modalServTitle">Servicio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0 text-center">
                        <img src="" id="modalServImg" class="service-modal-img shadow-lg">
                    </div>
                    
                    <div class="col-md-6 d-flex flex-column justify-content-center">
                        <h2 class="text-warning fw-bold mb-2" id="modalServPrice">S/ 0.00</h2>
                        <div class="text-light mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-warning"></i>
                            <span id="modalServDuration" class="fw-bold">0 min</span>
                        </div>
                        
                        <h6 class="text-muted text-uppercase small mb-2">Descripción</h6>
                        <p class="text-light mb-4" id="modalServDesc" style="line-height: 1.6; font-size: 1.1rem;">Descripción...</p>
                        
                        <div class="mt-auto">
                            <a id="modalServBtn" href="#" class="btn btn-custom-yellow w-100 btn-lg fw-bold shadow-sm">
                                <i class="bi bi-calendar-check me-2"></i> Reservar Ahora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modalServicio');
    modalElement.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        const desc = button.getAttribute('data-desc');
        const price = parseFloat(button.getAttribute('data-price'));
        const duration = button.getAttribute('data-duration');
        const img = button.getAttribute('data-img');
        
        document.getElementById('modalServImg').src = img;
        document.getElementById('modalServTitle').textContent = nombre;
        document.getElementById('modalServDesc').textContent = desc || 'Sin descripción disponible.';
        document.getElementById('modalServPrice').textContent = 'S/ ' + price.toFixed(2);
        document.getElementById('modalServDuration').textContent = duration + ' minutos';
        
        // El botón "Reservar Ahora" dentro del modal lleva a la página de reservas
        document.getElementById('modalServBtn').href = 'reservas.php?service_id=' + id;
    });
});
</script>

<?php include '../includes/footer.php'; ?>