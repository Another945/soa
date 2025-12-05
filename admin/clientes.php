<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <div class="flex-grow-1 p-4"><h1>Gestión de Clientes</h1></div>
    
    <div id="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <div class="mb-3"><input id="inputBusqueda" class="form-control" type="search" placeholder="Buscar cliente..." style="width: 300px;"></div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark"><tr><th>Nombre</th><th>Email</th><th>DNI</th><th>Teléfono</th><th>Fecha Alta</th><th>Acciones</th></tr></thead>
            <tbody id="tablaClientesBody"><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
        </table>
    </div>
    <div id="paginacionContainer" class="mt-3"></div>
</div>

<div class="modal fade" id="modalEditarUsuario" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/clientes-dash.php"><div class="modal-header"><h5 class="modal-title">Editar Cliente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editar_id" name="editar_id"><div class="mb-3"><label>Nombre</label><input type="text" class="form-control" id="editar_nombre" name="editar_nombre" required></div><div class="mb-3"><label>Apellido</label><input type="text" class="form-control" id="editar_apellido" name="editar_apellido" required></div><div class="mb-3"><label>Email</label><input type="email" class="form-control" id="editar_email" name="editar_email" required></div><div class="mb-3"><label>DNI</label><input type="text" class="form-control" id="editar_dni" name="editar_dni"></div><div class="mb-3"><label>Teléfono</label><input type="text" class="form-control" id="editar_telefono" name="editar_telefono"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div>

<script>
function cargarClientes(pagina = 1) {
    const busqueda = document.getElementById('inputBusqueda').value;
    fetch(`controllers/clientes-dash.php?action=ajax_list&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('tablaClientesBody').innerHTML = d.tabla;
            document.getElementById('paginacionContainer').innerHTML = d.paginacion;
        });
}
document.addEventListener('DOMContentLoaded', () => {
    cargarClientes();
    document.getElementById('inputBusqueda').addEventListener('keyup', () => cargarClientes(1));
    
    // Lógica Modal Editar
    var modal = document.getElementById('modalEditarUsuario');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        modal.querySelector('#editar_id').value = btn.dataset.id;
        modal.querySelector('#editar_nombre').value = btn.dataset.nombre;
        modal.querySelector('#editar_apellido').value = btn.dataset.apellido;
        modal.querySelector('#editar_email').value = btn.dataset.email;
        modal.querySelector('#editar_dni').value = btn.dataset.dni;
        modal.querySelector('#editar_telefono').value = btn.dataset.telefono;
    });
});
</script>
<?php include 'layouts/footer.php'; ?>