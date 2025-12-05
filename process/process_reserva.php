<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/reservas.php');
    exit;
}

$idServicio = $_POST['id_servicio'];
$idBarbero = $_POST['id_barbero'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$idUsuario = $_SESSION['user_id'] ?? null;

if (!$idUsuario) {
    header('Location: ../pages/login.php?error=Debes iniciar sesión para reservar.');
    exit;
}

// 1. VALIDACIÓN DE FECHA/HORA PASADA (SEGURIDAD DEL SERVIDOR)
$ahora = new DateTime();
$fechaSeleccionada = new DateTime("$fecha $hora");
if ($fechaSeleccionada < $ahora) {
    header('Location: ../pages/reservas.php?error=No puedes reservar en una fecha u hora pasada.');
    exit;
}

// 2. VALIDACIÓN ANTI-COLISIÓN MEJORADA (CORRIGE SUPERPOSICIÓN)
// Obtener duración del nuevo servicio
$stmt = $pdo->prepare("SELECT DuracionMinutos FROM servicios WHERE IdServicios = ?");
$stmt->execute([$idServicio]);
$duracion = $stmt->fetchColumn();
if (!$duracion) {
    header('Location: ../pages/reservas.php?error=Servicio no válido.');
    exit;
}
date_default_timezone_set('America/Lima'); 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/reservas.php');
    exit;
}

// Calcular el intervalo de tiempo de la nueva reserva
$nuevaReservaInicio = new DateTime("$fecha $hora");
$nuevaReservaFin = (clone $nuevaReservaInicio)->add(new DateInterval("PT{$duracion}M"));

// Obtener todas las reservas existentes para ese día y barbero
$stmt = $pdo->prepare("
    SELECT r.HoraReservas, s.DuracionMinutos FROM reservas r
    JOIN servicios s ON r.IdServicios = s.IdServicios
    WHERE r.IdBarberos = ? AND r.FechaReservas = ? AND r.EstadoReservas NOT IN ('Cancelado', 'No Completado')
");
$stmt->execute([$idBarbero, $fecha]);
$reservasExistentes = $stmt->fetchAll();

$conflicto = false;
foreach ($reservasExistentes as $reserva) {
    // Calcular el intervalo de tiempo de cada reserva existente
    $reservaExistenteInicio = new DateTime("$fecha {$reserva['HoraReservas']}");
    $reservaExistenteFin = (clone $reservaExistenteInicio)->add(new DateInterval("PT{$reserva['DuracionMinutos']}M"));

    // La fórmula correcta para detectar superposición es: (InicioA < FinB) y (FinA > InicioB)
    if ($nuevaReservaInicio < $reservaExistenteFin && $nuevaReservaFin > $reservaExistenteInicio) {
        $conflicto = true;
        break; // Si se encuentra un conflicto, no es necesario seguir buscando
    }
}

if ($conflicto) {
    header('Location: ../pages/reservas.php?error=El horario seleccionado ya no está disponible. Por favor, elige otro.');
    exit;
}

// --- FIN DE LA VALIDACIÓN ---

// Si no hay conflicto, insertar la reserva
$stmt = $pdo->prepare("INSERT INTO reservas (IdUsuarios, IdServicios, IdBarberos, FechaReservas, HoraReservas, EstadoReservas) VALUES (?, ?, ?, ?, ?, 'Confirmado')");
$stmt->execute([$idUsuario, $idServicio, $idBarbero, $fecha, $hora]);

header('Location: ../pages/perfil.php?success=reserva#v-pills-reservas-tab');
exit;