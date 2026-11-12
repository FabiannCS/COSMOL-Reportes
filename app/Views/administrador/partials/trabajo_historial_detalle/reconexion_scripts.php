<?php
/**
 * Parcial: Scripts de Validación y Modal de Reconexión
 * Ruta: app/Views/administrador/trabajo_historial_detalle/reconexion_scripts.php
 */
?>

<!-- Lógica de Validación y Modal Anti-Toques Accidentales -->
<script>
    function toggleReconexionFields() {
        var estado = document.getElementById('estado');
        var lecturacion = document.getElementById('lecturacion');
        var divLecturacion = document.getElementById('div-lecturacion');
        
        if (!estado || !lecturacion || !divLecturacion) return;

        if (estado.value === 'CONCLUIDO') {
            divLecturacion.style.display = 'block';
            lecturacion.required = true;
        } else {
            divLecturacion.style.display = 'none';
            lecturacion.required = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleReconexionFields();

        var btnAbrirModal = document.getElementById('btn-abrir-confirmacion');
        var formConcluir = document.getElementById('form-concluir');
        var selectEstado = document.getElementById('estado');
        var inputLecturacion = document.getElementById('lecturacion');
        var textareaGlosa = document.getElementById('glosa');
        var modalEl = document.getElementById('modalConfirmarConclusion');
        var btnConfirmarEnvio = document.getElementById('btn-confirmar-envio');

        if (btnAbrirModal && formConcluir && modalEl) {
            btnAbrirModal.addEventListener('click', function(e) {
                e.preventDefault();

                var estado = selectEstado ? selectEstado.value : 'CONCLUIDO';
                var lecturacion = inputLecturacion ? inputLecturacion.value.trim() : '';
                var glosa = textareaGlosa ? textareaGlosa.value.trim() : '';

                // Validación 1: Lecturación requerida si es CONCLUIDO
                if (estado === 'CONCLUIDO' && lecturacion === '') {
                    alert('Por favor ingrese la lectura física del medidor antes de concluir.');
                    if (inputLecturacion) {
                        inputLecturacion.focus();
                    }
                    return;
                }

                // Validación 2: Glosa requerida siempre
                if (glosa === '') {
                    alert('Por favor ingrese una observación o glosa técnica del trabajo.');
                    if (textareaGlosa) {
                        textareaGlosa.focus();
                    }
                    return;
                }

                // Cargar datos en el resumen del modal
                var badgeEstado = document.getElementById('modal-resumen-estado');
                var filaLectura = document.getElementById('modal-resumen-lectura-fila');
                var valorLectura = document.getElementById('modal-resumen-lectura');
                var valorGlosa = document.getElementById('modal-resumen-glosa');

                if (badgeEstado) {
                    badgeEstado.textContent = estado;
                    if (estado === 'CONCLUIDO') {
                        badgeEstado.className = 'badge bg-success text-white font-monospace px-2 py-1';
                    } else if (estado === 'NO CONCLUIDO') {
                        badgeEstado.className = 'badge bg-danger text-white font-monospace px-2 py-1';
                    } else {
                        badgeEstado.className = 'badge bg-secondary text-white font-monospace px-2 py-1';
                    }
                }

                if (filaLectura && valorLectura) {
                    if (estado === 'CONCLUIDO') {
                        filaLectura.style.display = 'flex';
                        valorLectura.textContent = lecturacion + ' m³';
                    } else {
                        filaLectura.style.display = 'none';
                    }
                }

                if (valorGlosa) {
                    valorGlosa.textContent = glosa;
                }

                // Abrir modal de Bootstrap
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    if (confirm('¿Está seguro de concluir este trabajo con estado ' + estado + '?')) {
                        formConcluir.submit();
                    }
                }
            });
        }

        if (btnConfirmarEnvio && formConcluir) {
            btnConfirmarEnvio.addEventListener('click', function() {
                btnConfirmarEnvio.disabled = true;
                btnConfirmarEnvio.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando conclusión...';
                formConcluir.submit();
            });
        }
    });
</script>
