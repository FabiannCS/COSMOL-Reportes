<?php
/**
 * Parcial: Ubicación GPS vs Mi Domicilio
 * Ruta: app/Views/administrador/trabajo_historial_detalle/ubicacion_gps.php
 *
 * Muestra el mapa embebido y rutas si el socio envió coordenadas numéricas GPS válidas,
 * o un aviso claro y destacado si la solicitud corresponde a su propio hogar ("Mi Domicilio").
 *
 * @var bool   $esGpsValido
 * @var string $coordsGpsLimpia
 * @var string $tipoTrabajo ('reconexion' | 'reclamo')
 */

$tipoTrabajo = isset($tipoTrabajo) ? $tipoTrabajo : 'reconexion';
$esReclamo   = ($tipoTrabajo === 'reclamo');
$alertaGps   = isset($alertaGps) ? $alertaGps : null;
$urlDomicilio= isset($urlDomicilio) ? $urlDomicilio : '';
?>

<!-- Sección de Ubicación: GPS vs Domicilio Propio -->
<?php if (!empty($esGpsValido) && !empty($coordsGpsLimpia)): ?>
    <?php 
        $urlVerMapa = "https://www.google.com/maps?q={$coordsGpsLimpia}";
        $urlRuta    = "https://www.google.com/maps/dir/?api=1&destination={$coordsGpsLimpia}";
        $urlIframe  = "https://maps.google.com/maps?q={$coordsGpsLimpia}&hl=es&z=15&output=embed";
    ?>
    <div class="p-3 rounded bg-light border" style="border-color: #cbd5e1 !important;">
        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
            <span class="text-secondary fw-semibold text-uppercase small d-inline-flex align-items-center gap-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                <i class="bi bi-geo-alt-fill text-danger"></i> Ubicación GPS Enviada
            </span>
            <span class="font-monospace small text-dark fw-bold"><?= htmlspecialchars($coordsGpsLimpia, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <?php if (!empty($alertaGps)): ?>
            <div class="alert alert-warning py-2 px-3 small mb-2.5 d-flex align-items-start gap-2 border-warning">
                <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0 mt-0.5"></i>
                <div>
                    <strong class="d-block text-dark" style="font-size: 0.8rem;">Aviso de Ubicación:</strong>
                    <span class="text-secondary"><?= htmlspecialchars($alertaGps, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex gap-2 flex-wrap mb-2.5">
            <a href="<?= htmlspecialchars($urlVerMapa, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm fw-semibold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5" style="min-height: 40px;">
                <i class="bi bi-box-arrow-up-right"></i> Abrir en Google Maps
            </a>
            <a href="<?= htmlspecialchars($urlRuta, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-sm fw-bold flex-fill d-inline-flex align-items-center justify-content-center gap-1.5" style="min-height: 40px;">
                <i class="bi bi-sign-turn-right-fill"></i> Iniciar Ruta GPS
            </a>
        </div>

        <div class="ratio ratio-21x9 border rounded overflow-hidden">
            <iframe src="<?= htmlspecialchars($urlIframe, ENT_QUOTES, 'UTF-8') ?>" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
<?php else: ?>
    <!-- Aviso: Solicitud en Domicilio Propio / Hogar del Socio -->
    <div class="p-3 rounded bg-light border d-flex flex-column gap-2" style="border-color: #cbd5e1 !important;">
        <div class="d-flex align-items-start gap-3">
            <div class="p-2 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                <i class="bi bi-house-check-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-bold text-dark fs-6 mb-1">
                    <?= $esReclamo ? 'Reclamo en Domicilio Registrado' : 'Reconexión en Domicilio Registrado' ?>
                </div>
                <div class="text-secondary small mb-0">
                    <?= $esReclamo 
                        ? 'El socio registró el reclamo para su domicilio registrado en la cooperativa. Guiarse por la Zona, Ruta y Ubicación Catastral.' 
                        : 'El socio solicitó la reconexión en su domicilio registrado en la cooperativa. Guiarse por la Zona, Ruta y Ubicación Catastral.' ?>
                </div>
            </div>
        </div>
        <?php if (!empty($urlDomicilio)): ?>
            <div class="pt-2 border-top" style="border-color: #e2e8f0;">
                <a href="<?= htmlspecialchars($urlDomicilio, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5" style="font-size: 0.8125rem;">
                    <i class="bi bi-geo-alt"></i> Buscar Dirección en Google Maps (Montero)
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
