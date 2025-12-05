<?php
require_once '../includes/functions.php';

if (!isLoggedIn()) { header('Location: login.php'); exit; }
$user = getCurrentUser();
if (!$user) { header('Location: ../process/logout.php'); exit; }

$compras = $pdo->prepare("SELECT * FROM ventas WHERE IdClientes = ? ORDER BY FechaVentas DESC");
$compras->execute([$user['IdUsuarios']]);
$historial_compras = $compras->fetchAll(PDO::FETCH_ASSOC);

$reservas = $pdo->prepare("SELECT r.*, s.TipoServicios, s.DuracionMinutos, e.IdEmpleados as BarberoId FROM reservas r JOIN servicios s ON r.IdServicios = s.IdServicios LEFT JOIN empleados e ON r.IdBarberos = e.IdEmpleados WHERE r.IdUsuarios = ? ORDER BY r.FechaReservas DESC, r.HoraReservas DESC");
$reservas->execute([$user['IdUsuarios']]);
$historial_reservas = $reservas->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3">
            <div class="profile-nav">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link" id="v-pills-datos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-datos" type="button" role="tab">Mis Datos</button>
                    <button class="nav-link" id="v-pills-compras-tab" data-bs-toggle="pill" data-bs-target="#v-pills-compras" type="button" role="tab">Mis Compras</button>
                    <button class="nav-link" id="v-pills-reservas-tab" data-bs-toggle="pill" data-bs-target="#v-pills-reservas" type="button" role="tab">Mis Reservas</button>
                </div>
                <hr style="border-color: rgba(255,255,255,0.1);">
                <a class="nav-link" href="../process/logout.php" style="color: var(--text-muted);">Cerrar Sesión</a>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="profile-content">
                <?php if (isset($_GET['success'])): ?><div class="alert alert-success">¡Acción completada!</div><?php endif; ?>
                <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>

                <div class="tab-content" id="v-pills-tabContent">
                    <div class="tab-pane fade" id="v-pills-datos" role="tabpanel">
                        <h3>Editar mis Datos</h3>
                        <form action="../process/process_update_profile.php" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($user['NombreUsuarios']); ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Apellido</label><input type="text" class="form-control" name="apellido" value="<?php echo htmlspecialchars($user['ApellidoUsuarios']); ?>" required></div>
                            </div>
                            <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['EmailUsuarios']); ?>" required></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Teléfono</label><input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($user['TelefonoUsuarios']); ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">DNI</label><input type="text" class="form-control" name="dni" value="<?php echo htmlspecialchars($user['DNIUsuarios']); ?>"></div>
                            </div>
                            <button type="submit" class="btn btn-custom-yellow">Guardar Cambios</button>
                        </form>
                        <h3 class="mt-5">Cambiar Contraseña</h3>
                        <form action="../process/process_change_password.php" method="POST">
                            <div class="mb-3"><label class="form-label">Contraseña Actual</label><input type="password" class="form-control" name="current_password" required></div>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Nueva Contraseña</label><input type="password" class="form-control" name="new_password" required minlength="6"></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Confirmar Nueva Contraseña</label><input type="password" class="form-control" name="confirm_password" required></div>
                            </div>
                            <button type="submit" class="btn btn-custom-yellow">Cambiar Contraseña</button>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="v-pills-compras" role="tabpanel">
                        <h3>Historial de Compras</h3>
                        <?php if (empty($historial_compras)): ?>
                            <p class="text-muted">Aún no has realizado ninguna compra.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-striped history-table">
                                    <thead><tr><th>ID Transacción</th><th>Fecha</th><th>Total</th><th>Estado</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($historial_compras as $compra): ?>
                                        <tr class="clickable-row" 
                                            data-id="<?php echo $compra['IdVentas']; ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetalleVenta"
                                            style="cursor: pointer;">
                                            <td><?php echo htmlspecialchars($compra['IdTransaccion']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($compra['FechaVentas'])); ?></td>
                                            <td>$<?php echo number_format($compra['TotalVentas'], 2); ?></td>
                                            <td><span class="badge bg-success"><?php echo htmlspecialchars($compra['EstadoVentas']); ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="v-pills-reservas" role="tabpanel">
                        <h3>Historial de Reservas</h3>
                         <?php if (empty($historial_reservas)): ?>
                            <p class="text-muted">Aún no has realizado ninguna reserva.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-dark table-striped history-table">
                                    <thead><tr><th>Servicio</th><th>Fecha</th><th>Hora</th><th>Estado</th><th>Acciones</th></tr></thead>
                                    <tbody>
                                         <?php foreach ($historial_reservas as $reserva): 
                                            $reservaDateTime = new DateTime($reserva['FechaReservas'] . ' ' . $reserva['HoraReservas']);
                                            $now = new DateTime();
                                            $canCancel = $reservaDateTime > $now && ($reserva['EstadoReservas'] === 'Confirmado' || $reserva['EstadoReservas'] === 'No Confirmado');
                                         ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($reserva['TipoServicios']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($reserva['FechaReservas'])); ?></td>
                                            <td><?php echo date('h:i A', strtotime($reserva['HoraReservas'])); ?></td>
                                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($reserva['EstadoReservas']); ?></span></td>
                                            <td>
                                                <?php if ($canCancel): ?>
                                                <button class="btn btn-outline-danger btn-sm cancel-btn"
                                                        data-id="<?php echo $reserva['IdReservas']; ?>"
                                                        data-datetime="<?php echo $reservaDateTime->format('Y-m-d H:i:s'); ?>"
                                                        data-duration="<?php echo $reserva['DuracionMinutos']; ?>"
                                                        data-barberoid="<?php echo $reserva['BarberoId']; ?>">Cancelar</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background-color: var(--light-dark-bg); border: 1px solid rgba(255, 255, 255, 0.1);">
      <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
        <h5 class="modal-title text-warning">Detalles de la Transacción</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6 class="text-light mb-3" id="modalTransactionId">ID: </h6>
        <div class="table-responsive">
            <table class="table table-dark table-striped history-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalSaleDetailsBody"></tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let hash = window.location.hash;
    let targetTabId = hash ? hash : '#v-pills-datos';
    let triggerEl = document.querySelector('button[data-bs-target="' + targetTabId + '"]');
    if (triggerEl) {
        let tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
});
</script>

<?php include '../includes/footer.php'; ?>