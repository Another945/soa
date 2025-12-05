document.addEventListener('DOMContentLoaded', function() {
    var modalEditar = document.getElementById('modalEditarCategoria');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; 

            // Se asegura de que los IDs de los campos de edición se pueblen correctamente
            document.getElementById('editar_id').value = button.getAttribute('data-id');
            document.getElementById('editar_nombre').value = button.getAttribute('data-nombre');
        });
    }
});