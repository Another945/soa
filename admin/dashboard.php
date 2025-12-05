<?php
include 'layouts/header.php';
 
// --- ESTADÍSTICAS ---
// Total de Clientes Activos
$total_clientes = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE EstadoUsuarios = 1")->fetch_assoc()['total'] ?? 0;

// Total de Productos Activos
$total_productos = $conn->query("SELECT COUNT(*) as total FROM productos WHERE ActivoProductos = 1")->fetch_assoc()['total'] ?? 0;

// Stock Total de Productos
$stock_total = $conn->query("SELECT SUM(StockProductos) as total FROM productos WHERE ActivoProductos = 1")->fetch_assoc()['total'] ?? 0;

// Total de Empleados Activos
$total_empleados = $conn->query("SELECT COUNT(*) as total FROM empleados WHERE Activo = 1")->fetch_assoc()['total'] ?? 0;

// Total de Servicios
$total_servicios = $conn->query("SELECT COUNT(*) as total FROM servicios WHERE ActivoServicios = 1")->fetch_assoc()['total'] ?? 0;

// Reservas Pendientes (Ejemplo: 'No Confirmado')
$reservas_pendientes = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE EstadoReservas = 'Confirmado' AND FechaReservas >= NOW()")->fetch_assoc()['total'] ?? 0;


// --- ÚLTIMAS RESERVAS (Lógica de Roles) ---
$userRole = $_SESSION['user_type'];
$userId = $_SESSION['user_id'];
$where_clause_ultimas = "";

// Si es un empleado, filtrar solo SUS reservas
if ($userRole === 'empleado') {
    $where_clause_ultimas = " WHERE r.IdBarberos = " . intval($userId);
}

$ultimas_reservas_query = "
    SELECT r.IdReservas, r.FechaReservas, r.HoraReservas, r.EstadoReservas,
           u.NombreUsuarios, u.ApellidoUsuarios,
           s.TipoServicios,
           e.NombreEmpleado, e.ApellidoEmpleados
    FROM reservas r
    JOIN usuarios u ON r.IdUsuarios = u.IdUsuarios
    JOIN servicios s ON r.IdServicios = s.IdServicios
    LEFT JOIN empleados e ON r.IdBarberos = e.IdEmpleados
    $where_clause_ultimas
    ORDER BY r.FechaReservas DESC, r.HoraReservas DESC
    LIMIT 5
";
$ultimas_reservas_result = $conn->query($ultimas_reservas_query);
?>

<div class="container-fluid p-4">
    <h1 class="mb-4">Bienvenido al Dashboard</h1>
    
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="clientes.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Clientes Activos</div><div class="card-text"><?php echo $total_clientes; ?></div></div>
                    <div class="col-auto"><i class="bi bi-people icon"></i></div>
                </div></div>
            </a></div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="productos.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Productos en Venta</div><div class="card-text"><?php echo $total_productos; ?></div></div>
                    <div class="col-auto"><i class="bi bi-box-seam icon"></i></div>
                </div></div>
            </a></div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="productos.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Stock Total (Unidades)</div><div class="card-text"><?php echo $stock_total; ?></div></div>
                    <div class="col-auto"><i class="bi bi-boxes icon"></i></div>
                </div></div>
            </a></div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="empleados.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Empleados Activos</div><div class="card-text"><?php echo $total_empleados; ?></div></div>
                    <div class="col-auto"><i class="bi bi-person-badge icon"></i></div>
                </div></div>
            </a></div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="servicios.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Servicios Ofrecidos</div><div class="card-text"><?php echo $total_servicios; ?></div></div>
                    <div class="col-auto"><i class="bi bi-scissors icon"></i></div>
                </div></div>
            </a></div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stat-card shadow-sm h-100 py-2"><a href="reservas.php">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col me-2"><div class="card-title">Citas Confirmadas</div><div class="card-text"><?php echo $reservas_pendientes; ?></div></div>
                    <div class="col-auto"><i class="bi bi-calendar-week icon"></i></div>
                </div></div>
            </a></div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Últimas Reservas Realizadas</h5>
                    <a href="reservas.php" class="btn btn-sm btn-warning">Ver Todas</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Servicio</th>
                                    <?php if ($userRole === 'administrador'): ?>
                                        <th>Barbero</th>
                                    <?php endif; ?>
                                    <th>Fecha y Hora</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($ultimas_reservas_result && $ultimas_reservas_result->num_rows > 0): ?>
                                    <?php while($reserva = $ultimas_reservas_result->fetch_assoc()): 
                                        // Lógica de color de estado
                                        $badgeClass = match(strtolower($reserva['EstadoReservas'])) {
                                            'confirmado' => 'bg-success',
                                            'completado' => 'bg-primary',
                                            'cancelado' => 'bg-danger',
                                            default => 'bg-warning text-dark'
                                        };
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($reserva['NombreUsuarios'] . ' ' . $reserva['ApellidoUsuarios']); ?></td>
                                            <td><?php echo htmlspecialchars($reserva['TipoServicios']); ?></td>
                                            
                                            <?php if ($userRole === 'administrador'): ?>
                                            <td>
                                                <?php if ($reserva['NombreEmpleado']): ?>
                                                    <i class="bi bi-person-circle me-1 text-muted"></i>
                                                    <?php echo htmlspecialchars($reserva['NombreEmpleado']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Por asignar</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php endif; ?>
                                            
                                            <td><?php echo date('d/m/Y h:i A', strtotime($reserva['FechaReservas'] . ' ' . $reserva['HoraReservas'])); ?></td>
                                            <td class="text-center"><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(ucfirst($reserva['EstadoReservas'])); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="<?php echo ($userRole === 'administrador') ? '5' : '4'; ?>" class="text-center py-4 text-muted">No hay reservas registradas recientemente.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'layouts/footer.php';
?>