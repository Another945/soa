<?php
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

// 1. PEGA TU CLAVE SECRETA DE STRIPE (sk_test_...)
\Stripe\Stripe::setApiKey('sk_test_51STaglL0CpOiDccydVmZOjCofJRo6x4VLSyQ4TCMasvuVAxRcwvFe1bBaSiZZ2aAGTHtUVEubZhlcRIxMH9Zj0lF00KgPEt2Sg');

header('Content-Type: application/json');

try {
    if (!isLoggedIn()) {
        throw new Exception("Usuario no autenticado.");
    }

    $idServicio = $_POST['id_servicio'];
    $idBarbero = $_POST['id_barbero'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $idUsuario = $_SESSION['user_id'];

    // 2. VALIDAR EL HORARIO Y OBTENER DATOS
    $stmt = $pdo->prepare("SELECT TipoServicios, PrecioServicios, DuracionMinutos FROM servicios WHERE IdServicios = ?");
    $stmt->execute([$idServicio]);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$servicio) {
        throw new Exception("Servicio no válido.");
    }

    $precio_en_centavos = (int)($servicio['PrecioServicios'] * 100);
    if ($precio_en_centavos <= 0) {
        throw new Exception("El precio del servicio no es válido. Asegúrate de que la columna PrecioServicios esté correcta en la base de datos.");
    }

    $duracion = $servicio['DuracionMinutos'];
    $nuevaReservaInicio = new DateTime("$fecha $hora");
    $nuevaReservaFin = (clone $nuevaReservaInicio)->add(new DateInterval("PT{$duracion}M"));

    $stmt = $pdo->prepare("SELECT r.HoraReservas, s.DuracionMinutos FROM reservas r JOIN servicios s ON r.IdServicios = s.IdServicios WHERE r.IdBarberos = ? AND r.FechaReservas = ? AND r.EstadoReservas NOT IN ('Cancelado', 'No Completado')");
    $stmt->execute([$idBarbero, $fecha]);
    $reservasExistentes = $stmt->fetchAll();

    foreach ($reservasExistentes as $reserva) {
        $reservaExistenteInicio = new DateTime("$fecha {$reserva['HoraReservas']}");
        $reservaExistenteFin = (clone $reservaExistenteInicio)->add(new DateInterval("PT{$reserva['DuracionMinutos']}M"));
        if ($nuevaReservaInicio < $reservaExistenteFin && $nuevaReservaFin > $reservaExistenteInicio) {
            throw new Exception("El horario seleccionado ya no está disponible.");
        }
    }

    // 3. GUARDAR LA RESERVA TEMPORALMENTE EN LA SESIÓN
    $_SESSION['pending_reservation'] = [
        'idUsuario' => $idUsuario,
        'idServicio' => $idServicio,
        'idBarbero' => $idBarbero,
        'fecha' => $fecha,
        'hora' => $hora
    ];

    if (isset($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
    date_default_timezone_set('America/Lima');
    // 4. CREAR LA SESIÓN DE PAGO EN STRIPE
    $protocol = 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $success_url = $protocol . $host . '/SoaProyecto/pages/pago_exitoso.php?session_id={CHECKOUT_SESSION_ID}';
    $cancel_url = $protocol . $host . '/SoaProyecto/pages/reservas.php?cancel=1';

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'pen',
                'product_data' => ['name' => 'Reserva: ' . $servicio['TipoServicios']],
                'unit_amount' => $precio_en_centavos,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
    ]);

    // 5. DEVOLVER EL ID DE LA SESIÓN AL JAVASCRIPT
    echo json_encode(['id' => $session->id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
