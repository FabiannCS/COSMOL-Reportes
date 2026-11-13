<?php
/**
 * Vista: Detalle de Reconexión Concluida (Módulo Operador)
 * Ficha técnica consolidada en modo solo lectura con diseño limpio, armónico y tipografía consistente.
 *
 * @var string      $title
 * @var string      $especialidad
 * @var array|null  $trabajo
 * @var string|null $apiFotoBaseUrl
 * @var string|null $error
 * @var string|null $urlVolver
 * @var string|null $origen
 */

$error = isset($error) && $error ? $error : (isset($_SESSION['error']) ? $_SESSION['error'] : null);
unset($_SESSION['error']);

$urlVolver = isset($urlVolver) && !empty($urlVolver) ? $urlVolver : (isset($_GET['volver']) ? $_GET['volver'] : '/operador/historial');
$origen    = isset($origen) ? $origen : (isset($_GET['origen']) ? $_GET['origen'] : '');

$idReconexion = isset($trabajo['id_reconexion']) ? (string)$trabajo['id_reconexion'] : '—';
$codSocio     = isset($trabajo['cod_socio']) ? (string)$trabajo['cod_socio'] : '';
$nombreSocio  = isset($trabajo['nombre_socio']) && !empty($trabajo['nombre_socio']) ? trim((string)$trabajo['nombre_socio']) : 'No especificado';
$direccion    = isset($trabajo['direccion_predio']) && !empty($trabajo['direccion_predio']) ? (string)$trabajo['direccion_predio'] : (isset($trabajo['direccion']) ? (string)$trabajo['direccion'] : '');
$zona         = isset($trabajo['zona']) ? trim((string)$trabajo['zona']) : '';
$ruta         = isset($trabajo['ruta']) ? trim((string)$trabajo['ruta']) : '';
$ubicacion    = isset($trabajo['ubicacion']) ? trim((string)$trabajo['ubicacion']) : '';

// Fila Clave: Zona y Ruta (Ej. "Zona 2 Ruta 24")
$textoZona = '';
if (!empty($zona)) {
    $textoZona = (stripos($zona, 'zona') === false ? 'Zona ' : '') . $zona;
}
$textoRuta = '';
if (!empty($ruta)) {
    $textoRuta = (stripos($ruta, 'ruta') === false ? 'Ruta ' : '') . $ruta;
}
$zonaRuta = trim($textoZona . ' ' . $textoRuta);
if (empty($zonaRuta) && !empty($ubicacion)) {
    $zonaRuta = $ubicacion;
}
if (empty($zonaRuta)) {
    $zonaRuta = 'Zona / Ruta no asignada';
}

$medidor       = isset($trabajo['medidor']) && !empty($trabajo['medidor']) ? (string)$trabajo['medidor'] : (isset($trabajo['num_medidor']) ? (string)$trabajo['num_medidor'] : '');
$glosaRegistro = isset($trabajo['glosa']) ? trim((string)$trabajo['glosa']) : '';
$foto          = isset($trabajo['foto']) && !empty($trabajo['foto']) ? trim((string)$trabajo['foto']) : null;

// Tipo de Reconexión (API: id_tipo_reconexion)
$idTipoReconexion = isset($trabajo['id_tipo_reconexion']) ? (int)$trabajo['id_tipo_reconexion'] : 1;
$tipoReconexionTexto = 'Corte normal';
$tipoReconexionDesc  = 'Sin retiro de medidor';

switch ($idTipoReconexion) {
    case 1:
        $tipoReconexionTexto = 'Corte normal';
        $tipoReconexionDesc  = 'Sin retiro de medidor';
        break;
    case 2:
        $tipoReconexionTexto = 'Con medidor';
        $tipoReconexionDesc  = 'Retiro de medidor';
        break;
    case 3:
        $tipoReconexionTexto = 'Con material';
        $tipoReconexionDesc  = 'Falta de accesorios';
        break;
    case 4:
        $tipoReconexionTexto = 'Otros';
        $tipoReconexionDesc  = 'Otro motivo';
        break;
}

// Validación de tipo de ubicación: GPS vs Mi Domicilio
$rawGps = isset($trabajo['coordenadas_gps']) ? trim((string)$trabajo['coordenadas_gps']) : '';
$esGpsValido     = false;
$coordsGpsLimpia = '';
$esMiDomicilio   = false;

