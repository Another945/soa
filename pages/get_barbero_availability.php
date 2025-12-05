<?php
require_once '../includes/functions.php';

$barbero_id = $_GET['barbero_id'];

$stmt = $pdo->prepare("
    SELECT FechaReservas, HoraReservas
    FROM reservas
    WHERE IdBarberos = ? AND EstadoReservas != 'Cancelado'
");
$stmt->execute([$barbero_id]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];

foreach ($reservas as $reserva) {
    $events[] = [
        'title' => 'Reservado',
        'start' => $reserva['FechaReservas'] . 'T' . $reserva['HoraReservas'],
        'end' => $reserva['FechaReservas'] . 'T' . date('H:i:s', strtotime($reserva['HoraReservas']) + 1800), // 30 minutos de duración
        'status' => 'ocupado' // Para bloquear la hora
    ];
}

echo json_encode($events);
?>
