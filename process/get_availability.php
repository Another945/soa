<?php
require_once '../includes/functions.php';
header('Content-Type: application/json');

$barberoId = $_GET['barbero_id'] ?? null;
$context = $_GET['context'] ?? 'public';
$start_date = $_GET['start'] ?? null;
$end_date = $_GET['end'] ?? null;

// Lógica de acceso
$isAdminView = ($context === 'admin');
$isPublicView = ($context === 'public' && is_numeric($barberoId));

if (!$isAdminView && !$isPublicView) {
    echo json_encode([]);
    exit;
}

// Consulta
$sql = "
    SELECT r.FechaReservas, r.HoraReservas, r.EstadoReservas, r.MotivoCancelacion,
           s.DuracionMinutos, e.NombreEmpleado, r.IdBarberos
    FROM reservas r
    JOIN servicios s ON r.IdServicios = s.IdServicios
    JOIN empleados e ON r.IdBarberos = e.IdEmpleados
    WHERE 1=1
";
$params = [];

// Filtros
if ($context === 'public') {
    // Clientes no ven cancelados
    $sql .= " AND r.EstadoReservas NOT IN ('Cancelado', 'No Completado')";
}

if (is_numeric($barberoId)) {
    $sql .= " AND r.IdBarberos = ?";
    $params[] = $barberoId;
}

if ($start_date && $end_date) {
    $sql .= " AND r.FechaReservas BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventos = [];
foreach ($reservas as $reserva) {
    $start = $reserva['FechaReservas'] . 'T' . $reserva['HoraReservas'];
    
    // --- CORRECCIÓN AQUÍ: Se eliminó la 'D' extra que causaba el error ---
    try {
        $endDateTime = new DateTime($start);
        $endDateTime->add(new DateInterval('PT' . $reserva['DuracionMinutos'] . 'M'));
        $end = $endDateTime->format('Y-m-d\TH:i:s');
    } catch (Exception $e) {
        continue; // Si falla la fecha, saltamos esta reserva
    }

    $estado = $reserva['EstadoReservas'];

    if ($context === 'public') {
        // Vista Cliente: Gris y anónimo
        $eventos[] = [
            'title'   => 'Ocupado',
            'start'   => $start,
            'end'     => $end,
            'display' => 'background',
            'color'   => '#555'
        ];
    } else { 
        // Vista Admin: Colores por Estado y Nombre Visible
        $color = '#ffc107'; // Amarillo (Pendiente)
        $titulo = $reserva['NombreEmpleado'];

        if ($estado === 'Confirmado' || $estado === 'Completado') {
            $color = '#28a745'; // Verde
            $titulo .= " (OK)";
        } elseif ($estado === 'Cancelado' || $estado === 'No Completado') {
            $color = '#dc3545'; // Rojo
            $titulo .= " (CANCELADO)";
        } else {
            $titulo .= " (Pendiente)";
        }

        $eventos[] = [
            'title'     => $titulo,
            'start'     => $start,
            'end'       => $end,
            'color'     => $color,
            'textColor' => '#fff',
            'extendedProps' => [
                'estado' => $estado,
                'motivo' => $reserva['MotivoCancelacion']
            ]
        ];
    }
}

echo json_encode($eventos);
?>