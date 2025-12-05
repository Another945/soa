<?php 
require_once '../includes/functions.php'; 
$currentUser = getCurrentUser();

$selectedServiceId = $_GET['service_id'] ?? null;
$selectedBarberId = $_GET['barber_id'] ?? null;
?>
<?php include '../includes/header.php'; ?>

<script src="https://js.stripe.com/v3/"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/es.global.min.js'></script>

<style>
    /* Estilo del botón volver */
    .btn-back {
        display: inline-flex;
        align-items: center;
        color: #adb5bd;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 30px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background-color: rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .btn-back:hover {
        background-color: var(--primary-yellow);
        color: #000;
        border-color: var(--primary-yellow);
        transform: translateX(-5px);
    }
    .btn-back i { margin-right: 8px; }

    /* Calendario */
    #calendario {
        min-height: 600px;
        background-color: #1e1e1e;
        border-radius: 15px;
        padding: 20px;
        border: 1px solid rgba(255, 193, 7, 0.2);
        color: #f8f9fa;
    }
    .fc .fc-toolbar-title { color: #FFC107; text-transform: capitalize; } /* Título capitalizado */
    .fc .fc-button { background-color: #333; border-color: #555; color: #fff; text-transform: capitalize; }
    .fc .fc-button:hover { background-color: #444; }
    .fc .fc-button-primary:not(:disabled).fc-button-active { background-color: #FFC107; border-color: #FFC107; color: #000; font-weight: 600; }
    .fc .fc-col-header-cell-cushion, .fc .fc-timegrid-axis-cushion { color: #adb5bd; text-decoration: none; }
    
    .fc-event.fc-selection-event {
        background-color: #FFC107 !important; border-color: #FFC107 !important; color: #000 !important; font-weight: bold;
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
    }
    
    .fc-bg-event {
        background: repeating-linear-gradient(45deg, rgba(108, 117, 125, 0.15), rgba(108, 117, 125, 0.15) 10px, rgba(108, 117, 125, 0.25) 10px, rgba(108, 117, 125, 0.25) 20px) !important;
        opacity: 1; border-radius: 4px;
    }
</style>

<div class="container my-5">
    <div class="mb-4">
        <a href="servicios.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Volver a Servicios
        </a>
    </div>

    <h1 class="section-title">Reservar Cita</h1>
    <div class="row g-5">
        <div class="col-lg-4">
            <div class="item-card p-4">
                <form id="reservaForm">
                    <input type="hidden" name="nombre" value="<?php echo $currentUser ? htmlspecialchars($currentUser['NombreUsuarios']) : ''; ?>">
                    <input type="hidden" name="apellido" value="<?php echo $currentUser ? htmlspecialchars($currentUser['ApellidoUsuarios']) : ''; ?>">
                    <input type="hidden" name="email" value="<?php echo $currentUser ? htmlspecialchars($currentUser['EmailUsuarios']) : ''; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-warning">1. Servicio Seleccionado</label>
                        <?php 
                        $servicioFijo = null;
                        if ($selectedServiceId) {
                            $stmt = $pdo->prepare("SELECT * FROM servicios WHERE IdServicios = ?");
                            $stmt->execute([$selectedServiceId]);
                            $servicioFijo = $stmt->fetch(PDO::FETCH_ASSOC);
                        }

                        if ($servicioFijo): 
                        ?>
                            <input type="text" class="form-control bg-dark text-white border-secondary mb-2" 
                                   value="<?php echo htmlspecialchars($servicioFijo['TipoServicios']); ?> (S/ <?php echo number_format($servicioFijo['PrecioServicios'], 2); ?>)" 
                                   readonly disabled>
                            <input type="hidden" id="servicio" name="id_servicio" value="<?php echo $selectedServiceId; ?>">
                            
                            <div class="alert alert-warning text-center py-2">
                                <small class="text-dark fw-bold" id="duracion-display" data-duration="<?php echo $servicioFijo['DuracionMinutos']; ?>">
                                    <i class="bi bi-clock"></i> Duración: <?php echo $servicioFijo['DuracionMinutos']; ?> min
                                </small>
                            </div>

                        <?php else: ?>
                            <select class="form-select bg-dark text-white border-secondary" id="servicio" name="id_servicio" required>
                                <option value="" data-duration="0">-- Elige un servicio --</option>
                                <?php 
                                $stmt = $pdo->query("SELECT * FROM servicios WHERE ActivoServicios = '1' ORDER BY TipoServicios");
                                while ($serv = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <option value="<?php echo $serv['IdServicios']; ?>" data-duration="<?php echo $serv['DuracionMinutos']; ?>">
                                        <?php echo htmlspecialchars($serv['TipoServicios']); ?> (S/ <?php echo number_format($serv['PrecioServicios'], 2); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-warning">2. Elige tu Barbero</label>
                        <select class="form-select bg-dark text-white border-secondary" id="barbero" name="id_barbero" required>
                            <option value="">-- Elige un barbero --</option>
                            <?php 
                            $stmt = $pdo->query("SELECT * FROM empleados WHERE RolEmpleados = 'barbero' AND Activo = 1");
                            while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)):
                                $isSelected = ($emp['IdEmpleados'] == $selectedBarberId) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $emp['IdEmpleados']; ?>" <?php echo $isSelected; ?>>
                                    <?php echo htmlspecialchars($emp['NombreEmpleado'] . ' ' . $emp['ApellidoEmpleados']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <input type="hidden" id="fecha" name="fecha" required>
                    <input type="hidden" id="hora" name="hora" required>

                    <p class="text-muted small mt-3 mb-3">3. Haz clic en un horario libre en el calendario.</p>

                    <button type="button" id="submitReserva" class="btn btn-custom-yellow w-100 py-2 fw-bold">Pagar y Confirmar</button>
                </form>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div id="calendario"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendario');
    const barberoSelect = document.getElementById('barbero');
    const servicioInput = document.getElementById('servicio'); 
    const fechaInput = document.getElementById('fecha');
    const horaInput = document.getElementById('hora');
    const form = document.getElementById('reservaForm');
    const submitButton = document.getElementById('submitReserva');
    
    function getDuration() {
        const fixedDisplay = document.getElementById('duracion-display');
        if (fixedDisplay) {
            return parseInt(fixedDisplay.dataset.duration);
        } else if (servicioInput.tagName === 'SELECT' && servicioInput.selectedIndex > 0) {
            return parseInt(servicioInput.options[servicioInput.selectedIndex].dataset.duration);
        }
        return 0;
    }

    const stripe = Stripe('pk_test_51STaglL0CpOiDccy9CPHICEfw0qKoSKcfzwtNTtfEw4Rb7ejSrxd9TykC20DpKfvfMvmhVkd8JMHD6vv1ZWg5WMu00PpNfkGvL');
    
    let tempSelectionEvent = null;
    const todayStr = new Date().toISOString().split('T')[0];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'es', // IDIOMA ESPAÑOL ACTIVADO
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,timeGridDay' },
        allDaySlot: false,
        slotMinTime: '09:00:00',
        slotMaxTime: '21:00:00',
        validRange: { start: todayStr },
        height: 'auto', contentHeight: 600,
        businessHours: { daysOfWeek: [0, 1, 2, 3, 4, 5, 6], startTime: '09:00', endTime: '21:00' },
        events: {
            url: '../process/get_availability.php',
            extraParams: () => ({ barbero_id: barberoSelect.value })
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault(); 
            if (info.event.display === 'background') {
                Swal.fire({ icon: 'error', title: 'Ocupado', text: 'Este horario ya está reservado.', confirmButtonColor: '#FFC107' });
            }
        },
        dateClick: function(info) {
            const now = new Date();
            const selectedDate = new Date(info.dateStr);

            if (selectedDate < new Date(now.getTime() - 60000)) { 
                Swal.fire({ icon: 'warning', title: 'Horario Pasado', text: 'No puedes reservar en el pasado.', confirmButtonColor: '#FFC107' });
                return;
            }

            if (!servicioInput.value) {
                Swal.fire('Atención', 'Selecciona un servicio primero.', 'warning');
                return;
            }
            
            const duration = getDuration();
            const start = info.date;
            const end = new Date(start.getTime() + duration * 60000);

            const allEvents = calendar.getEvents();
            let isOverlapping = false;
            for (const event of allEvents) {
                if (event.display === 'background') {
                    if (start < event.end && end > event.start) {
                        isOverlapping = true;
                        break;
                    }
                }
            }

            if (isOverlapping) {
                Swal.fire({ icon: 'error', title: 'Conflicto', text: 'El servicio no cabe en este espacio libre.', confirmButtonColor: '#FFC107' });
                return;
            }

            if (tempSelectionEvent) tempSelectionEvent.remove();
            
            tempSelectionEvent = calendar.addEvent({ 
                title: 'Tu Selección', start: start, end: end, className: 'fc-selection-event' 
            });
            
            const localDate = new Date(selectedDate.getTime() - (selectedDate.getTimezoneOffset() * 60000));
            const isoString = localDate.toISOString();
            fechaInput.value = isoString.slice(0, 10);
            horaInput.value = isoString.slice(11, 16);
        }
    });

    calendar.render();

    barberoSelect.addEventListener('change', function() {
        if (tempSelectionEvent) { tempSelectionEvent.remove(); tempSelectionEvent = null; }
        calendar.refetchEvents();
    });
    
    if(servicioInput.tagName === 'SELECT'){
        servicioInput.addEventListener('change', function() {
             if (tempSelectionEvent) { tempSelectionEvent.remove(); tempSelectionEvent = null; fechaInput.value=''; horaInput.value=''; }
        });
    }

    submitButton.addEventListener('click', function(e) {
        e.preventDefault();
        if (!servicioInput.value || !barberoSelect.value || !fechaInput.value || !horaInput.value) {
            Swal.fire('Faltan Datos', 'Selecciona servicio, barbero y hora en el calendario.', 'warning');
            return;
        }

        submitButton.disabled = true;
        submitButton.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin"></i>';
        const formData = new FormData(form);

        fetch('../process/create_reservation_payment.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.id) {
                stripe.redirectToCheckout({ sessionId: data.id });
            } else {
                Swal.fire('Error', data.error || 'Error al procesar.', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Pagar y Confirmar';
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Error', 'Error de conexión.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Pagar y Confirmar';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>