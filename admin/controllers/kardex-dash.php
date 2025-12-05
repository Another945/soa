<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

if (isset($_GET['action']) && $_GET['action'] === 'ajax_list') {
    // Nota: Kardex muestra TODO sin paginación para facilitar reportes globales
    
    // 1. Recibir Filtros (vienen como cadenas separadas por comas)
    $f_productos = isset($_GET['producto']) && $_GET['producto'] !== '' ? explode(',', $_GET['producto']) : [];
    $f_categorias = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? explode(',', $_GET['categoria']) : [];
    $f_estados = isset($_GET['estado']) && $_GET['estado'] !== '' ? explode(',', $_GET['estado']) : [];

    // 2. Construir la cláusula WHERE dinámica
    $where_parts = ["p.ActivoProductos = 1"];

    // Filtro de Productos (IN)
    if (!empty($f_productos)) {
        // Sanitizar cada ID
        $ids = array_map('intval', $f_productos);
        if (!empty($ids)) {
            $where_parts[] = "p.IdProductos IN (" . implode(',', $ids) . ")";
        }
    }

    // Filtro de Categorías (IN)
    if (!empty($f_categorias)) {
        $ids = array_map('intval', $f_categorias);
        if (!empty($ids)) {
            $where_parts[] = "c.IdCategorias IN (" . implode(',', $ids) . ")";
        }
    }

    // Filtro de Estados (Lógica de Stock)
    // Normal: > 10 | Bajo: 1-10 | Agotado: <= 0
    $estado_conditions = [];
    if (!empty($f_estados)) {
        foreach ($f_estados as $estado) {
            if ($estado === 'Normal') $estado_conditions[] = "p.StockProductos > 10";
            if ($estado === 'Bajo') $estado_conditions[] = "(p.StockProductos > 0 AND p.StockProductos <= 10)";
            if ($estado === 'Agotado') $estado_conditions[] = "p.StockProductos <= 0";
        }
        if (!empty($estado_conditions)) {
            $where_parts[] = "(" . implode(' OR ', $estado_conditions) . ")";
        }
    }

    $where_sql = "WHERE " . implode(' AND ', $where_parts);

    // 3. Consulta SQL
    $sql = "SELECT p.IdProductos, p.NombreProductos, p.StockProductos, p.PrecioProductos, c.NombreCategorias, 
            COALESCE(SUM(dv.CantidadDetalleVentas), 0) AS TotalSalidas
            FROM productos p 
            JOIN categorias c ON p.IdCategorias = c.IdCategorias
            LEFT JOIN detalleventas dv ON p.IdProductos = dv.IdProductos
            $where_sql
            GROUP BY p.IdProductos
            ORDER BY p.NombreProductos ASC";
            
    $result = $conn->query($sql);

    // 4. Generar HTML
    ob_start();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { 
            $stock = $row['StockProductos'];
            $badge = $stock > 10 ? 'bg-success' : ($stock > 0 ? 'bg-warning text-dark' : 'bg-danger');
            $texto = $stock > 10 ? 'Normal' : ($stock > 0 ? 'Bajo' : 'Agotado');
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['NombreProductos']); ?></td>
                <td><?php echo htmlspecialchars($row['NombreCategorias']); ?></td>
                <td>S/ <?php echo number_format($row['PrecioProductos'], 2); ?></td>
                <td class="text-danger fw-bold"><?php echo $row['TotalSalidas']; ?></td>
                <td class="text-success fw-bold"><?php echo $stock; ?></td>
                <td><span class="badge <?php echo $badge; ?>"><?php echo $texto; ?></span></td>
            </tr>
        <?php }
    } else { echo '<tr><td colspan="6" class="text-center">No hay datos que coincidan con los filtros.</td></tr>'; }
    $tabla = ob_get_clean();

    echo json_encode(['tabla' => $tabla]);
    exit;
}
?>