if (!empty($rawGps)) {
    if (strtoupper($rawGps) === 'DOMICILIO_REGISTRADO' || stripos($rawGps, 'DOMICILIO') !== false) {
        $esMiDomicilio = true;
    } elseif (preg_match('/^-?\d{1,3}\.\d+[\s]*,[\s]*-?\d{1,3}\.\d+$/', $rawGps)) {
        $esGpsValido     = true;
        $coordsGpsLimpia = preg_replace('/\s+/', '', $rawGps);
    } else {
        $esMiDomicilio = true;
    }
} else {
    $esMiDomicilio = true;
}

// Formateo de fecha de solicitud
$fechaStr = isset($trabajo['fecha_registro']) ? $trabajo['fecha_registro'] : '';
$fechaFormateada = '—';
if ($fechaStr) {
    $fechaObj = date_create($fechaStr);
    $fechaFormateada = $fechaObj ? date_format($fechaObj, 'd/m/Y H:i') : htmlspecialchars($fechaStr, ENT_QUOTES, 'UTF-8');
}

// Estados operativos
$estadoRaw = strtoupper(trim(isset($trabajo['estado']) ? (string)$trabajo['estado'] : ''));
$estadoCalculado = isset($trabajo['estado_calculado']) 
    ? $trabajo['estado_calculado'] 
    : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($trabajo) : ($estadoRaw ?: 'CONCLUIDO'));

$tieneEstadoPendienteLocal = !empty($trabajo['estado_interno']);
$esEstadoNoConcluido       = ($estadoCalculado === 'NO CONCLUIDO' || $estadoCalculado === 'NO PROCEDENTE' || $tieneEstadoPendienteLocal);

$badgeClass = 'bg-success-subtle text-success border-success';
if ($estadoCalculado === 'NO CONCLUIDO') {
    $badgeClass = 'bg-danger-subtle text-danger border-danger';
} elseif ($estadoCalculado === 'NO PROCEDENTE' || $estadoCalculado === 'ANULADO' || $estadoCalculado === 'CANCELADO') {
    $badgeClass = 'bg-secondary-subtle text-secondary border-secondary';
}

$lecturaActual = isset($trabajo['lectura_reconexion']) ? (string)$trabajo['lectura_reconexion'] : '';
$glosaInterna  = isset($trabajo['glosa_interna']) ? (string)$trabajo['glosa_interna'] : '';

$fechaConclusion = !empty($trabajo['fecha_reconexion']) 
    ? $trabajo['fecha_reconexion'] 
    : (!empty($trabajo['fecha_actualizacion']) 
        ? $trabajo['fecha_actualizacion'] 
        : (!empty($trabajo['fecha_seguimiento']) ? $trabajo['fecha_seguimiento'] : ''));

$fechaConclusionFormateada = '';
if (!empty($fechaConclusion)) {
    $fcObj = date_create($fechaConclusion);
    $fechaConclusionFormateada = $fcObj ? date_format($fcObj, 'd/m/Y H:i') : htmlspecialchars($fechaConclusion, ENT_QUOTES, 'UTF-8');
}

