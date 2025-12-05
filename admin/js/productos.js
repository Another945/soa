document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modalEditarProducto');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('editar_id').value = button.getAttribute('data-id');
            document.getElementById('editar_nombre').value = button.getAttribute('data-nombre');
            document.getElementById('editar_descripcion').value = button.getAttribute('data-descripcion');
            document.getElementById('editar_precio').value = button.getAttribute('data-precio');
            document.getElementById('editar_stock').value = button.getAttribute('data-stock');
            // Aquí se asigna la categoría correctamente
            document.getElementById('editar_categoria').value = button.getAttribute('data-categoria');
        });
    }
});