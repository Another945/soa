<?php
include 'layouts/header.php';

// --- LÓGICA DE PAGINACIÓN Y BÚSQUEDA ---
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;
$busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

$where_clause = "";
if ($busqueda) {
    $where_clause = "WHERE u.NombreUsuarios LIKE '%$busqueda%' OR v.IdTransaccion LIKE '%$busqueda%' OR v.EmailVentas LIKE '%$busqueda%'";
}

// Contar total
$total_ventas_query = "SELECT COUNT(*) as total FROM ventas v JOIN usuarios u ON v.IdClientes = u.IdUsuarios $where_clause";
$total_ventas = $conn->query($total_ventas_query)->fetch_assoc()['total'];
$total_paginas = ceil($total_ventas / $registros_por_pagina);

// Consulta principal
$sql = "
    SELECT v.*, u.NombreUsuarios, u.ApellidoUsuarios 
    FROM ventas v 
    JOIN usuarios u ON v.IdClientes = u.IdUsuarios 
    $where_clause 
    ORDER BY v.FechaVentas DESC 
    LIMIT $registros_por_pagina OFFSET $offset
";
$result = $conn->query($sql);
?>

<div class="container-fluid p-4">
    <h1 class="mb-4">Historial de Ventas</h1>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Registro de Transacciones</h5>
        </div>
        <div class="card-body">
            <form class="d-flex mb-3" method="get" action="ventas.php">
                <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar por cliente, email o ID transacción..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button class="btn btn-outline-warning" type="submit">Buscar</button>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Transacción</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-center">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($venta = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($venta['IdTransaccion']); ?></td>
                                    <td><?php echo htmlspecialchars($venta['NombreUsuarios'] . ' ' . $venta['ApellidoUsuarios']); ?></td>
                                    <td><?php echo htmlspecialchars($venta['EmailVentas']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venta['FechaVentas'])); ?></td>
                                    <td>S/ <?php echo number_format($venta['TotalVentas'], 2); ?></td>
                                    <td><span class="badge bg-success"><?php echo htmlspecialchars($venta['EstadoVentas']); ?></span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetalleVenta" data-id="<?php echo $venta['IdVentas']; ?>">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No se encontraron ventas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($total_paginas > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $i; ?>&busqueda=<?php echo urlencode($busqueda); ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Detalles de la Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tablaDetalles">
                        <thead><tr><th>Producto/Servicio</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalDetalle = document.getElementById('modalDetalleVenta');
    modalDetalle.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var idVenta = button.getAttribute('data-id');
        var modalBody = modalDetalle.querySelector('#tablaDetalles tbody');
        
        modalBody.innerHTML = '<tr><td colspan="4" class="text-center">Cargando...</td></tr>';

        // Reutilizamos el script que ya creamos para el perfil del usuario
        fetch(`controllers/get_sale_details_admin.php?id=${idVenta}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '';
                    data.details.forEach(item => {
                        let subtotal = item.PrecioDetalleVentas * item.CantidadDetalleVentas;
                        html += `<tr>
                            <td>${item.NombreDetalleVentas}</td>
                            <td>${item.CantidadDetalleVentas}</td>
                            <td>S/ ${parseFloat(item.PrecioDetalleVentas).toFixed(2)}</td>
                            <td>S/ ${subtotal.toFixed(2)}</td>
                        </tr>`;
                    });
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar detalles.</td></tr>';
                }
            });
    });
});
</script>

<?php include 'layouts/footer.php'; ?>