<?php include 'layouts/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-4">
    <h1 class="mb-4">Punto de Venta (POS)</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['message']);
    endif; ?>

    <ul class="nav nav-tabs" id="posTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" id="venta-tab" data-bs-toggle="tab" data-bs-target="#venta" type="button">Venta de Productos</button></li>
        <li class="nav-item"><button class="nav-link" id="cita-tab" data-bs-toggle="tab" data-bs-target="#cita" type="button">Cita Rápida (Walk-in)</button></li>
    </ul>

    <div class="tab-content p-4 border border-top-0 bg-white" id="posTabContent">

        <div class="tab-pane fade show active" id="venta" role="tabpanel">
            <form action="controllers/pos_controller.php" method="POST">
                <input type="hidden" name="action" value="venta_producto">
                <div class="row">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Buscar Productos:</label>
                        <select class="form-select mb-3 select2" id="productoSelect" onchange="agregarProductoAlCarrito()">
                            <option value="">-- Escribe para buscar --</option>
                            <?php
                            $prods = $conn->query("SELECT * FROM productos WHERE ActivoProductos = 1 AND StockProductos > 0");
                            while ($p = $prods->fetch_assoc()) {
                                echo "<option value='{$p['IdProductos']}' data-price='{$p['PrecioProductos']}' data-name='{$p['NombreProductos']}'>{$p['NombreProductos']} (S/ {$p['PrecioProductos']} - Stock: {$p['StockProductos']})</option>";
                            }
                            ?>
                        </select>
                        <table class="table table-bordered mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cant.</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody"></tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-center">Total: S/ <span id="totalVenta">0.00</span></h3>
                                <hr>
                                <div class="mb-3">
                                    <label class="fw-bold">Método de Pago</label>
                                    <select name="metodo_pago" class="form-select">
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="Yape/Plin">Yape / Plin</option>
                                        <option value="Tarjeta">Tarjeta (POS Físico)</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success w-100 btn-lg">Confirmar Venta</button>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="carrito_data" id="carritoData">
            </form>
        </div>

        <div class="tab-pane fade" id="cita" role="tabpanel">
            <form id="formCitaManual" action="controllers/pos_controller.php" method="POST">
                <input type="hidden" name="action" value="cita_rapida">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Servicio:</label>
                        <select name="id_servicio" class="form-select select2" required style="width: 100%;">
                            <option value="">-- Buscar servicio --</option>
                            <?php
                            $servs = $conn->query("SELECT * FROM servicios WHERE ActivoServicios = 1");
                            while ($s = $servs->fetch_assoc()) {
                                echo "<option value='{$s['IdServicios']}'>{$s['TipoServicios']} (S/ {$s['PrecioServicios']} - {$s['DuracionMinutos']} min)</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Barbero:</label>
                        <select name="id_barbero" class="form-select select2" required style="width: 100%;">
                            <option value="">-- Buscar barbero --</option>
                            <?php
                            $barbs = $conn->query("SELECT * FROM empleados WHERE RolEmpleados = 'barbero'");
                            while ($b = $barbs->fetch_assoc()) {
                                echo "<option value='{$b['IdEmpleados']}'>{$b['NombreEmpleado']} {$b['ApellidoEmpleados']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" id="inputFecha" name="fecha" class="form-control"
                            value="<?php echo date('Y-m-d'); ?>"
                            min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Hora</label>
                        <input type="time" id="inputHora" name="hora" class="form-control"
                            value="<?php echo date('H:i'); ?>"
                            min="09:00"
                            max="20:00"
                            step="300"
                            required>
                        <div class="form-text">Horario de atención: 9:00 AM - 8:00 PM</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">Agendar Cita (Cliente Manual)</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "Escribe para buscar..."
        });
        $('#productoSelect').on('select2:select', function(e) {
            agregarProductoAlCarrito();
        });

        // --- VALIDACIÓN DE FECHA Y HORA EN CITA MANUAL ---
        document.getElementById('formCitaManual').addEventListener('submit', function(e) {
            const fecha = document.getElementById('inputFecha').value;
            const hora = document.getElementById('inputHora').value;

            if (fecha && hora) {
                const ahora = new Date(); // Fecha actual real
                // Crear fecha seleccionada (concatenar y formatear para que JS la entienda)
                // Se usa 'T' para formato ISO compatible
                const fechaSeleccionada = new Date(fecha + 'T' + hora);

                // Restamos 1 minuto (60000ms) al 'ahora' para dar un pequeño margen de tolerancia
                if (fechaSeleccionada < new Date(ahora.getTime() - 60000)) {
                    e.preventDefault(); // DETIENE EL ENVÍO DEL FORMULARIO
                    Swal.fire({
                        icon: 'error',
                        title: 'Hora Inválida',
                        text: 'No puedes agendar una cita en el pasado. Verifica la fecha y la hora.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            }
        });
    });

    // --- Lógica del Carrito (Sin cambios) ---
    let carrito = [];

    function agregarProductoAlCarrito() {
        const select = document.getElementById('productoSelect');
        const id = select.value;
        if (!id) return;
        const option = select.options[select.selectedIndex];
        const nombre = option.getAttribute('data-name');
        const precio = parseFloat(option.getAttribute('data-price'));
        const existing = carrito.find(p => p.id === id);
        if (existing) {
            existing.cantidad++;
        } else {
            carrito.push({
                id,
                nombre,
                precio,
                cantidad: 1
            });
        }
        renderCarrito();
        $('#productoSelect').val(null).trigger('change');
    }

    function renderCarrito() {
        const tbody = document.getElementById('carritoBody');
        tbody.innerHTML = '';
        let total = 0;
        carrito.forEach((prod, index) => {
            const subtotal = prod.precio * prod.cantidad;
            total += subtotal;
            tbody.innerHTML += `<tr><td>${prod.nombre}</td><td>S/ ${prod.precio.toFixed(2)}</td><td><input type="number" min="1" value="${prod.cantidad}" onchange="updateCant(${index}, this.value)" class="form-control form-control-sm" style="width:70px"></td><td>S/ ${subtotal.toFixed(2)}</td><td><button type="button" class="btn btn-danger btn-sm" onclick="removeProd(${index})"><i class="bi bi-trash"></i></button></td></tr>`;
        });
        document.getElementById('totalVenta').textContent = total.toFixed(2);
        document.getElementById('carritoData').value = JSON.stringify(carrito);
    }

    function updateCant(index, val) {
        if (val < 1) val = 1;
        carrito[index].cantidad = parseInt(val);
        renderCarrito();
    }

    function removeProd(index) {
        carrito.splice(index, 1);
        renderCarrito();
    }
</script>
<?php include 'layouts/footer.php'; ?>