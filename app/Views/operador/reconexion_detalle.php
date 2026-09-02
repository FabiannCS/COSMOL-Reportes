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

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h2 class="h4 mb-0"><i class="bi bi-file-earmark-text"></i> Detalle de Reconexión</h2>
    <a href="/operador/trabajos" class="btn btn-outline-secondary align-self-start align-self-md-auto">
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
        No se pudieron cargar los detalles de esta reconexión. <a href="/operador/trabajos" class="alert-link">Regresar</a>.
    </div>
<?php else: ?>
    <div class="row mx-0">
        <!-- Columna de Datos (Solo Lectura) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Trabajo (API)</h5>
                </div>
                <div class="card-body bg-light">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">ID Reconexión</label>
                        <input type="text" class="form-control bg-e9ecef" value="#<?= htmlspecialchars($trabajo['id_reconexion'] ?? 'N/A') ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Socio</label>
                        <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['cod_socio'] ?? '') ?> - <?= htmlspecialchars(trim($trabajo['nombre_socio'] ?? '')) ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Ubicación (U-Z-R)</label>
                        <textarea class="form-control bg-e9ecef" rows="2" readonly disabled><?= htmlspecialchars($trabajo['ubicacion'] ?? '') ?> (Z: <?= htmlspecialchars($trabajo['zona'] ?? '') ?>, R: <?= htmlspecialchars($trabajo['ruta'] ?? '') ?>)</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Dirección del Predio</label>
                        <textarea class="form-control bg-e9ecef" rows="2" readonly disabled><?= htmlspecialchars($trabajo['direccion_predio'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Glosa / Observación del Registro</label>
                        <textarea class="form-control bg-e9ecef" rows="3" readonly disabled><?= htmlspecialchars($trabajo['glosa'] ?? 'Sin glosa extra') ?></textarea>
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
                    <h5 class="mb-0"><i class="bi bi-check2-square"></i> Concluir Trabajo</h5>
                </div>
                <div class="card-body">
                    <form action="/operador/concluir" method="POST" id="form-concluir">
                        <input type="hidden" name="id_trabajo" value="<?= htmlspecialchars($trabajo['id_reconexion'] ?? '') ?>">
                        
                        <div class="mb-4">
                            <label for="lecturacion" class="form-label fw-bold">Lecturación <span class="text-danger">*</span></label>
                            <input type="text" name="lecturacion" id="lecturacion" class="form-control" placeholder="Ingrese el valor de la lecturación..." required>
                        </div>

                        <div class="mb-4">
                            <label for="glosa" class="form-label fw-bold">Glosa / Observación <span class="text-danger">*</span></label>
                            <textarea name="glosa" id="glosa" class="form-control" rows="4" placeholder="Detalle qué trabajo se realizó o cualquier otra observación..." required></textarea>
                            <div class="form-text">Esta información actualizará el registro en el sistema central.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalConfirmar">
                                <i class="bi bi-send"></i> Enviar Conclusión
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
                                        ¿Estás seguro de que deseas enviar esta conclusión al servidor? <br>
                                        <strong>Esta acción registrará el trabajo permanentemente.</strong>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Sí, enviar conclusión</button>
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
