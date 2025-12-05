<?php
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// TU CLAVE SECRETA OTRA VEZ
\Stripe\Stripe::setApiKey('sk_test_51STaglL0CpOiDccydVmZOjCofJRo6x4VLSyQ4TCMasvuVAxRcwvFe1bBaSiZZ2aAGTHtUVEubZhlcRIxMH9Zj0lF00KgPEt2Sg');

if (!isset($_GET['session_id'])) { header('Location: inicio.php'); exit; }

try {
    $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

    if ($session->payment_status == 'paid') {
        
        // DATOS DE STRIPE
        $id_transaccion = $session->payment_intent;
        $total = $session->amount_total / 100;
        $email = $session->customer_details->email;
        $id_usuario = $_SESSION['user_id'];
        
        // --- CASO 1: VENTA DE PRODUCTOS (CARRITO) ---
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $cart = getCart();
            
            // 1. Insertar Venta
            $stmt = $pdo->prepare("INSERT INTO ventas (IdTransaccion, IdClientes, FechaVentas, EstadoVentas, EmailVentas, TotalVentas) VALUES (?, ?, NOW(), 'COMPLETED', ?, ?)");
            $stmt->execute([$id_transaccion, $id_usuario, $email, $total]);
            $idVenta = $pdo->lastInsertId();

            // 2. Insertar Detalles y Actualizar Stock
            $stmt_det = $pdo->prepare("INSERT INTO detalleventas (IdVentas, IdProductos, NombreDetalleVentas, CantidadDetalleVentas, PrecioDetalleVentas) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = $pdo->prepare("UPDATE productos SET StockProductos = StockProductos - ? WHERE IdProductos = ?");
            
            foreach ($cart as $id => $item) {
                $stmt_det->execute([$idVenta, $id, $item['nombre'], $item['cantidad'], $item['precio']]);
                $stmt_stock->execute([$item['cantidad'], $id]);
            }
            clearCart();
            
            $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Compra realizada con éxito!'];
            header('Location: perfil.php#v-pills-compras-tab');
            exit;

        // --- CASO 2: PAGO DE RESERVA ---
        } elseif (isset($_SESSION['pending_reservation'])) {
            $res = $_SESSION['pending_reservation'];
            
            $stmt = $pdo->prepare("INSERT INTO reservas (IdUsuarios, IdServicios, IdBarberos, FechaReservas, HoraReservas, EstadoReservas, IdTransaccion) VALUES (?, ?, ?, ?, ?, 'Confirmado', ?)");
            $stmt->execute([$res['idUsuario'], $res['idServicio'], $res['idBarbero'], $res['fecha'], $res['hora'], $id_transaccion]);
            
            unset($_SESSION['pending_reservation']);
            
            $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Reserva confirmada!'];
            header('Location: perfil.php#v-pills-reservas-tab');
            exit;
        }

    } else {
        throw new Exception("El pago no fue completado.");
    }

} catch (Exception $e) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
    header('Location: carrito.php');
    exit;
}
?>