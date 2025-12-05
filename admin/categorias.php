<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <div class="flex-grow-1 p-4"><h1>Gestión de Categorías</h1></div>

    <div id="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <input id="inputBusqueda" class="form-control me-2" type="search" placeholder="Buscar categoría..." style="width: 300px;">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarCategoria">Agregar Categoría</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre de Categoría</th>
                    <th class="text-center" style="width: 150px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaCategoriasBody"><tr><td colspan="2" class="text-center">Cargando...</td></tr></tbody>
        </table>
    </div>
    <div id="paginacionContainer" class="mt-3"></div>
</div>

<div class="modal fade" id="modalAgregarCategoria" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/categorias-dash.php"><div class="modal-header"><h5 class="modal-title">Agregar Categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="nombre" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div>

<div class="modal fade" id="modalEditarCategoria" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/categorias-dash.php"><div class="modal-header"><h5 class="modal-title">Editar Categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editar_id" name="editar_id"><div class="mb-3"><label>Nombre</label><input type="text" class="form-control" id="editar_nombre" name="editar_nombre" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Cambios</button></div></form></div></div>

<script>
function cargarCategorias(pagina = 1) {
    const busqueda = document.getElementById('inputBusqueda').value;
    fetch(`controllers/categorias-dash.php?action=ajax_list&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('tablaCategoriasBody').innerHTML = d.tabla;
            document.getElementById('paginacionContainer').innerHTML = d.paginacion;
        });
}
document.addEventListener('DOMContentLoaded', () => {
    cargarCategorias();
    document.getElementById('inputBusqueda').addEventListener('keyup', () => cargarCategorias(1));
    
    var modal = document.getElementById('modalEditarCategoria');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        modal.querySelector('#editar_id').value = btn.dataset.id;
        modal.querySelector('#editar_nombre').value = btn.dataset.nombre;
    });
});
</script>
<?php include 'layouts/footer.php'; ?>