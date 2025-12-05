document.addEventListener('DOMContentLoaded', function() {
    var modalEditar = document.getElementById('modalEditarServicio');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; 

            var idServicio = button.getAttribute('data-id');
            var tipoServicio = button.getAttribute('data-tipo');
            var precioServicio = button.getAttribute('data-precio');
            var descripcionServicio = button.getAttribute('data-descripcion');
            var duracionMinutos = button.getAttribute('data-duracion');
            
            document.getElementById('editar_id').value = idServicio;
            document.getElementById('editar_tipo').value = tipoServicio;
            document.getElementById('editar_precio').value = precioServicio;
            document.getElementById('editar_descripcion').value = descripcionServicio;
            document.getElementById('editar_duracion').value = duracionMinutos;
        });
    }
});