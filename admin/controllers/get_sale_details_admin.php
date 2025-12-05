<?php
// Incluir conexión (ajusta la ruta según tu estructura real)
// Intentamos cargar la conexión desde la carpeta 'admin'
if (file_exists(__DIR__ . '/../conexion.php')) {
    require_once __DIR__ . '/../conexion.php';
} else {
    // Fallback a config.php si no encuentra conexion.php
    require_once __DIR__ . '/../../includes/config.php';
}

header('Content-Type: application/json');

// Validar ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}

$idVenta = intval($_GET['id']);

try {
    // Consulta para obtener detalles (Usando MySQLi o PDO según tu conexión)
    $sql = "
        SELECT d.NombreDetalleVentas, d.CantidadDetalleVentas, d.PrecioDetalleVentas
        FROM detalleventas d
        WHERE d.IdVentas = ?
    ";

    // Detectamos si la conexión es PDO o MySQLi y actuamos en consecuencia
    if (isset($pdo)) {
        // Versión PDO
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idVenta]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Versión MySQLi (la que usa tu admin)
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idVenta);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalles = [];
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
    }

    if (!empty($detalles)) {
        echo json_encode(['success' => true, 'details' => $detalles]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron detalles.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>