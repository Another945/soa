<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <div class="flex-grow-1 p-4"><h1>Gestión de Servicios</h1></div>

    <div id="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <input id="inputBusqueda" class="form-control me-2" type="search" placeholder="Buscar servicio..." style="width: 300px;">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarServicio">Agregar Servicio</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Imagen</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Descripción</th>
                    <th>Duración (min)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaServiciosBody"><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
        </table>
    </div>
    <div id="paginacionContainer" class="mt-3"></div>
</div>

<div class="modal fade" id="modalAgregarServicio" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/servicios-dash.php" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Agregar Servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label>Tipo de Servicio</label><input type="text" class="form-control" name="tipo" required></div><div class="mb-3"><label>Precio</label><input type="number" step="0.01" class="form-control" name="precio" required></div><div class="mb-3"><label>Descripción</label><textarea class="form-control" name="descripcion" rows="3" required></textarea></div><div class="mb-3"><label>Duración (min)</label><input type="number" class="form-control" name="duracion" required></div><div class="mb-3"><label>Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div>

<div class="modal fade" id="modalEditarServicio" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/servicios-dash.php" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Editar Servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editar_id" name="editar_id"><div class="mb-3"><label>Tipo</label><input type="text" class="form-control" id="editar_tipo" name="editar_tipo" required></div><div class="mb-3"><label>Precio</label><input type="number" step="0.01" class="form-control" id="editar_precio" name="editar_precio" required></div><div class="mb-3"><label>Descripción</label><textarea class="form-control" id="editar_descripcion" name="editar_descripcion" rows="3" required></textarea></div><div class="mb-3"><label>Duración (min)</label><input type="number" class="form-control" id="editar_duracion" name="editar_duracion" required></div><div class="mb-3"><label>Imagen Actual</label><br><img id="editar_imagen_preview" src="" style="width: 100px; border-radius: 5px;"></div><div class="mb-3"><label>Nueva Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Cambios</button></div></form></div></div>

<script>
function cargarServicios(pagina = 1) {
    const busqueda = document.getElementById('inputBusqueda').value;
    fetch(`controllers/servicios-dash.php?action=ajax_list&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('tablaServiciosBody').innerHTML = d.tabla;
            document.getElementById('paginacionContainer').innerHTML = d.paginacion;
        });
}
document.addEventListener('DOMContentLoaded', () => {
    cargarServicios();
    document.getElementById('inputBusqueda').addEventListener('keyup', () => cargarServicios(1));
    
    var modal = document.getElementById('modalEditarServicio');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        modal.querySelector('#editar_id').value = btn.dataset.id;
        modal.querySelector('#editar_tipo').value = btn.dataset.tipo;
        modal.querySelector('#editar_precio').value = btn.dataset.precio;
        modal.querySelector('#editar_descripcion').value = btn.dataset.descripcion;
        modal.querySelector('#editar_duracion').value = btn.dataset.duracion;
        modal.querySelector('#editar_imagen_preview').src = btn.dataset.imagen;
    });
});
</script>
<?php include 'layouts/footer.php'; ?>