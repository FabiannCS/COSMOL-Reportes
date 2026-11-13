<?php
/**
 * Componente: Scripts para Gestión de Usuarios
 * Archivo: app/Views/administrador/partials/usuarios_scripts.php
 * 
 * Lógica JS para poblar modales y alternar dinámicamente la especialidad según el rol.
 */
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Alternar visibilidad y requerimiento del campo especialidad según el rol seleccionado
    function alternarEspecialidad(selectRol, grupoEsp, selectEsp) {
        if (!selectRol || !grupoEsp) return;
        var selectedOption = selectRol.options[selectRol.selectedIndex];
        var nombreRol = selectedOption ? selectedOption.getAttribute('data-nombre-rol') : '';
        if (nombreRol === 'Operador') {
            grupoEsp.style.display = 'block';
            if (selectEsp) selectEsp.required = true;
        } else {
            grupoEsp.style.display = 'none';
            if (selectEsp) {
                selectEsp.required = false;
                selectEsp.value = '';
            }
        }
    }

    // Modal Crear Usuario
    var selectRolCrear = document.getElementById('crear_id_rol');
    var grupoEspCrear = document.getElementById('grupo_crear_especialidad');
    var selectEspCrear = document.getElementById('crear_id_especialidad');
    if (selectRolCrear) {
        selectRolCrear.addEventListener('change', function () {
            alternarEspecialidad(selectRolCrear, grupoEspCrear, selectEspCrear);
        });
        alternarEspecialidad(selectRolCrear, grupoEspCrear, selectEspCrear);
    }

    // Modal Editar Usuario
    var modalEditar = document.getElementById('modalEditarUsuario');
    var selectRolEdit = document.getElementById('edit_usuario_id_rol');
    var grupoEspEdit = document.getElementById('grupo_edit_especialidad');
    var selectEspEdit = document.getElementById('edit_usuario_id_especialidad');

    if (selectRolEdit) {
        selectRolEdit.addEventListener('change', function () {
            alternarEspecialidad(selectRolEdit, grupoEspEdit, selectEspEdit);
        });
    }

    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;

            var idUsuario      = button.getAttribute('data-id');
            var username       = button.getAttribute('data-username');
            var idRol          = button.getAttribute('data-id-rol');
            var idEspecialidad = button.getAttribute('data-id-especialidad');

            var inputId = document.getElementById('edit_usuario_id');
            var inputUser = document.getElementById('edit_usuario_username');
            var selectRol = document.getElementById('edit_usuario_id_rol');
            var selectEsp = document.getElementById('edit_usuario_id_especialidad');
            var inputPass = document.getElementById('edit_usuario_password');

            if (inputId) inputId.value = idUsuario || '';
            if (inputUser) inputUser.value = username || '';
            if (selectRol) selectRol.value = idRol || '';
            if (selectEsp) selectEsp.value = idEspecialidad || '';
            if (inputPass) inputPass.value = '';

            alternarEspecialidad(selectRolEdit, grupoEspEdit, selectEspEdit);
        });
    }
});
</script>
