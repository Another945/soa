<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/../conexion.php';

date_default_timezone_set('America/Lima');
$ID_CLIENTE_MANUAL = 9999; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ... (La parte de 'venta_producto' NO cambia, déjala igual) ...
    if ($_POST['action'] === 'venta_producto') {
        // ... tu código de venta de productos ...
    }

    // --- OPCIÓN 2: CITA RÁPIDA (CON VALIDACIÓN DE HORARIO) ---
    if ($_POST['action'] === 'cita_rapida') {
        $idServicio = $_POST['id_servicio'];
        $idBarbero = $_POST['id_barbero'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];

        // 1. VALIDAR FECHA PASADA
        $ahora = new DateTime();
        $fechaSeleccionada = new DateTime("$fecha $hora");

        if ($fechaSeleccionada < $ahora) {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error: No se puede agendar en una fecha u hora pasada.'];
            header("Location: ../nueva_venta.php"); exit;
        }

        // 2. NUEVO: VALIDAR RANGO DE HORARIO (09:00 - 20:00)
        $hora_timestamp = strtotime($hora);
        $hora_inicio = strtotime('09:00:00');
        $hora_fin = strtotime('20:00:00');

        if ($hora_timestamp < $hora_inicio || $hora_timestamp > $hora_fin) {
            $_SESSION['message'] = ['type' => 'warning', 'text' => 'Error: La hora debe estar entre las 9:00 AM y las 8:00 PM.'];
            header("Location: ../nueva_venta.php"); exit;
        }

        // 3. INSERTAR RESERVA
        $stmt = $conn->prepare("INSERT INTO reservas (IdUsuarios, IdServicios, IdBarberos, FechaReservas, HoraReservas, EstadoReservas, DescripcionReservas) VALUES (?, ?, ?, ?, ?, 'Confirmado', 'Reserva Manual en Local')");
        $stmt->bind_param("iiiss", $ID_CLIENTE_MANUAL, $idServicio, $idBarbero, $fecha, $hora);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Cita agendada correctamente.'];
        } else {
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error al agendar cita.'];
        }
        header("Location: ../nueva_venta.php"); exit;
    }
}
?>