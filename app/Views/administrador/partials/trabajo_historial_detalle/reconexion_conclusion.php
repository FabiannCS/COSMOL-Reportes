<?php
/**
 * Parcial: Panel de Conclusión de Reconexión (Columna Derecha)
 * Ruta: app/Views/administrador/trabajo_historial_detalle/reconexion_conclusion.php
 *
 * Muestra el estado concluido o el formulario administrativo con validación y modal anti-toques.
 *
 * @var bool   $esFinalizadoEnApi
 * @var string $urlVolver
 * @var string $textoVolver
 * @var array  $trabajo
 * @var string $idReconexion
 * @var string $origen
 * @var string $estadoInterno
 * @var string $lecturaActual
 * @var string $glosaInterna
 * @var string $nombreSocio
 */

$esAdmin = function_exists('hasRole') ? hasRole('Administrador') : (isset($_SESSION['usuario']['nombre_rol']) && $_SESSION['usuario']['nombre_rol'] === 'Administrador');
$esPendiente = ($estadoCalculado === 'PENDIENTE' && empty($trabajo['estado_interno']));
$mostrarOperador = isset($mostrarOperador) ? $mostrarOperador : ($esAdmin && !$esPendiente && ($esEstadoNoConcluido || $esFinalizadoEnApi));

$operadorMostrar = $mostrarOperador ? (!empty($trabajo['operador_nombre']) 
    ? $trabajo['operador_nombre'] 
    : (!empty($trabajo['operador_reporta']) 
        ? $trabajo['operador_reporta'] 
        : (!empty($trabajo['nombre_operador']) ? $trabajo['nombre_operador'] : ''))) : '';
$fechaSeguimiento = !empty($trabajo['fecha_seguimiento']) ? $trabajo['fecha_seguimiento'] : '';
?>

