<?php
include 'layouts/header.php';
include 'controllers/reservas-dash.php'; // Carga los datos en $reservas
?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid p-4">
    <h1 class="mb-4">Gestión de Reservas</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['message']['text']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['message']); endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Listado de Reservas</h5>
        </div>
        <div class="card-body">
            <form class="d-flex mb-3" method="get" action="reservas.php">
                <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
                <button class="btn btn-outline-warning" type="submit">Buscar</button>
            </form>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <?php if ($_SESSION['user_type'] === 'administrador'): ?>
                                <th>Barbero</th>
                            <?php endif; ?>
                            <th>Fecha y Hora</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reservas)): ?>
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reserva['NombreUsuarios'] . ' ' . $reserva['ApellidoUsuarios']); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['TipoServicios']); ?></td>
                                    
                                    <?php if ($_SESSION['user_type'] === 'administrador'): ?>
                                        <td><?php echo $reserva['NombreEmpleado'] ? htmlspecialchars($reserva['NombreEmpleado']) : '<em>N/A</em>'; ?></td>
                                    <?php endif; ?>
                                    
                                    <td><?php echo date('d/m/Y h:i A', strtotime($reserva['FechaReservas'] . ' ' . $reserva['HoraReservas'])); ?></td>
                                    
                                    <td>
                                        <span class="badge <?php echo getBadgeClass($reserva['EstadoReservas']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($reserva['EstadoReservas'])); ?>
                                        </span>
                                        <?php if ($reserva['EstadoReservas'] === 'Cancelado' && !empty($reserva['MotivoCancelacion'])): ?>
                                            <button class="btn btn-outline-danger btn-sm ms-2" onclick="verMotivo('<?php echo htmlspecialchars(addslashes($reserva['MotivoCancelacion'])); ?>')">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">Acciones</button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="controllers/reservas-dash.php?action=cambiar_estado&id=<?php echo $reserva['IdReservas']; ?>&estado=Confirmado">Confirmar</a></li>
                                                <li><a class="dropdown-item" href="controllers/reservas-dash.php?action=cambiar_estado&id=<?php echo $reserva['IdReservas']; ?>&estado=Completado">Completar</a></li>
                                                <li><a class="dropdown-item" href="controllers/reservas-dash.php?action=cambiar_estado&id=<?php echo $reserva['IdReservas']; ?>&estado=Cancelado">Cancelar</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="controllers/reservas-dash.php?action=eliminar&id=<?php echo $reserva['IdReservas']; ?>" onclick="return confirm('¿Eliminar permanentemente?');">Eliminar</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">No se encontraron reservas recientes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Vista de Calendario</h5>
        </div>
        <div class="card-body">
            <div id="calendario-admin"></div>
        </div>
    </div>
</div>

<script>
    function verMotivo(motivo) {
        Swal.fire({ title: 'Motivo', text: motivo, icon: 'info', confirmButtonColor: '#ffc107' });
        Swal.fire({
            title: 'Motivo de Cancelación',
            text: motivo,
            icon: 'info',
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Cerrar'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendario-admin');
        const barberFilter = document.getElementById('barber-filter');
        let initialBarberId = '<?php echo ($_SESSION['user_type'] === 'empleado') ? $_SESSION['user_id'] : 'all'; ?>';

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay,dayGridMonth'
            },
            allDaySlot: false,
            slotMinTime: '09:00:00',
            slotMaxTime: '21:00:00',
            events: {
                url: '../process/get_availability.php',
                extraParams: function() {
                    let barberId = barberFilter ? barberFilter.value : initialBarberId;
                    return {
                        barbero_id: barberId,
                        context: 'admin' // Contexto admin para ver colores y cancelados
                    };
                }
            },
            eventClick: function(info) {
                // Si es una cita cancelada, mostrar el motivo al hacer clic en el calendario también
                if (info.event.extendedProps.estado === 'Cancelado') {
                    const motivo = info.event.extendedProps.motivo || 'Sin motivo especificado.';
                    verMotivo(motivo);
                }
            },
            eventDidMount: function(info) {
                // Cambiar cursor si es clickeable
                if (info.event.extendedProps.estado === 'Cancelado') {
                    info.el.style.cursor = 'pointer';
                }
            }
        });

        calendar.render();

        if (barberFilter) {
            barberFilter.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Manejador de clics para los botones de acción
    document.querySelectorAll('.action-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const id = this.dataset.id;
            const estado = this.dataset.estado || ''; // Solo si es cambiar_estado

            let confirmMessage = "¿Estás seguro?";
            if (action === 'eliminar') confirmMessage = "¡Esta acción es irreversible!";
            else confirmMessage = `¿Cambiar estado a ${estado}?`;

            Swal.fire({
                title: confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FFC107',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la petición AJAX
                    const url = `controllers/reservas-dash.php?action=${action}&id=${id}&estado=${estado}`;
                    
                    fetch(url)
                    .then(response => {
                        if (response.ok) {
                            // Recargar la página para ver los cambios
                            window.location.reload();
                        } else {
                            Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error de conexión.', 'error');
                    });
                }
            });
        });
    });
});
</script>
<?php include 'layouts/footer.php'; ?>