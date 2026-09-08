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
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Reclamo</h5>
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
                                         loading="lazy"
                                         decoding="async"
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

        <!-- Columna de Conclusión (Historial) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Reclamo Concluido</h5>
                </div>
                <div class="card-body bg-light text-center d-flex flex-column justify-content-center align-items-center p-4">
                    <i class="bi bi-shield-check display-1 text-success mb-3"></i>
                    <h4 class="text-success mb-3">Reclamo ya solucionado</h4>
                    <p class="text-muted mb-0">El informe técnico de este reclamo ya fue registrado en el sistema central y no puede ser modificado desde esta pantalla.</p>
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
