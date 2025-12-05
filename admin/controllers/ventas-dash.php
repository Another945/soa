<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    $limite = 5;
    $pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
    $offset = ($pagina - 1) * $limite;
    $busqueda = isset($_GET['busqueda']) ? $conn->real_escape_string($_GET['busqueda']) : '';

    $where = "WHERE 1=1";
    if ($busqueda) {
        $where .= " AND (u.NombreUsuarios LIKE '%$busqueda%' OR v.IdTransaccion LIKE '%$busqueda%' OR v.EmailVentas LIKE '%$busqueda%')";
    }

    $total = $conn->query("SELECT COUNT(*) as total FROM ventas v JOIN usuarios u ON v.IdClientes = u.IdUsuarios $where")->fetch_assoc()['total'];
    $paginas = ceil($total / $limite);

    $sql = "SELECT v.*, u.NombreUsuarios, u.ApellidoUsuarios FROM ventas v JOIN usuarios u ON v.IdClientes = u.IdUsuarios $where ORDER BY v.FechaVentas DESC LIMIT $limite OFFSET $offset";
    $result = $conn->query($sql);

    ob_start();
    if ($result && $result->num_rows > 0) {
        while($venta = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($venta['IdTransaccion']); ?></td>
                <td><?php echo htmlspecialchars($venta['NombreUsuarios'] . ' ' . $venta['ApellidoUsuarios']); ?></td>
                <td><?php echo htmlspecialchars($venta['EmailVentas']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($venta['FechaVentas'])); ?></td>
                <td>S/ <?php echo number_format($venta['TotalVentas'], 2); ?></td>
                <td><span class="badge bg-success"><?php echo htmlspecialchars($venta['EstadoVentas']); ?></span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetalleVenta" data-id="<?php echo $venta['IdVentas']; ?>">Ver</button>
                </td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="7" class="text-center">No se encontraron ventas.</td></tr>'; }
    $tabla = ob_get_clean();

    // (Generación de paginación idéntica a las anteriores, cambiando onclick="cargarVentas(...)")
    ob_start();
    if ($paginas > 1) { ?>
        <nav><ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($pagina <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarVentas(<?php echo $pagina - 1; ?>); return false;">Anterior</a></li>
            <?php for ($i = 1; $i <= $paginas; $i++): ?>
                <li class="page-item <?php echo ($pagina == $i) ? 'active' : ''; ?>"><a class="page-link" href="#" onclick="cargarVentas(<?php echo $i; ?>); return false;"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($pagina >= $paginas) ? 'disabled' : ''; ?>"><a class="page-link" href="#" onclick="cargarVentas(<?php echo $pagina + 1; ?>); return false;">Siguiente</a></li>
        </ul></nav>
    <?php }
    $paginacion = ob_get_clean();

    echo json_encode(['tabla' => $tabla, 'paginacion' => $paginacion]);
    exit;
}
?>