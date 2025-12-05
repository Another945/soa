<?php
require_once 'config.php';


function isLoggedIn() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'cliente';
}

function isAdminLoggedIn() {
    return isset($_SESSION['user_type']) && 
           ($_SESSION['user_type'] === 'administrador' || $_SESSION['user_type'] === 'empleado');
}

/**
 * Obtiene los datos del usuario (cliente) actualmente logueado.
 * MODIFICADO: Se renombró y ahora consulta la tabla 'usuarios'.
 */
function getCurrentUser() {
    global $pdo;
    if (isLoggedIn() && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE IdUsuarios = ? AND EstadoUsuarios = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

function getCart() {
    return $_SESSION['cart'] ?? [];
}

function addToCart($idProducto, $cantidad = 1) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT NombreProductos, PrecioProductos FROM productos WHERE IdProductos = ? AND ActivoProductos = 1");
    $stmt->execute([$idProducto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $cart = getCart();
        if (isset($cart[$idProducto])) {
            $cart[$idProducto]['cantidad'] += $cantidad;
        } else {
            $cart[$idProducto] = [
                'nombre' => $producto['NombreProductos'],
                'precio' => $producto['PrecioProductos'],
                'cantidad' => $cantidad
            ];
        }
        $_SESSION['cart'] = $cart;
        return true;
    }
    return false;
}

function removeFromCart($idProducto) {
    $cart = getCart();
    unset($cart[$idProducto]);
    $_SESSION['cart'] = $cart;
}

function cartTotal() {
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total;
}

function clearCart() {
    unset($_SESSION['cart']);
}

function getReservaDetails($idReserva) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, 
               u.NombreUsuarios, u.ApellidoUsuarios, 
               s.DescripcionServicios, -- Asumiendo que quieres la descripción del servicio
               e.NombreEmpleado, e.ApellidoEmpleados 
        FROM reservas r 
        LEFT JOIN usuarios u ON r.IdUsuarios = u.IdUsuarios 
        LEFT JOIN servicios s ON r.IdServicios = s.IdServicios 
        LEFT JOIN empleados e ON r.IdBarberos = e.IdEmpleados 
        WHERE r.IdReservas = ?
    ");
    $stmt->execute([$idReserva]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getDashboardStats() {
    global $pdo;
    $stats = [];
    $stats['total_ventas'] = $pdo->query("SELECT SUM(TotalVentas) FROM ventas WHERE EstadoVentas = 'COMPLETED'")->fetchColumn() ?: 0;
    $stats['reservas_pendientes'] = $pdo->query("SELECT COUNT(*) FROM reservas WHERE EstadoReservas = 'Confirmado' AND FechaReservas >= CURDATE()")->fetchColumn() ?: 0;
    $stats['productos_stock'] = $pdo->query("SELECT SUM(StockProductos) FROM productos WHERE ActivoProductos = 1")->fetchColumn() ?: 0;
    // La consulta de clientes ahora cuenta desde la tabla 'usuarios'
    $stats['clientes_activos'] = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE EstadoUsuarios = 1")->fetchColumn() ?: 0;
    return $stats;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>