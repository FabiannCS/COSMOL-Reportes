<?php
/**
 * @var string $title
 * @var string $especialidad
 * @var array|null $trabajo
 * @var string|null $error
 */
$error = isset($error) ? $error : null;
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Detalle de Reclamo</h2>
    <a href="/operador/trabajos" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
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
                        <label class="form-label text-muted small fw-bold mb-1">Solicitante</label>
                        <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars(!empty($trabajo['cod_socio']) ? $trabajo['cod_socio'] . ' - ' : '') ?><?= htmlspecialchars(trim($trabajo['nombre_socio'] ?? 'No especificado')) ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Ubicación (U-Z-R)</label>
                        <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['ubicacion'] ?? '') ?> (Z: <?= htmlspecialchars($trabajo['zona'] ?? '') ?>, R: <?= htmlspecialchars($trabajo['ruta'] ?? '') ?>)" readonly disabled>
                    </div>

                    <?php if (!empty($trabajo['direccion_predio'])): ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Dirección del Predio</label>
                            <textarea class="form-control bg-e9ecef" rows="2" readonly disabled><?= htmlspecialchars($trabajo['direccion_predio']) ?></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Glosa / Mensaje del Cliente</label>
                        <textarea class="form-control bg-e9ecef text-dark" rows="3" readonly disabled><?= htmlspecialchars($trabajo['glosa'] ?? 'Sin mensaje del cliente') ?></textarea>
                    </div>

                    <!-- Fotografía Adjunta -->
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Fotografía del Reclamo</label>
                        <?php if (!empty($trabajo['foto'])): ?>
                            <?php 
                                $fotoRaw = trim($trabajo['foto']);
                                if (strpos($fotoRaw, 'http://') === 0 || strpos($fotoRaw, 'https://') === 0) {
                                    $fotoUrl = $fotoRaw;
                                } else {
                                    if (strpos($fotoRaw, '/uploads/') === false && strpos($fotoRaw, 'uploads/') === false) {
                                        $fotoRaw = '/uploads/reclamos/' . ltrim($fotoRaw, '/');
                                    }
                                    $base = !empty($apiFotoBaseUrl) ? rtrim($apiFotoBaseUrl, '/') : '';
                                    $fotoUrl = $base . '/' . ltrim($fotoRaw, '/');
                                }
                                $fotoId = htmlspecialchars($trabajo['id_reclamo'] ?? '0');
                            ?>
                            <div class="border rounded p-2 bg-white text-center shadow-sm">
                                <a href="<?= htmlspecialchars($fotoUrl) ?>" target="_blank" title="Clic para ampliar en pestaña nueva">
                                    <img src="<?= htmlspecialchars($fotoUrl) ?>" 
                                         alt="Foto del reclamo" 
                                         class="img-fluid rounded border mb-2" 
                                         style="max-height: 240px; width: auto; object-fit: contain;"
                                         onerror="this.style.display='none'; document.getElementById('foto_error_<?= $fotoId ?>').style.display='block';">
                                </a>
                                <div id="foto_error_<?= $fotoId ?>" style="display: none;" class="alert alert-warning small py-2 mb-2">
                                    <i class="bi bi-exclamation-circle text-warning me-1"></i> No se pudo cargar la vista previa de la imagen.
                                </div>
                                <div>
                                    <a href="<?= htmlspecialchars($fotoUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Ver Imagen Completa
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-white rounded border text-muted small text-center">
                                <i class="bi bi-camera-slash display-6 d-block mb-1 text-secondary opacity-50"></i>
                                El socio no adjuntó fotografía en este reclamo.
                            </div>
                        <?php endif; ?>
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
                        <?php 
                            $coordsLimpia = preg_replace('/\s+/', '', $trabajo['coordenadas_gps']); 
                            $urlVerMapa = "https://www.google.com/maps?q={$coordsLimpia}";
                            $urlRuta    = "https://www.google.com/maps/dir/?api=1&destination={$coordsLimpia}";
                        ?>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Coordenadas GPS y Navegación</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white"><i class="bi bi-geo-alt-fill text-danger"></i></span>
                                <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['coordenadas_gps']) ?>" readonly disabled>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars($urlVerMapa) ?>" target="_blank" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="bi bi-map me-1"></i> Ver Ubicación
                                </a>
                                <a href="<?= htmlspecialchars($urlRuta) ?>" target="_blank" class="btn btn-sm btn-outline-success flex-fill">
                                    <i class="bi bi-sign-turn-right-fill me-1"></i> Trazar Ruta (Cómo llegar)
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