$glosaMostrar = !empty($glosaInterna) ? $glosaInterna : $glosaRegistro;
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4 border-bottom pb-3" style="border-color: #e2e8f0 !important;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="h3 fw-bold mb-0 text-dark">
                    Reconexión <span class="font-monospace text-primary">#<?= htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <span class="badge border font-monospace fw-bold <?= $badgeClass ?>" style="font-size: 0.75rem; letter-spacing: 0.03em;">
                    <?= htmlspecialchars($estadoCalculado, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <p class="text-secondary small mb-0">
                <span>Llegada de solicitud: <strong class="text-dark font-monospace"><?= $fechaFormateada ?></strong></span>
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="min-height: 38px;">
            <i class="bi bi-arrow-left"></i>
            <span>Volver a la lista</span>
        </a>
    </div>

    <!-- Alertas de Error -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border mb-3" role="alert" style="border-color: #fecaca !important;">
            <div class="small fw-semibold"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!$trabajo): ?>
        <div class="card shadow-sm border text-center p-4 p-md-5 bg-white rounded-2" style="border-color: #cbd5e1 !important;">
            <h2 class="h5 fw-bold text-dark mb-2">No se pudo cargar la orden</h2>
            <p class="text-secondary small mb-3">No fue posible obtener los datos de esta reconexión en el servidor.</p>
            <div>
                <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary px-3 py-2 fw-semibold">
                    Regresar a la lista
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3 g-md-4">
            
            <!-- Columna Izquierda: Información de la Orden de Trabajo -->
            <div class="col-12 col-lg-7 d-flex flex-column gap-3">
                <div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid #94a3b8 !important;">
                    
                    <!-- Encabezado de la Ficha -->
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3" style="border-color: #e2e8f0 !important;">
                        <span class="text-secondary fw-semibold font-monospace" style="font-size: 0.85rem;">
                            Orden #<?= htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="card-body p-3">
                        
                        <!-- 1. Identificación del Socio Titular -->
                        <div class="pb-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Socio Solicitante
                            </div>
                            <div class="text-secondary fs-6 text-break">
                                <?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($codSocio)): ?>
                                    <span class="text-muted ms-1 font-monospace">(Cód: <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

                        <!-- 2. Tipo de Reconexión -->
                        <div class="py-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Tipo de Reconexión
                            </div>
                            <div class="text-secondary fs-6">
                                <?= htmlspecialchars($tipoReconexionTexto, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($tipoReconexionDesc)): ?>
                                    <span class="text-muted ms-1">(<?= htmlspecialchars($tipoReconexionDesc, ENT_QUOTES, 'UTF-8') ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

                        <!-- 3. Ubicación Proporcionada (Condicional: GPS vs Domicilio Registrado) -->
                        <div class="py-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Ubicación
                            </div>
                            <div class="text-secondary fs-6 text-break">
                                <?php if ($esGpsValido): ?>
                                    Ubicación referencial enviada por el socio (ver mapa y coordenadas abajo)
                                <?php else: ?>
                                    <div>
                                        <?= !empty($direccion) ? htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') : '<span class="text-muted fst-italic">Dirección no especificada</span>' ?>
                                        <span class="text-muted ms-1">(Domicilio registrado)</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 mt-2 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem; letter-spacing: 0.02em;">
                                            <i class="bi bi-geo-alt-fill text-primary me-1"></i><?= htmlspecialchars($zonaRuta, ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <?php if (!empty($ubicacion)): ?>
                                            <span class="text-secondary font-monospace small" style="font-size: 0.78rem;">
                                                U-Z-R: <strong class="text-dark"><?= htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8') ?></strong>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

                        <!-- 4. Descripción del Socio / Glosa -->
                        <div class="py-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Descripción del Socio
                            </div>
                            <div class="text-secondary fs-6 text-break">
                                <?= !empty($glosaRegistro) ? htmlspecialchars($glosaRegistro, ENT_QUOTES, 'UTF-8') : 'Reconexión de servicio de agua potable' ?>
                            </div>
                        </div>

                        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

                        <!-- Sección de Ubicación: GPS vs Domicilio Propio -->
                        <?php if ($esGpsValido): ?>
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
                            <div class="p-3 rounded bg-light border d-flex align-items-start gap-3" style="border-color: #cbd5e1 !important;">
                                <div class="p-2 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-house-check-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6 mb-1">
                                        Reconexión en Domicilio Propio
                                    </div>
                                    <div class="text-secondary small mb-0">
                                        El socio solicitó la reconexión en su domicilio registrado. No requiere navegación GPS externa.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Fotografía Adjunta -->
                        <div style="margin-top: 20px;">
                            <span class="text-secondary fw-bold text-uppercase d-block mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                Fotografía de referencia
                            </span>
                            <?php if (!empty($foto)): ?>
                                <?php 
                                    $fotoRaw = trim($foto);
                                    if (strpos($fotoRaw, 'http://') === 0 || strpos($fotoRaw, 'https://') === 0) {
                                        $fotoUrl = $fotoRaw;
                                    } else {
                                        if (strpos($fotoRaw, '/uploads/') === false && strpos($fotoRaw, 'uploads/') === false) {
                                            $fotoRaw = '/uploads/reconexiones/' . ltrim($fotoRaw, '/');
                                        }
                                        $base = !empty($apiFotoBaseUrl) ? rtrim($apiFotoBaseUrl, '/') : '';
                                        $fotoUrl = $base . '/' . ltrim($fotoRaw, '/');
                                    }
                                    $fotoId = htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="p-2 border rounded bg-white text-center">
                                    <a href="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Clic para ampliar imagen en nueva pestaña">
                                        <img src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="Foto del predio" 
                                             class="img-fluid rounded border mb-1" 
                                             style="max-height: 220px; width: auto; object-fit: contain;"
                                             onerror="this.style.display='none'; document.getElementById('foto_error_rec_<?= $fotoId ?>').style.display='block';">
                                    </a>
                                    <div id="foto_error_rec_<?= $fotoId ?>" style="display: none;" class="alert alert-warning small py-1.5 my-1">
                                        <i class="bi bi-exclamation-circle text-warning me-1"></i> No se pudo cargar la vista previa de la imagen.
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        <i class="bi bi-zoom-in me-1"></i>Clic en la foto para ampliar
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="p-2.5 bg-light rounded border text-muted small text-center" style="border-color: #e2e8f0 !important;">
                                    El socio no adjuntó fotografía en esta reconexión.
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Estado de Conclusión / Informe -->
            <div class="col-12 col-lg-5">
                <div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid <?= ($estadoCalculado === 'NO CONCLUIDO') ? '#fca5a5' : (($estadoCalculado === 'NO PROCEDENTE') ? '#cbd5e1' : '#86efac') ?> !important;">
                    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0 !important;">
                        <h5 class="mb-0 fw-bold text-dark fs-6">
                            Estado del Trabajo
                        </h5>
                        <span class="badge border font-monospace fw-bold <?= $badgeClass ?>" style="font-size: 0.75rem;">
                            <?= htmlspecialchars($estadoCalculado, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="card-body p-3">
                        <div class="text-center py-2 border-bottom mb-3" style="border-color: #f1f5f9 !important;">
                            <h4 class="h5 fw-bold text-dark mb-1">
                                <?php if ($estadoCalculado === 'NO CONCLUIDO'): ?>
                                    Trabajo No Concluido
                                <?php elseif ($estadoCalculado === 'NO PROCEDENTE'): ?>
                                    Reconexión No Procedente
                                <?php else: ?>
                                    Reconexión Concluida
                                <?php endif; ?>
                            </h4>
                            <p class="text-secondary small mb-0">
                                <?php if ($estadoCalculado === 'NO CONCLUIDO'): ?>
                                    El trabajo fue finalizado por el operador sin concluir la reconexión. El informe técnico quedó registrado en el sistema.
                                <?php elseif ($estadoCalculado === 'NO PROCEDENTE'): ?>
                                    Esta reconexión fue marcada como no procedente o falsa alarma.
                                <?php else: ?>
                                    Los detalles y la conclusión de este trabajo ya fueron enviados al sistema central y no pueden ser modificados.
                                <?php endif; ?>
                            </p>
                        </div>

                        <!-- Datos del Cierre del Trabajo -->
                        <?php if (!empty($fechaConclusionFormateada)): ?>
                            <div class="mb-2.5 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <div class="text-secondary small fw-semibold mb-0.5">Fecha de conclusión:</div>
                                <div class="text-dark font-monospace small"><?= $fechaConclusionFormateada ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($lecturaActual !== '' && $lecturaActual !== null): ?>
                            <div class="mb-2.5 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <div class="text-secondary small fw-semibold mb-0.5">Lectura de reconexión:</div>
                                <div class="text-dark font-monospace fs-6 fw-bold"><?= htmlspecialchars((string)$lecturaActual, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($glosaMostrar)): ?>
                            <div class="mb-3">
                                <div class="text-secondary small fw-semibold mb-1">Informe / Glosa registrada:</div>
                                <div class="p-2.5 rounded bg-light border text-dark small" style="border-color: #e2e8f0 !important; white-space: pre-wrap; line-height: 1.45;"><?= htmlspecialchars($glosaMostrar, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="pt-2">
                            <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary w-100 py-2 fw-semibold shadow-sm">
                                <i class="bi bi-arrow-left me-1"></i>Volver a la lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>