<?php if ($esFinalizadoEnApi): ?>
    <div class="card bg-white rounded-2 shadow-sm text-center p-4" style="border: 1px solid #10b981 !important;">
        <div class="py-4">
            <i class="bi bi-check-circle-fill display-3 text-success mb-3 d-block"></i>
            <h4 class="fw-bold text-dark mb-1">Reconexión Concluida</h4>
            <p class="text-secondary small mb-3">Esta reconexión ya fue procesada y guardada exitosamente en el servidor central.</p>
            <?php if (!empty($mostrarOperador) && !empty($operadorMostrar)): ?>
                <div class="small text-secondary mb-3">
                    Operador que realizó el trabajo: <strong class="text-dark"><?= htmlspecialchars($operadorMostrar, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
            <div>
                <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i><?= htmlspecialchars($textoVolver, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid #94a3b8 !important;">
        <div class="card-header bg-white border-bottom py-2.5 px-3" style="border-color: #e2e8f0 !important;">
            <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span><i class="bi bi-check2-square text-primary me-1"></i>Registrar Conclusión de Trabajo</span>
                <?php if (!empty($trabajo['estado_interno'])): ?>
                    <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($trabajo['estado_interno'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </h5>
        </div>
        
        <div class="card-body p-3">
            <?php if (hasPermission('trabajos.concluir')): ?>
                <?php if (!empty($trabajo['glosa_interna'])): ?>
                    <div class="alert alert-warning mb-3 p-3 rounded-2 small border" style="border-color: #fde68a !important; background-color: #fffbeb;">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2 pb-2 border-bottom" style="border-color: #fde68a !important;">
                            <strong class="text-dark d-flex align-items-center gap-1.5">
                                <i class="bi bi-clock-history text-warning-emphasis"></i>
                                <span>Informe Previo (<?= htmlspecialchars(isset($trabajo['estado_interno']) ? $trabajo['estado_interno'] : 'NO CONCLUIDO', ENT_QUOTES, 'UTF-8') ?>)</span>
                            </strong>
                            <?php if (!empty($mostrarOperador) && !empty($operadorMostrar)): ?>
                                <span class="badge bg-white text-dark border font-monospace px-2 py-1" style="border-color: #cbd5e1 !important;">
                                    Operador: <?= htmlspecialchars($operadorMostrar, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-dark mb-2"><?= nl2br(htmlspecialchars($trabajo['glosa_interna'], ENT_QUOTES, 'UTF-8')) ?></div>
                        <?php if ($esAdmin && !empty($fechaSeguimiento)): ?>
                            <div class="text-secondary font-monospace" style="font-size: 0.72rem;">
                                <i class="bi bi-calendar3 me-1"></i>Fecha de reporte: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($fechaSeguimiento)), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form action="/administrador/trabajos/concluir" method="POST" id="form-concluir">
                    <?= csrfField(); ?>
                    <input type="hidden" name="tipo" value="reconexion">
                    <input type="hidden" name="id_trabajo" value="<?= htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="origen" value="<?= htmlspecialchars($origen, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Campo 1: Estado del Trabajo -->
                    <div class="mb-3">
                        <label for="estado" class="form-label fw-bold text-dark small mb-1">
                            Estado del Trabajo <span class="text-danger">*</span>
                        </label>
                        <select name="estado" id="estado" class="form-select fw-semibold" required onchange="toggleReconexionFields()" style="min-height: 44px; border-color: #cbd5e1;">
                            <option value="CONCLUIDO" <?= $estadoInterno === 'CONCLUIDO' ? 'selected' : '' ?>>Concluido / Reconectado</option>
                            <option value="NO CONCLUIDO" <?= $estadoInterno === 'NO CONCLUIDO' ? 'selected' : '' ?>>No Concluido / Pendiente de atención</option>
                            <option value="NO PROCEDENTE" <?= $estadoInterno === 'NO PROCEDENTE' ? 'selected' : '' ?>>No Procedente / Falsa alarma</option>
                        </select>
                    </div>

                    <!-- Campo 2: Lecturación (Requerida solo en CONCLUIDO) -->
                    <div class="mb-3" id="div-lecturacion">
                        <label for="lecturacion" class="form-label fw-bold text-dark small mb-1">
                            Lecturación Física del Medidor <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   name="lecturacion" 
                                   id="lecturacion" 
                                   class="form-control font-monospace fw-bold fs-6" 
                                   value="<?= htmlspecialchars($lecturaActual, ENT_QUOTES, 'UTF-8') ?>" 
                                   style="min-height: 44px; border-color: #cbd5e1;" 
                                   autocomplete="off" 
                                   required>
                            <span class="input-group-text bg-light text-secondary border-start-0 font-monospace small" style="border-color: #cbd5e1;">
                                m³
                            </span>
                        </div>
                        <div class="form-text small" style="font-size: 0.78rem;">
                            Valor numérico actual registrado en el medidor
                        </div>
                    </div>

                    <!-- Campo 3: Observación / Glosa Técnica -->
                    <div class="mb-4">
                        <label for="glosa" class="form-label fw-bold text-dark small mb-1">
                            Observación / Glosa Técnica <span class="text-danger">*</span>
                        </label>
                        <textarea name="glosa" 
                                  id="glosa" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="Detalle qué trabajo se realizó o cualquier otra observación técnica..." 
                                  style="border-color: #cbd5e1; font-size: 0.9rem;" 
                                  required><?= htmlspecialchars($glosaInterna, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="form-text small" style="font-size: 0.78rem;">
                            Si selecciona <strong>CONCLUIDO</strong>, esta conclusión actualizará permanentemente el estado en el servidor central de COSMOL.
                        </div>
                    </div>

                    <!-- Botón Principal: Concluir Trabajo (Abre Modal de Confirmación) -->
                    <div class="d-grid">
                        <button type="button" 
                                id="btn-abrir-confirmacion" 
                                class="btn btn-primary fw-bold py-2.5 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                style="min-height: 48px; font-size: 1rem;">
                            <i class="bi bi-send fs-5"></i>
                            <span>Enviar Conclusión</span>
                        </button>
                    </div>

                    <!-- Modal de Confirmación Anti-Toques Accidentales -->
                    <div class="modal fade" id="modalConfirmarConclusion" tabindex="-1" aria-labelledby="modalConfirmarLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-3">
                                <div class="modal-header border-bottom py-2.5 px-3 bg-light">
                                    <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center gap-2" id="modalConfirmarLabel">
                                        <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                        ¿Confirmar Conclusión de Trabajo?
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                
                                <div class="modal-body p-3">
                                    <p class="text-secondary small mb-3">
                                        Verifique los datos ingresados antes de enviar la conclusión al sistema central:
                                    </p>

                                    <!-- Resumen de Datos Clave -->
                                    <div class="p-2.5 rounded bg-light border mb-3 small" style="border-color: #e2e8f0 !important;">
                                        <div class="d-flex justify-content-between mb-1.5">
                                            <span class="text-secondary">Orden:</span>
                                            <strong class="text-dark font-monospace">#<?= htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1.5">
                                            <span class="text-secondary">Socio:</span>
                                            <strong class="text-dark text-truncate text-end ms-2" style="max-width: 220px;"><?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                                            <span class="text-secondary">Estado a Guardar:</span>
                                            <span id="modal-resumen-estado" class="badge bg-primary text-white font-monospace">CONCLUIDO</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1.5" id="modal-resumen-lectura-fila">
                                            <span class="text-secondary">Lecturación:</span>
                                            <strong id="modal-resumen-lectura" class="text-dark font-monospace fs-6">—</strong>
                                        </div>
                                        <div class="border-top pt-1.5 mt-1.5">
                                            <span class="text-secondary d-block mb-1">Observación:</span>
                                            <div id="modal-resumen-glosa" class="text-dark fw-medium fst-italic text-break" style="font-size: 0.8125rem;">—</div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning border-0 p-2 small mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                                        <div style="font-size: 0.78rem;">
                                            Esta acción registrará la conclusión de la reconexión en el servidor central de forma definitiva.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="modal-footer border-top py-2 px-3 bg-light d-flex justify-content-between gap-2">
                                    <button type="button" class="btn btn-outline-secondary fw-semibold px-3 flex-fill" data-bs-dismiss="modal" style="min-height: 44px;">
                                        Revisar / Cancelar
                                    </button>
                                    <button type="button" id="btn-confirmar-envio" class="btn btn-success fw-bold px-4 flex-fill d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="min-height: 44px;">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Sí, Concluir Trabajo</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-shield-slash fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted fw-semibold mb-3">No cuenta con los permisos necesarios para concluir este trabajo.</p>
                    <button type="button" class="btn btn-secondary btn-lg disabled" disabled title="Permiso denegado">
                        <i class="bi bi-lock-fill me-1"></i> Conclusión Bloqueada
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
