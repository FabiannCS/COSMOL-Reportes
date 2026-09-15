<?php
/**
 * Vista: Detalle de Reconexión Concluida (Operador)
 * @var string $title
 * @var string $especialidad
 * @var array|null $trabajo
 * @var string|null $apiFotoBaseUrl
 * @var string|null $error
 * @var string|null $urlVolver
 * @var string|null $origen
 */
$error = isset($error) && $error ? $error : (isset($_SESSION['error']) ? $_SESSION['error'] : null);
unset($_SESSION['error']);

$urlVolver = isset($urlVolver) && !empty($urlVolver) ? $urlVolver : (isset($_GET['volver']) ? $_GET['volver'] : '/operador/historial');
$origen    = isset($origen) ? $origen : (isset($_GET['origen']) ? $_GET['origen'] : '');
?>

<div class="container-fluid p-0">
    <!-- Encabezado con navegación -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-tools text-primary me-2"></i>Reconexión #<?= htmlspecialchars(isset($trabajo['id_reconexion']) ? $trabajo['id_reconexion'] : '—', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-muted mb-0">Ficha de Reconexión — Solo lectura</p>
        </div>
        <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!$trabajo): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>No se pudieron cargar los detalles de esta reconexión. <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="alert-link">Regresar</a>.
        </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Columna de Información -->
        <div class="col-lg-7 d-flex flex-column gap-4">
            <!-- Información de la Reconexión -->
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de la Reconexión</h5>
                </div>
                <div class="card-body bg-light">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">ID Reconexión</label>
                        <input type="text" class="form-control bg-e9ecef" value="#<?= htmlspecialchars($trabajo['id_reconexion'] ?? 'N/A') ?>" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Socio</label>
                        <input type="text" class="form-control bg-e9ecef" value="Cod. <?= htmlspecialchars(!empty($trabajo['cod_socio']) ? $trabajo['cod_socio'] . ' - ' : '') ?><?= htmlspecialchars(trim($trabajo['nombre_socio'] ?? 'No especificado')) ?>" readonly disabled>
                    </div>

                    <!-- SECCIÓN: DATOS DE SISTEMA -->
                    <div class="alert alert-secondary border-0 p-3 mb-4 rounded-3 bg-opacity-50">
                        <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2"><i class="bi bi-house-door me-1"></i> 1. Dirección del Socio Registrada en Sistema</h6>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold mb-1">Ubicación (U-Z-R)</label>
                            <input type="text" class="form-control bg-e9ecef" value="<?= htmlspecialchars($trabajo['ubicacion'] ?? '') ?> (Zona: <?= htmlspecialchars($trabajo['zona'] ?? '') ?>, Ruta: <?= htmlspecialchars($trabajo['ruta'] ?? '') ?>)" readonly disabled>
                        </div>

                        <?php if (!empty($trabajo['direccion_predio']) || !empty($trabajo['direccion'])): ?>
                            <div class="mb-0">
                                <label class="form-label text-muted small fw-bold mb-1">Dirección fija del Socio</label>
                                <textarea class="form-control bg-e9ecef" rows="2" readonly disabled><?= htmlspecialchars($trabajo['direccion_predio'] ?? $trabajo['direccion'] ?? '') ?></textarea>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- SECCIÓN: REPORTE EN CAMPO -->
                    <h6 class="fw-bold mb-3 border-bottom border-primary border-opacity-25 pb-2 text-primary">2.- Reporte de Trabajo</h6>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Glosa / Observación del Registro</label>
                        <textarea class="form-control bg-e9ecef text-dark" rows="3" readonly disabled><?= htmlspecialchars($trabajo['glosa'] ?? 'Sin glosa extra') ?></textarea>
                    </div>

                    <!-- GPS SECTION CON MAPA -->
                    <?php if (!empty($trabajo['coordenadas_gps'])): ?>
                        <?php 
                            $coordsLimpia = preg_replace('/\s+/', '', $trabajo['coordenadas_gps']); 
                            $urlVerMapa = "https://www.google.com/maps?q={$coordsLimpia}";
                            $urlRuta    = "https://www.google.com/maps/dir/?api=1&destination={$coordsLimpia}";
                            // URL para embeber el mapa directamente en la página
                            $urlIframe  = "https://maps.google.com/maps?q={$coordsLimpia}&hl=es&z=15&output=embed";
                        ?>
                        <div class="mb-4 p-3 border border-danger border-opacity-25 rounded-3 bg-white shadow-sm">
                            <label class="form-label text-danger small fw-bold mb-2"><i class="bi bi-geo-alt-fill"></i> Ubicación Física (GPS)</label>
                            
                            <div class="alert alert-warning border-warning p-2 mb-3 small text-dark d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-5 me-2 text-warning"></i> 
                                <div>
                                    <strong>¡Atención!</strong> Esta es la ubicación de la reconexión. <span class="text-danger fw-bold">Puede ser distinta a su casa.</span> ¡Guíese por esta dirección!
                                </div>
                            </div>
                            
                            <!-- Mapa Embebido de Google Maps -->
                            <div class="ratio ratio-16x9 mb-3 border rounded overflow-hidden shadow-sm">
                                <iframe src="<?= htmlspecialchars($urlIframe) ?>" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>

                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light text-muted small" style="font-size: 15px;">Coordenadas:</span>
                                <input type="text" class="form-control bg-e9ecef text-center fw-bold" style="font-size: 15px;" value="<?=htmlspecialchars($trabajo['coordenadas_gps']) ?>" readonly disabled>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars($urlVerMapa) ?>" target="_blank" class="btn btn-outline-secondary flex-fill">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en Maps
                                </a>
                                <a href="<?= htmlspecialchars($urlRuta) ?>" target="_blank" class="btn btn-success flex-fill shadow-sm fw-bold">
                                    <i class="bi bi-sign-turn-right-fill me-1"></i> Iniciar Ruta GPS
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Fotografía Adjunta -->
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">Fotografía del Predio / Medidor</label>
                        <?php if (!empty($trabajo['foto'])): ?>
                            <?php 
                                $fotoRaw = trim($trabajo['foto']);
                                if (strpos($fotoRaw, 'http://') === 0 || strpos($fotoRaw, 'https://') === 0) {
                                    $fotoUrl = $fotoRaw;
                                } else {
                                    if (strpos($fotoRaw, '/uploads/') === false && strpos($fotoRaw, 'uploads/') === false) {
                                        $fotoRaw = '/uploads/reconexiones/' . ltrim($fotoRaw, '/');
                                    }
                                    $base = !empty($apiFotoBaseUrl) ? rtrim($apiFotoBaseUrl, '/') : '';
                                    $fotoUrl = $base . '/' . ltrim($fotoRaw, '/');
                                }
                                $fotoId = htmlspecialchars($trabajo['id_reconexion'] ?? '0');
                            ?>
                            <div class="border rounded p-2 bg-white text-center shadow-sm">
                                <a href="<?= htmlspecialchars($fotoUrl) ?>" target="_blank" title="Clic para ampliar en pestaña nueva">
                                    <img src="<?= htmlspecialchars($fotoUrl) ?>" 
                                         alt="Foto de la reconexión" 
                                         loading="lazy"
                                         decoding="async"
                                         class="img-fluid rounded border mb-2" 
                                         style="max-height: 240px; width: auto; object-fit: contain;"
                                         onerror="this.style.display='none'; document.getElementById('foto_error_rec_<?= $fotoId ?>').style.display='block';">
                                </a>
                                <div id="foto_error_rec_<?= $fotoId ?>" style="display: none;" class="alert alert-warning small py-2 mb-2">
                                    <i class="bi bi-exclamation-circle text-warning me-1"></i> No se pudo cargar la vista previa de la imagen.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-3 bg-white rounded border text-muted small text-center">
                                <i class="bi bi-camera-slash display-6 d-block mb-1 text-secondary opacity-50"></i>
                                El socio no adjuntó fotografía en esta reconexión.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna de Conclusión (Historial) -->
        <div class="col-lg-5">
            <?php 
                $estCalc = function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($trabajo) : ($trabajo['estado'] ?? 'CONCLUIDO');
            ?>
            <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                <div class="card shadow-sm h-100 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Trabajo No Concluido</h5>
                    </div>
                    <div class="card-body bg-light text-center d-flex flex-column justify-content-center align-items-center p-4">
                        <i class="bi bi-x-octagon-fill display-1 text-danger mb-3"></i>
                        <h4 class="text-danger mb-2">Registrado como No Concluido</h4>
                        <p class="text-muted mb-0">Este trabajo fue finalizado por el operador sin concluir la reconexión. El informe técnico quedó registrado en el sistema.</p>
                    </div>
                </div>
            <?php elseif ($estCalc === 'NO PROCEDENTE'): ?>
                <div class="card shadow-sm h-100 border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-slash-circle me-2"></i>Reconexión No Procedente</h5>
                    </div>
                    <div class="card-body bg-light text-center d-flex flex-column justify-content-center align-items-center p-4">
                        <i class="bi bi-slash-circle display-1 text-secondary mb-3"></i>
                        <h4 class="text-secondary mb-2">No Procedente</h4>
                        <p class="text-muted mb-0">Esta reconexión fue marcada como no procedente.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Reconexión Concluida</h5>
                    </div>
                    <div class="card-body bg-light text-center d-flex flex-column justify-content-center align-items-center p-4">
                        <i class="bi bi-shield-check display-1 text-success mb-3"></i>
                        <h4 class="text-success mb-2">Reconexión registrada</h4>
                        <p class="text-muted mb-0">Los detalles y la conclusión de este trabajo ya fueron enviados al sistema central y no pueden ser modificados desde esta pantalla.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    /* Diferenciación visual clara para campos de solo lectura */
    .bg-e9ecef {
        background-color: #e9ecef !important;
        opacity: 1;
        cursor: not-allowed;
    }
</style>

