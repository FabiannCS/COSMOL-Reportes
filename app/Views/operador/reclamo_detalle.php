<?php
/**
 * @var string $title
 * @var string $especialidad
 * @var array|null $trabajo
 * @var string|null $error
 */
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Detalle de Reclamo</h2>
    <a href="/operador/trabajos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a la Lista
    </a>
</div>

<?php if ($error || $sesionError): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error ?: $sesionError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!$trabajo): ?>
    <div class="alert alert-warning">
        No se pudieron cargar los detalles de este reclamo. <a href="/operador/trabajos" class="alert-link">Regresar</a>.
    </div>
<?php else: ?>
    <div class="row">
        <!-- Columna de Datos (Solo Lectura) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Reclamo (API)</h5>
                </div>
                <div class="card-body bg-light">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">ID Reclamo</label>
                        <input type="text" class="form-control bg-e9ecef" value="#<?= htmlspecialchars($trabajo['id_reclamo'] ?? 'N/A') ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Descripción Técnica</label>
                        <input type="text" class="form-control bg-e9ecef fw-bold text-dark" value="<?= htmlspecialchars($trabajo['descripcion'] ?? 'N/A') ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Ubicación (U-Z-R)</label>
                        <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['ubicacion'] ?? '') ?> (Z: <?= htmlspecialchars($trabajo['zona'] ?? '') ?>, R: <?= htmlspecialchars($trabajo['ruta'] ?? '') ?>)" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Glosa / Mensaje del Cliente</label>
                        <textarea class="form-control bg-e9ecef text-dark" rows="3" readonly disabled><?= htmlspecialchars($trabajo['glosa'] ?? 'Sin mensaje del cliente') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold mb-1">Origen del Registro</label>
                            <div>
                                <?php if (isset($trabajo['usuario_registro']) && $trabajo['usuario_registro'] == 2): ?>
                                    <span class="badge bg-info text-dark w-100 p-2"><i class="bi bi-robot"></i> Bot de WhatsApp</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary w-100 p-2"><i class="bi bi-person"></i> Presencial / Admin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($trabajo['coordenadas_gps'])): ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Coordenadas GPS</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['coordenadas_gps']) ?>" readonly disabled>
                                <a href="https://maps.google.com/?q=<?= urlencode(str_replace(' ', '', $trabajo['coordenadas_gps'])) ?>" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bi bi-map"></i> Ver Mapa
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna de Conclusión (Editable) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-check2-square"></i> Concluir Reclamo</h5>
                </div>
                <div class="card-body">
                    <form action="/operador/concluir" method="POST" id="form-concluir">
                        <input type="hidden" name="id_trabajo" value="<?= htmlspecialchars($trabajo['id_reclamo'] ?? '') ?>">
                        
                        <div class="mb-4">
                            <label for="estado" class="form-label fw-bold">Estado Final <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="" disabled selected>Seleccione un estado...</option>
                                <option value="SOLUCIONADO">Solucionado / Reparado</option>
                                <option value="FALSA_ALARMA">Falsa Alarma / No Encontrado</option>
                                <option value="DERIVADO">Derivado a otro departamento</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="observacion_conclusion" class="form-label fw-bold">Informe Técnico del Operador <span class="text-danger">*</span></label>
                            <textarea name="observacion_conclusion" id="observacion_conclusion" class="form-control" rows="5" placeholder="Detalle la reparación realizada, estado de las tuberías o razón por la cual no se pudo solucionar..." required></textarea>
                            <div class="form-text">Esta información será registrada en el historial del reclamo.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                                <i class="bi bi-send"></i> Enviar Informe y Concluir
                            </button>
                        </div>

                        <!-- Modal Confirmación -->
                        <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalConfirmarLabel">Confirmar Conclusión</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Estás seguro de que deseas enviar este informe al servidor? <br>
                                        <strong>Esta acción dará por concluido este reclamo.</strong>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Sí, enviar informe</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Diferenciación visual clara para campos de solo lectura */
        .bg-e9ecef {
            background-color: #e9ecef !important;
            opacity: 1;
            cursor: not-allowed;
        }
    </style>
<?php endif; ?>
