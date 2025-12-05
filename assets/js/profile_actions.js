document.addEventListener('DOMContentLoaded', function() {

    // --- CÓDIGO DE CANCELAR RESERVAS (Se mantiene igual) ---
    document.body.addEventListener('click', function(e) {
        if (e.target.matches('.cancel-btn')) {
            const button = e.target;
            const id = button.dataset.id;
            const datetime = new Date(button.dataset.datetime);
            const duration = button.dataset.duration;
            const barberoId = button.dataset.barberoid;
            const now = new Date();
            const diffMinutes = (datetime - now) / (1000 * 60);

            if (diffMinutes <= 60) {
                showFinalCancelAlert(id, "Tu cita es en menos de una hora. Si cancelas, el reembolso será solo del 50%. ¿Estás seguro?");
            } else {
                checkNextSlot(id, datetime.toISOString(), duration, barberoId);
            }
        }
    });
    // ... (Mantén aquí tus funciones checkNextSlot, showFinalCancelAlert, etc.) ...
    
    function checkNextSlot(id, datetime, duration, barberoId) {
        const formData = new FormData();
        formData.append('datetime', datetime);
        formData.append('duration', duration);
        formData.append('barbero_id', barberoId);
        fetch('../process/check_next_slot.php', { method: 'POST', body: formData })
        .then(res => res.json()).then(data => {
            if (data.nextSlotAvailable) {
                Swal.fire({ title: '¿Aplazar?', html: `El siguiente turno está libre. ¿Aplazar a las <b>${data.nextSlotTime}</b>?`, showDenyButton: true, showCancelButton: true, confirmButtonText: 'Sí, Aplazar', denyButtonText: `Cancelar Reserva` }).then((r) => {
                    if (r.isConfirmed) postponeReservation(id, data.nextSlotTime);
                    else if (r.isDenied) showFinalCancelAlert(id, "¿Cancelar la reserva?");
                });
            } else { showFinalCancelAlert(id, "¿Cancelar la reserva?"); }
        });
    }
    function showFinalCancelAlert(id, message) {
        Swal.fire({
            title: 'Confirmar Cancelación',
            text: message,
            icon: 'warning',
            input: 'textarea', // <--- ESTO AGREGA EL CUADRO DE TEXTO
            inputLabel: 'Por favor, indícanos el motivo:',
            inputPlaceholder: 'Ej: Surgió una emergencia...',
            inputAttributes: {
                'aria-label': 'Escribe tu motivo aquí'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirmar Cancelación',
            cancelButtonText: 'Volver',
            inputValidator: (value) => {
                if (!value) {
                    return '¡Necesitas escribir un motivo para cancelar!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Pasamos el ID y el motivo (result.value) a la función
                cancelReservation(id, result.value);
            }
        });
    }

    // Actualizamos esta función para recibir el motivo
    function cancelReservation(id, motivo) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('motivo', motivo); // <--- ENVIAMOS EL MOTIVO AL SERVER

        // Mostrar cargando...
        Swal.fire({ title: 'Procesando...', didOpen: () => Swal.showLoading() });

        fetch('../process/cancel_reservation.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('¡Cancelado!', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Error', 'Error de conexión', 'error');
        });
    }
    
    function postponeReservation(id, time) { const fd = new FormData(); fd.append('id', id); fd.append('new_time', time); fetch('../process/postpone_reservation.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => { d.success ? Swal.fire('¡Listo!', d.message, 'success').then(() => location.reload()) : Swal.fire('Error', d.message, 'error'); }); }


    // ==========================================================
    // LÓGICA DEL MODAL (NUEVA VERSIÓN CON DEPURACIÓN)
    // ==========================================================
    const saleDetailModal = document.getElementById('modalDetalleVenta');
    
    if (saleDetailModal) {
        const modalBody = document.getElementById('modalSaleDetailsBody');
        const modalTransactionId = document.getElementById('modalTransactionId');

        saleDetailModal.addEventListener('show.bs.modal', function(event) {
            const row = event.relatedTarget;
            const saleId = row.getAttribute('data-id'); // Usamos getAttribute para mayor seguridad
            
            console.log("Intentando cargar venta ID:", saleId); // Para ver en la consola (F12)

            // Limpiar y mostrar cargando
            modalBody.innerHTML = '<tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-warning" role="status"></div><br>Cargando detalles...</td></tr>';
            modalTransactionId.textContent = 'ID: ...';

            // La ruta relativa es clave. Asumimos que estamos en /pages/perfil.php
            const url = `../process/get_sale_details.php?id=${saleId}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        // Si el archivo no existe (404) o hay error de servidor (500)
                        throw new Error(`Error HTTP: ${response.status} (${response.statusText})`);
                    }
                    return response.text(); // Obtenemos texto primero para ver si es JSON válido
                })
                .then(text => {
                    try {
                        return JSON.parse(text); // Intentamos convertir a JSON
                    } catch (e) {
                        // Si falla, mostramos qué devolvió el servidor (probablemente un error de PHP visible)
                        throw new Error("El servidor no devolvió JSON válido. Respuesta: " + text.substring(0, 100) + "...");
                    }
                })
                .then(data => {
                    if (data.success) {
                        let html = '';
                        modalTransactionId.textContent = `ID Transacción: ${data.details[0].IdTransaccion}`;

                        data.details.forEach(item => {
                            const precio = parseFloat(item.PrecioDetalleVentas);
                            const cantidad = parseInt(item.CantidadDetalleVentas);
                            const subtotal = precio * cantidad;
                            
                            html += `
                                <tr>
                                    <td>${item.NombreDetalleVentas}</td>
                                    <td class="text-center">${cantidad}</td>
                                    <td class="text-end">S/ ${precio.toFixed(2)}</td>
                                    <td class="text-end fw-bold text-warning">S/ ${subtotal.toFixed(2)}</td>
                                </tr>
                            `;
                        });
                        modalBody.innerHTML = html;
                    } else {
                        modalTransactionId.textContent = 'Aviso';
                        modalBody.innerHTML = `<tr><td colspan="4" class="text-center">${data.message}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                    // Mostrar alerta visual al usuario
                    modalTransactionId.textContent = 'Error';
                    modalBody.innerHTML = `<tr><td colspan="4" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> ${error.message}</td></tr>`;
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        text: `No se pudieron cargar los detalles. \n\nCausa técnica: ${error.message}`,
                        footer: 'Revisa que el archivo process/get_sale_details.php exista.'
                    });
                });
        });
    }
});