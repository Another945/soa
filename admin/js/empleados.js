document.addEventListener('DOMContentLoaded', function() {
    var modalEditar = document.getElementById('modalEditarEmpleado');
    
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function(event) {

            var button = event.relatedTarget; 

            document.getElementById('editar_id').value = button.getAttribute('data-id');
            document.getElementById('editar_nombre').value = button.getAttribute('data-nombre');
            document.getElementById('editar_apellido').value = button.getAttribute('data-apellido');
            document.getElementById('editar_dni').value = button.getAttribute('data-dni');
            document.getElementById('editar_telefono').value = button.getAttribute('data-telefono');
            document.getElementById('editar_email').value = button.getAttribute('data-email');
            document.getElementById('editar_rol').value = button.getAttribute('data-rol');
        });
    }

    const params = new URLSearchParams(window.location.search);
    if (params.has('agregado') || params.has('editado') || params.has('eliminado')) {
        let msg = '';
        if (params.has('agregado')) { msg = '¡Empleado agregado exitosamente!'; }
        if (params.has('editado')) { msg = '¡Empleado editado exitosamente!'; }
        if (params.has('eliminado')) { msg = '¡Empleado dado de baja exitosamente!'; }
        
        console.log(msg);
        setTimeout(() => {
            history.replaceState(null, '', window.location.pathname);
        }, 3000);
    }
});