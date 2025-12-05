<?php include 'layouts/header.php'; ?>
<div class="container mt-4">
    <div class="flex-grow-1 p-4"><h1>Gestión de Productos</h1></div>

    <div id="alert-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']['text']; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php unset($_SESSION['message']); endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <input id="inputBusqueda" class="form-control me-2" type="search" placeholder="Buscar producto..." style="width: 300px;">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">Agregar Producto</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark"><tr><th>Imagen</th><th>Nombre</th><th>Descripción</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr></thead>
            <tbody id="tablaProductosBody"><tr><td colspan="6" class="text-center">Cargando...</td></tr></tbody>
        </table>
    </div>
    <div id="paginacionContainer" class="mt-3"></div>
</div>

<div class="modal fade" id="modalAgregarProducto" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/productos-dash.php" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Agregar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="nombre" required></div><div class="mb-3"><label>Descripción</label><input type="text" class="form-control" name="descripcion"></div><div class="mb-3"><label>Precio</label><input type="number" step="0.01" class="form-control" name="precio" required></div><div class="mb-3"><label>Stock</label><input type="number" class="form-control" name="stock" required></div><div class="mb-3"><label>Categoría</label><select class="form-select" name="categoria" required><option value="">Seleccione</option><?php $res_cat = $conn->query("SELECT IdCategorias, NombreCategorias FROM categorias WHERE ActivoCategorias=1"); while($c = $res_cat->fetch_assoc()) { echo "<option value='{$c['IdCategorias']}'>{$c['NombreCategorias']}</option>"; } ?></select></div><div class="mb-3"><label>Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div></form></div></div>

<div class="modal fade" id="modalEditarProducto" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="controllers/productos-dash.php" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Editar Producto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="editar_id" name="editar_id"><div class="mb-3"><label>Nombre</label><input type="text" class="form-control" id="editar_nombre" name="editar_nombre" required></div><div class="mb-3"><label>Descripción</label><input type="text" class="form-control" id="editar_descripcion" name="editar_descripcion"></div><div class="mb-3"><label>Precio</label><input type="number" step="0.01" class="form-control" id="editar_precio" name="editar_precio" required></div><div class="mb-3"><label>Stock</label><input type="number" class="form-control" id="editar_stock" name="editar_stock" required></div><div class="mb-3"><label>Categoría</label><select class="form-select" id="editar_categoria" name="editar_categoria" required><option value="">Seleccione</option><?php $res_cat->data_seek(0); while($c = $res_cat->fetch_assoc()) { echo "<option value='{$c['IdCategorias']}'>{$c['NombreCategorias']}</option>"; } ?></select></div><div class="mb-3"><label>Imagen Actual</label><br><img id="editar_imagen_preview" src="" style="width: 80px; border-radius:5px;"></div><div class="mb-3"><label>Nueva Imagen</label><input class="form-control" type="file" name="imagen" accept="image/*"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar Cambios</button></div></form></div></div>

<script>
function cargarProductos(pagina = 1) {
    const busqueda = document.getElementById('inputBusqueda').value;
    fetch(`controllers/productos-dash.php?action=ajax_list&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('tablaProductosBody').innerHTML = d.tabla;
            document.getElementById('paginacionContainer').innerHTML = d.paginacion;
        });
}
document.addEventListener('DOMContentLoaded', () => {
    cargarProductos();
    document.getElementById('inputBusqueda').addEventListener('keyup', () => cargarProductos(1));
    
    var modal = document.getElementById('modalEditarProducto');
    modal.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        modal.querySelector('#editar_id').value = btn.dataset.id;
        modal.querySelector('#editar_nombre').value = btn.dataset.nombre;
        modal.querySelector('#editar_descripcion').value = btn.dataset.descripcion;
        modal.querySelector('#editar_precio').value = btn.dataset.precio;
        modal.querySelector('#editar_stock').value = btn.dataset.stock;
        modal.querySelector('#editar_categoria').value = btn.dataset.categoria;
        modal.querySelector('#editar_imagen_preview').src = btn.dataset.imagen;
    });
});
</script>
<?php include 'layouts/footer.php'; ?>