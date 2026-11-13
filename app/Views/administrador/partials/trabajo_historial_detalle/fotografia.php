<?php
/**
 * Parcial: Fotografía Adjunta de Referencia
 * Ruta: app/Views/administrador/trabajo_historial_detalle/fotografia.php
 *
 * Muestra la imagen previa con enlace a pestaña nueva, zoom y fallback si no carga o no existe.
 *
 * @var string|null $foto
 * @var string|null $apiFotoBaseUrl
 * @var string      $fotoId
 * @var string      $subcarpeta ('reconexiones' | 'reclamos')
 * @var string      $etiqueta
 */

$foto           = isset($foto) && !empty($foto) ? trim((string)$foto) : null;
$subcarpeta     = isset($subcarpeta) && !empty($subcarpeta) ? trim($subcarpeta, '/') : 'reconexiones';
$fotoId         = isset($fotoId) ? preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$fotoId) : '0';
$etiqueta       = isset($etiqueta) && !empty($etiqueta) ? $etiqueta : 'Fotografía de referencia';
$apiFotoBaseUrl = isset($apiFotoBaseUrl) ? (string)$apiFotoBaseUrl : '';
?>

<div style="margin-top: 20px;">
    <span class="text-secondary fw-bold text-uppercase d-block mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.05em;">
        <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?>
    </span>
    <?php if (!empty($foto)): ?>
        <?php 
            $fotoRaw = $foto;
            if (strpos($fotoRaw, 'http://') === 0 || strpos($fotoRaw, 'https://') === 0) {
                $fotoUrl = $fotoRaw;
            } else {
                if (strpos($fotoRaw, '/uploads/') === false && strpos($fotoRaw, 'uploads/') === false) {
                    $fotoRaw = '/uploads/' . $subcarpeta . '/' . ltrim($fotoRaw, '/');
                }
                $base = !empty($apiFotoBaseUrl) ? rtrim($apiFotoBaseUrl, '/') : '';
                $fotoUrl = $base . '/' . ltrim($fotoRaw, '/');
            }
        ?>
        <div class="p-2 border rounded bg-white text-center">
            <a href="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Clic para ampliar imagen en nueva pestaña">
                <img src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" 
                     alt="<?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?>" 
                     class="img-fluid rounded border mb-1" 
                     style="max-height: 220px; width: auto; object-fit: contain;"
                     onerror="this.style.display='none'; document.getElementById('foto_error_<?= $fotoId ?>').style.display='block';">
            </a>
            <div id="foto_error_<?= $fotoId ?>" style="display: none;" class="alert alert-warning small py-1.5 my-1">
                <i class="bi bi-exclamation-circle text-warning me-1"></i> No se pudo cargar la vista previa de la imagen.
            </div>
            <div class="small text-secondary mt-1">
                <i class="bi bi-zoom-in me-1"></i>Clic en la foto para ampliar
            </div>
        </div>
    <?php else: ?>
        <div class="p-2.5 bg-light rounded border text-muted small text-center" style="border-color: #e2e8f0 !important;">
            <i class="bi bi-camera-slash me-1 text-secondary"></i> El socio no adjuntó fotografía en este trabajo.
        </div>
    <?php endif; ?>
</div>
