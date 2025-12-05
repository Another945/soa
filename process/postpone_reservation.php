<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$idReserva = $_POST['id'];
$newTime = $_POST['new_time'];

$stmt = $pdo->prepare("UPDATE reservas SET HoraReservas = ? WHERE IdReservas = ?");
if ($stmt->execute([$newTime, $idReserva])) {
    echo json_encode(['success' => true, 'message' => '¡Tu reserva ha sido aplazada!']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo aplazar la reserva.']);
}