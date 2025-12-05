<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$barberoId = $_POST['barbero_id'];
$currentDateTime = new DateTime($_POST['datetime']);
$duration = (int)$_POST['duration'];

// Calcular inicio y fin del siguiente slot
$nextSlotStart = clone $currentDateTime;
$nextSlotStart->add(new DateInterval("PT{$duration}M"));
$nextSlotEnd = clone $nextSlotStart;
$nextSlotEnd->add(new DateInterval("PT{$duration}M"));

$fecha = $nextSlotStart->format('Y-m-d');

// Buscar conflictos
$stmt = $pdo->prepare("
    SELECT r.HoraReservas, s.DuracionMinutos FROM reservas r
    JOIN servicios s ON r.IdServicios = s.IdServicios
    WHERE r.IdBarberos = ? AND r.FechaReservas = ? AND r.EstadoReservas NOT IN ('Cancelado', 'No Completado')
");
$stmt->execute([$barberoId, $fecha]);
$reservasExistentes = $stmt->fetchAll();

$isAvailable = true;
foreach ($reservasExistentes as $reserva) {
    $reservaExistenteInicio = new DateTime("$fecha {$reserva['HoraReservas']}");
    $reservaExistenteFin = (clone $reservaExistenteInicio)->add(new DateInterval("PT{$reserva['DuracionMinutos']}M"));
    if ($nextSlotStart < $reservaExistenteFin && $nextSlotEnd > $reservaExistenteInicio) {
        $isAvailable = false;
        break;
    }
}

echo json_encode(['nextSlotAvailable' => $isAvailable, 'nextSlotTime' => $nextSlotStart->format('H:i')]);