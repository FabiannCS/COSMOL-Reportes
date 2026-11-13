<?php
/**
 * Parcial: Scripts de Validación y Modal de Reclamo
 * Ruta: app/Views/administrador/trabajo_historial_detalle/reclamo_scripts.php
 */
?>

<!-- Lógica de Validación y Modal Anti-Toques Accidentales para Reclamo -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btnAbrirModal = document.getElementById('btn-abrir-confirmacion');
        var formConcluir = document.getElementById('form-concluir');
        var selectEstado = document.getElementById('estado');
        var textareaObs = document.getElementById('observacion_conclusion');
        var modalEl = document.getElementById('modalConfirmarConclusionReclamo');
        var btnConfirmarEnvio = document.getElementById('btn-confirmar-envio');

        if (btnAbrirModal && formConcluir && modalEl) {
            btnAbrirModal.addEventListener('click', function(e) {
                e.preventDefault();

                var estado = selectEstado ? selectEstado.value : 'CONCLUIDO';
                var observacion = textareaObs ? textareaObs.value.trim() : '';

                // Validación: Observación / Informe Técnico es obligatoria
                if (observacion === '') {
                    alert('Por favor ingrese el informe técnico u observación antes de concluir el reclamo.');
                    if (textareaObs) {
                        textareaObs.focus();
                    }
                    return;
                }

                // Cargar datos en el resumen del modal
                var badgeEstado = document.getElementById('modal-resumen-estado');
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

                if (valorGlosa) {
                    valorGlosa.textContent = observacion;
                }

                // Abrir modal de Bootstrap
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    if (confirm('¿Está seguro de concluir este reclamo con estado ' + estado + '?')) {
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
