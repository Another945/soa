<?php
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Validar ID de reserva
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID de reserva inválido.</div>";
    include '../includes/footer.php';
    exit;
}

$id_reserva = (int)$_GET['id'];

// Obtener detalles de la reserva
$stmt = $pdo->prepare("
    SELECT r.*, 
           s.NombreServicio, s.PrecioServicio, 
           c.NombreClientes, c.ApellidoClientes, c.EmailClientes, c.TelefonoClientes,
           e.NombreEmpleado, e.ApellidoEmpleados
    FROM reservas r
    JOIN servicios s ON r.IdServicios = s.IdServicios
    JOIN clientes c ON r.IdClientes = c.IdClientes
    LEFT JOIN empleados e ON r.IdBarberos = e.IdEmpleados
    WHERE r.IdReservas = ?
");
$stmt->execute([$id_reserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo "<div class='alert alert-warning'>Reserva no encontrada.</div>";
    include '../includes/footer.php';
    exit;
}
?>

<style>
/* ====== Estilo Barbería ====== */
body {
    background-color: #1a1a1a;
    font-family: 'Poppins', sans-serif;
}

h2, h3 {
    color: #d4af37; /* Dorado */
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card {
    background: #2b2b2b;
    border: 1px solid #d4af37;
    border-radius: 15px;
}

.badge {
    font-size: 1em;
    padding: 6px 12px;
}

/* ✅ Texto blanco solo en los detalles */
.card p {
    color: #ffffff;
    font-size: 15px;
    margin-bottom: 6px;
}

/* Calendario estilo barbería */
#calendar {
    background-color: #222;
    border: 1px solid #d4af37;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
}

/* ====== FullCalendar Estilo ====== */
.fc .fc-toolbar-title {
    color: #f8f8f8;
    font-weight: bold;
}

.fc .fc-button {
    background-color: #d4af37;
    border: none;
    color: #000;
    font-weight: bold;
    border-radius: 6px;
}

.fc .fc-button:hover {
    background-color: #b68f2a;
}

.fc-daygrid-day-number {
    color: #f8f8f8;
}

.fc-event {
    background-color: #d4af37 !important;
    color: #000 !important;
    border: none !important;
    font-weight: 600;
    text-align: center;
}
</style>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Detalles de tu Reserva</h2>

    <div class="card shadow p-4 mb-5">
        <h4 class="text-warning text-center mb-3">
            <?php echo htmlspecialchars($reserva['NombreServicio']); ?>
        </h4>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($reserva['NombreClientes'] . ' ' . $reserva['ApellidoClientes']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($reserva['EmailClientes']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($reserva['TelefonoClientes']); ?></p>
        <p><strong>Barbero:</strong> 
            <?php echo $reserva['NombreEmpleado'] 
                ? htmlspecialchars($reserva['NombreEmpleado'] . ' ' . $reserva['ApellidoEmpleados'])
                : '<em>No asignado</em>'; ?>
        </p>
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($reserva['FechaReservas']); ?></p>
        <p><strong>Hora:</strong> <?php echo htmlspecialchars($reserva['HoraReservas']); ?></p>
        <p><strong>Estado:</strong> 
            <span class="badge 
                <?php echo $reserva['EstadoReservas'] == 'Confirmado' ? 'bg-success' : ($reserva['EstadoReservas'] == 'Cancelado' ? 'bg-danger' : 'bg-warning'); ?>">
                <?php echo htmlspecialchars($reserva['EstadoReservas']); ?>
            </span>
        </p>
        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($reserva['DescripcionReservas'] ?? 'Sin descripción.'); ?></p>
        <p><strong>Precio:</strong> $<?php echo number_format($reserva['PrecioServicio'], 2); ?></p>
    </div>

    <h3 class="text-center mb-3">Calendario de la Reserva</h3>
    <div id="calendar" style="max-width: 900px; margin: 0 auto;"></div>
</div>

<!-- Librerías FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'spanish', // 🇪🇸 Calendario completamente en español
        height: 550,
        themeSystem: 'standard',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            {
                title: '<?php echo htmlspecialchars($reserva["NombreServicio"]); ?>',
                start: '<?php echo $reserva["FechaReservas"] . "T" . $reserva["HoraReservas"]; ?>',
                end: '<?php echo $reserva["FechaReservas"] . "T" . date("H:i:s", strtotime($reserva["HoraReservas"]) + 3600); ?>',
                description: '<?php echo htmlspecialchars($reserva["DescripcionReservas"] ?? "Reserva de servicio"); ?>'
            }
        ],
        eventDidMount: function(info) {
            new bootstrap.Tooltip(info.el, {
                title: info.event.title + " - " + info.event.extendedProps.description,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        },
        eventClick: function(info) {
            alert("📅 Reserva: " + info.event.title + "\n🕒 " + info.event.start.toLocaleString('es-ES'));
        }
    });

    calendar.render();
});
</script>

<?php include '../includes/footer.php'; ?>
