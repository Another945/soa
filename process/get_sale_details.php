<?php
// Usamos __DIR__ para encontrar la ruta exacta, sin importar desde dónde se llame
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// 1. Validar sesión e ID
if (!isLoggedIn() || !isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado o falta ID.']);
    exit;
}

$idVenta = (int)$_GET['id'];
$idUsuario = $_SESSION['user_id'];

try {
    // 2. Consulta a la base de datos
    $stmt = $pdo->prepare("
        SELECT v.IdTransaccion, d.NombreDetalleVentas, d.CantidadDetalleVentas, d.PrecioDetalleVentas
        FROM ventas v
        JOIN detalleventas d ON v.IdVentas = d.IdVentas
        WHERE v.IdVentas = ? AND v.IdClientes = ?
    ");
    $stmt->execute([$idVenta, $idUsuario]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Enviar respuesta
    if ($detalles) {
        echo json_encode(['success' => true, 'details' => $detalles]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron productos para esta venta.']);
    }

} catch (Exception $e) {
    // Si falla la BD, enviamos el error exacto
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>