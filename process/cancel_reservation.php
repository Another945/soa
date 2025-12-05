<?php
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; // Cargar librería de Stripe

// 1. CONFIGURA TU CLAVE SECRETA DE STRIPE
// Asegúrate de usar la misma clave que en process_payment.php
\Stripe\Stripe::setApiKey('sk_test_51STaglL0CpOiDccydVmZOjCofJRo6x4VLSyQ4TCMasvuVAxRcwvFe1bBaSiZZ2aAGTHtUVEubZhlcRIxMH9Zj0lF00KgPEt2Sg');

header('Content-Type: application/json');

// Verificar login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$idReserva = $_POST['id'];
$idUsuario = $_SESSION['user_id'];

try {
    // 2. OBTENER DATOS DE LA RESERVA (Incluyendo el ID de pago de Stripe)
    $stmt = $pdo->prepare("
        SELECT r.IdTransaccion, r.FechaReservas, r.HoraReservas, s.PrecioServicios
        FROM reservas r
        JOIN servicios s ON r.IdServicios = s.IdServicios
        WHERE r.IdReservas = ? AND r.IdUsuarios = ?
    ");
    $stmt->execute([$idReserva, $idUsuario]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        throw new Exception("Reserva no encontrada.");
    }

    // Verificar que la reserva tenga un pago asociado
    if (empty($reserva['IdTransaccion'])) {
        // Si es una reserva antigua o manual sin pago online, solo cancelamos en BD
        $stmt_update = $pdo->prepare("UPDATE reservas SET EstadoReservas = 'Cancelado' WHERE IdReservas = ?");
        $stmt_update->execute([$idReserva]);
        echo json_encode(['success' => true, 'message' => 'Reserva cancelada (sin reembolso monetario porque no hubo pago online).']);
        exit;
    }

    $id_transaccion = $reserva['IdTransaccion'];
    $precio_total_soles = $reserva['PrecioServicios'];
    $monto_total_centavos = $precio_total_soles * 100;
    
    // Calcular tiempo restante
    $fechaReserva = new DateTime($reserva['FechaReservas'] . ' ' . $reserva['HoraReservas']);
    $ahora = new DateTime();
    // Diferencia en minutos
    $minutosParaCita = ($fechaReserva->getTimestamp() - $ahora->getTimestamp()) / 60;

    $monto_a_reembolsar = 0;
    $mensaje_exito = "";

    // 3. LÓGICA DE REEMBOLSO (REGLAS DE NEGOCIO)
    if ($minutosParaCita < 0) {
        throw new Exception("No se puede cancelar una cita que ya pasó.");
    } elseif ($minutosParaCita <= 60) {
        // Menos de 1 hora: Reembolso del 50%
        $monto_a_reembolsar = round($monto_total_centavos / 2);
        $mensaje_exito = "Reserva cancelada. Se ha procesado un reembolso del 50% a tu tarjeta.";
    } else {
        // Más de 1 hora: Reembolso del 100%
        $monto_a_reembolsar = $monto_total_centavos;
        $mensaje_exito = "Reserva cancelada. Se ha procesado un reembolso completo a tu tarjeta.";
    }

    // 4. EJECUTAR EL REEMBOLSO EN STRIPE
    $stripe = new \Stripe\StripeClient('sk_test_51STaglL0CpOiDccydVmZOjCofJRo6x4VLSyQ4TCMasvuVAxRcwvFe1bBaSiZZ2aAGTHtUVEubZhlcRIxMH9Zj0lF00KgPEt2Sg');
    
    $stripe->refunds->create([
        'payment_intent' => $id_transaccion,
        'amount' => (int)$monto_a_reembolsar
    ]);

    // 5. ACTUALIZAR EL ESTADO EN LA BASE DE DATOS
    $motivo = $_POST['motivo'] ?? 'Sin motivo especificado'; // Recibimos el motivo

    $stmt_update = $pdo->prepare("UPDATE reservas SET EstadoReservas = 'Cancelado', MotivoCancelacion = ? WHERE IdReservas = ?");
    $stmt_update->execute([$motivo, $idReserva]);

    echo json_encode(['success' => true, 'message' => $mensaje_exito]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al procesar la cancelación: ' . $e->getMessage()]);
}
?>