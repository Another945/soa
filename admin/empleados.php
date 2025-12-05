<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <div class="flex-grow-1 p-4"><h1>Gestión de Empleados</h1></div>

    <div id="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <input id="inputBusqueda" class="form-control me-2" type="search" placeholder="Buscar empleado..." style="width: 300px;">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarEmpleado">Agregar Empleado</button>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th style="width: 80px;">Foto</th>
                    <th>Nombre Completo</th>
                    <th style="width: 100px;">DNI</th> <th style="width: 120px;">Teléfono</th>
                    <th style="width: 250px;">Email</th> <th style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaEmpleadosBody"><tr><td colspan="6" class="text-center py-4">Cargando...</td></tr></tbody>
        </table>
    </div>
    <div id="paginacionContainer" class="mt-3"></div>
</div>

<div class="modal fade" id="modalAgregarEmpleado" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="controllers/empleados-dash.php" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Agregar Empleado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="nombre" required></div>
                <div class="mb-3"><label>Apellido</label><input type="text" class="form-control" name="apellido" required></div>
                <div class="mb-3"><label>DNI</label><input type="text" class="form-control" name="dni" required></div>
                <div class="mb-3"><label>Teléfono</label><input type="text" class="form-control" name="telefono"></div>
                <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email"></div>
                <div class="mb-3"><label>Rol</label><input type="text" class="form-control" name="rol" value="barbero" required></div>
                <div class="mb-3"><label>Foto</label><input class="form-control" type="file" name="imagen" accept="image/*"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditarEmpleado" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="controllers/empleados-dash.php" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title">Editar Empleado</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editar_id" name="editar_id">
                <div class="text-center mb-3"><img id="editar_imagen_preview" src="" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #ffc107;"></div>
                <div class="mb-3"><label>Nombre</label><input type="text" class="form-control" id="editar_nombre" name="editar_nombre" required></div>
                <div class="mb-3"><label>Apellido</label><input type="text" class="form-control" id="editar_apellido" name="editar_apellido" required></div>
                <div class="mb-3"><label>DNI</label><input type="text" class="form-control" id="editar_dni" name="editar_dni"></div>
                <div class="mb-3"><label>Teléfono</label><input type="text" class="form-control" id="editar_telefono" name="editar_telefono"></div>
                <div class="mb-3"><label>Email</label><input type="email" class="form-control" id="editar_email" name="editar_email"></div>
                <div class="mb-3"><label>Rol</label><input type="text" class="form-control" id="editar_rol" name="editar_rol" required></div>
                <div class="mb-3"><label>Cambiar Foto</label><input class="form-control" type="file" name="imagen" accept="image/*"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Cambios</button></div>
        </form>
    </div>
</div>

<script>
function cargarEmpleados(pagina = 1) {
    const busqueda = document.getElementById('inputBusqueda').value;
    fetch(`controllers/empleados-dash.php?action=ajax_list&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('tablaEmpleadosBody').innerHTML = d.tabla;
            document.getElementById('paginacionContainer').innerHTML = d.paginacion;
        });
}
document.addEventListener('DOMContentLoaded', () => {
    cargarEmpleados();
    document.getElementById('inputBusqueda').addEventListener('keyup', () => cargarEmpleados(1));
    
    var modal = document.getElementById('modalEditarEmpleado');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        modal.querySelector('#editar_id').value = btn.dataset.id;
        modal.querySelector('#editar_nombre').value = btn.dataset.nombre;
        modal.querySelector('#editar_apellido').value = btn.dataset.apellido;
        modal.querySelector('#editar_dni').value = btn.dataset.dni;
        modal.querySelector('#editar_telefono').value = btn.dataset.telefono;
        modal.querySelector('#editar_email').value = btn.dataset.email;
        modal.querySelector('#editar_rol').value = btn.dataset.rol;
        modal.querySelector('#editar_imagen_preview').src = btn.dataset.imagen;
    });
});
</script>
<?php include 'layouts/footer.php'; ?>