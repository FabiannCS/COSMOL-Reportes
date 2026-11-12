<?php
/**
 * Vista: Detalle y Conclusión de Reclamo (Módulo Operador)
 * Diseñada para máxima claridad, velocidad de captura en terreno y prevención de toques accidentales.
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

$urlVolver = isset($urlVolver) && !empty($urlVolver) ? $urlVolver : (isset($_GET['volver']) ? $_GET['volver'] : '/operador/trabajos');
$origen    = isset($origen) ? $origen : (isset($_GET['origen']) ? $_GET['origen'] : '');

$idReclamo   = isset($trabajo['id_reclamo']) ? (string)$trabajo['id_reclamo'] : '—';
$codSocio    = isset($trabajo['cod_socio']) ? (string)$trabajo['cod_socio'] : '';
$nombreSocio = isset($trabajo['nombre_socio']) && !empty($trabajo['nombre_socio']) ? trim((string)$trabajo['nombre_socio']) : 'No especificado';
$direccion   = isset($trabajo['direccion_predio']) && !empty($trabajo['direccion_predio']) ? (string)$trabajo['direccion_predio'] : (isset($trabajo['direccion']) ? (string)$trabajo['direccion'] : '');
$zona        = isset($trabajo['zona']) ? trim((string)$trabajo['zona']) : '';
$ruta        = isset($trabajo['ruta']) ? trim((string)$trabajo['ruta']) : '';
$ubicacion   = isset($trabajo['ubicacion']) ? trim((string)$trabajo['ubicacion']) : '';

// Tipo de Reclamo
$idTipoReclamo = isset($trabajo['id_tipo_reclamo']) ? (int)$trabajo['id_tipo_reclamo'] : 0;
$tipoReclamoTexto = 'Reclamo Técnico';
$badgeTipoReclamoClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';

if ($idTipoReclamo === 2) {
    $tipoReclamoTexto = 'Agua Potable';
    $badgeTipoReclamoClass = 'bg-info-subtle text-info border border-info-subtle';
} elseif ($idTipoReclamo === 3) {
    $tipoReclamoTexto = 'Alcantarillado';
    $badgeTipoReclamoClass = 'bg-success-subtle text-success border border-success-subtle';
}

$descripcionTecnica = isset($trabajo['descripcion']) ? trim((string)$trabajo['descripcion']) : '';
$mensajeCliente     = isset($trabajo['glosa']) ? trim((string)$trabajo['glosa']) : '';

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

$foto = isset($trabajo['foto']) && !empty($trabajo['foto']) ? trim((string)$trabajo['foto']) : null;

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

// Formateo de fecha
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
    : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($trabajo) : 'PENDIENTE');

$tieneEstadoPendienteLocal = !empty($trabajo['estado_interno']);
$esEstadoNoConcluido       = ($estadoCalculado === 'NO CONCLUIDO' || $estadoCalculado === 'NO PROCEDENTE' || $tieneEstadoPendienteLocal);
$esFinalizadoEnApi         = ($estadoRaw === 'CONCLUIDO' || $estadoRaw === 'CONCLUIDA') && !$esEstadoNoConcluido;

$badgeClass = 'bg-warning-subtle text-dark border-warning';
if ($estadoCalculado === 'CONCLUIDO' || $estadoCalculado === 'CONCLUIDA') {
    $badgeClass = 'bg-success-subtle text-success border-success';
} elseif ($estadoCalculado === 'NO CONCLUIDO') {
    $badgeClass = 'bg-danger-subtle text-danger border-danger';
} elseif ($estadoCalculado === 'NO PROCEDENTE' || $estadoCalculado === 'ANULADO' || $estadoCalculado === 'CANCELADO') {
    $badgeClass = 'bg-secondary-subtle text-secondary border-secondary';
}

$glosaInterna  = isset($trabajo['glosa_interna']) ? (string)$trabajo['glosa_interna'] : '';
$estadoInterno = isset($trabajo['estado_interno']) ? (string)$trabajo['estado_interno'] : 'CONCLUIDO';
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4 border-bottom pb-3" style="border-color: #e2e8f0 !important;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="h3 fw-bold mb-0 text-dark">
                    <i class="bi bi-exclamation-circle-fill text-info me-1"></i>Reclamo <span class="font-monospace text-primary">#<?= htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <span class="badge border font-monospace fw-bold <?= $badgeClass ?>" style="font-size: 0.75rem; letter-spacing: 0.03em;">
                    <?= htmlspecialchars($estadoCalculado, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
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
            <h2 class="h5 fw-bold text-dark mb-2">No se pudo cargar la orden de reclamo</h2>
            <p class="text-secondary small mb-3">No fue posible obtener los datos de este reclamo en el servidor central.</p>
            <div>
                <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary px-3 py-2 fw-semibold">
                    Regresar a la lista
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3 g-md-4">
            
            <!-- Columna Izquierda: Información del Reclamo -->
            <div class="col-12 col-lg-7 d-flex flex-column gap-3">
                <div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid #94a3b8 !important;">
                    
                    <!-- Encabezado de la Ficha -->
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3" style="border-color: #e2e8f0 !important;">
                        <span class="text-secondary fw-semibold font-monospace" style="font-size: 0.85rem;">
                            Orden #<?= htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="text-secondary small font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" title="Fecha de reporte">
                            <?= $fechaFormateada ?>
                        </span>
                    </div>

                    <div class="card-body p-3">
                        
                        <!-- 1. Identificación del Socio Solicitante -->
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

                        <!-- 2. Tipo de Reclamo y Descripción Técnica -->
                        <div class="py-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Tipo de Reclamo
                            </div>
                            <div class="text-secondary fs-6">
                                <?= htmlspecialchars($tipoReclamoTexto, ENT_QUOTES, 'UTF-8') ?>
                                <?php if (!empty($descripcionTecnica)): ?>
                                    <span class="text-muted ms-1">(<?= htmlspecialchars($descripcionTecnica, ENT_QUOTES, 'UTF-8') ?>)</span>
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

                        <!-- 4. Mensaje del Cliente / Glosa -->
                        <div class="py-2">
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                                Mensaje del Cliente
                            </div>
                            <div class="text-secondary fs-6 text-break">
                                <?= !empty($mensajeCliente) ? htmlspecialchars($mensajeCliente, ENT_QUOTES, 'UTF-8') : 'Sin mensaje adicional del cliente' ?>
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
                                        Reclamo en Domicilio Propio
                                    </div>
                                    <div class="text-secondary small mb-0">
                                        El socio registró el reclamo en su domicilio registrado. No requiere navegación GPS externa.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Fotografía Adjunta -->
                        <div style="margin-top: 20px;">
                            <span class="text-secondary fw-bold text-uppercase d-block mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                Fotografía del reclamo
                            </span>
                            <?php if (!empty($foto)): ?>
                                <?php 
                                    $fotoRaw = trim($foto);
                                    if (strpos($fotoRaw, 'http://') === 0 || strpos($fotoRaw, 'https://') === 0) {
                                        $fotoUrl = $fotoRaw;
                                    } else {
                                        if (strpos($fotoRaw, '/uploads/') === false && strpos($fotoRaw, 'uploads/') === false) {
                                            $fotoRaw = '/uploads/reclamos/' . ltrim($fotoRaw, '/');
                                        }
                                        $base = !empty($apiFotoBaseUrl) ? rtrim($apiFotoBaseUrl, '/') : '';
                                        $fotoUrl = $base . '/' . ltrim($fotoRaw, '/');
                                    }
                                    $fotoId = htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8');
                                ?>
                                <div class="p-2 border rounded bg-white text-center">
                                    <a href="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Clic para ampliar imagen en nueva pestaña">
                                        <img src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="Foto del reclamo" 
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
                                    <i class="bi bi-camera-slash me-1 text-secondary"></i> El socio no adjuntó fotografía en este reclamo.
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Formulario de Conclusión de Reclamo -->
            <div class="col-12 col-lg-5">
                <?php if ($esFinalizadoEnApi): ?>
                    <div class="card bg-white rounded-2 shadow-sm text-center p-4" style="border: 1px solid #10b981 !important;">
                        <div class="py-4">
                            <i class="bi bi-check-circle-fill display-3 text-success mb-3 d-block"></i>
                            <h4 class="fw-bold text-dark mb-1">Reclamo Concluido</h4>
                            <p class="text-secondary small mb-3">Este reclamo ya fue solucionado y guardado en el servidor central.</p>
                            <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold shadow-sm">
                                <i class="bi bi-arrow-left me-1"></i>Volver a la lista
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid #94a3b8 !important;">
                        <div class="card-header bg-white border-bottom py-2.5 px-3" style="border-color: #e2e8f0 !important;">
                            <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span><i class="bi bi-check2-square text-primary me-1"></i>Registrar Conclusión de Reclamo</span>
                                <?php if (!empty($trabajo['estado_interno'])): ?>
                                    <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($trabajo['estado_interno'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        
                        <div class="card-body p-3">
                            <?php if (!empty($trabajo['glosa_interna'])): ?>
                                <div class="alert alert-warning mb-3 p-2.5 rounded-2 small border" style="border-color: #fde68a !important;">
                                    <strong class="d-block mb-1 text-dark">
                                        <i class="bi bi-clock-history me-1"></i>Informe Previo (<?= htmlspecialchars(isset($trabajo['estado_interno']) ? $trabajo['estado_interno'] : 'NO CONCLUIDO', ENT_QUOTES, 'UTF-8') ?>):
                                    </strong>
                                    <div class="text-dark"><?= nl2br(htmlspecialchars($trabajo['glosa_interna'], ENT_QUOTES, 'UTF-8')) ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="/operador/concluir" method="POST" id="form-concluir">
                                <?= csrfField(); ?>
                                <input type="hidden" name="id_trabajo" value="<?= htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="origen" value="<?= htmlspecialchars($origen, ENT_QUOTES, 'UTF-8') ?>">

                                <!-- Campo 1: Estado del Reclamo -->
                                <div class="mb-3">
                                    <label for="estado" class="form-label fw-bold text-dark small mb-1">
                                        Estado del Trabajo <span class="text-danger">*</span>
                                    </label>
                                    <select name="estado" id="estado" class="form-select fw-semibold" required style="min-height: 44px; border-color: #cbd5e1;">
                                        <option value="CONCLUIDO" <?= $estadoInterno === 'CONCLUIDO' ? 'selected' : '' ?>>CONCLUIDO (Solucionado)</option>
                                        <option value="NO CONCLUIDO" <?= $estadoInterno === 'NO CONCLUIDO' ? 'selected' : '' ?>>NO CONCLUIDO (Pendiente de atención)</option>
                                        <option value="NO PROCEDENTE" <?= $estadoInterno === 'NO PROCEDENTE' ? 'selected' : '' ?>>NO PROCEDENTE (Falsa alarma / Descartado)</option>
                                    </select>
                                </div>

                                <!-- Campo 2: Informe Técnico / Observación de Conclusión -->
                                <div class="mb-4">
                                    <label for="observacion_conclusion" class="form-label fw-bold text-dark small mb-1">
                                        Informe Técnico / Observación de Campo <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="observacion_conclusion" 
                                              id="observacion_conclusion" 
                                              class="form-control" 
                                              rows="4" 
                                              placeholder="Detalle qué trabajo se realizó o cualquier otra observación técnica..." 
                                              style="border-color: #cbd5e1; font-size: 0.9rem;" 
                                              required><?= htmlspecialchars($glosaInterna, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    <div class="form-text small" style="font-size: 0.78rem;">
                                        Esta conclusión se registrará como constancia y actualizará el servidor central de COSMOL.
                                    </div>
                                </div>

                                <!-- Botón Principal: Concluir Trabajo (Abre Modal de Confirmación) -->
                                <div class="d-grid">
                                    <button type="button" 
                                            id="btn-abrir-confirmacion" 
                                            class="btn btn-primary fw-bold py-2.5 d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" 
                                            style="min-height: 48px; font-size: 1rem;">
                                        <i class="bi bi-check2-circle fs-5"></i>
                                        <span>Concluir Trabajo</span>
                                    </button>
                                </div>

                                <!-- Modal de Confirmación Anti-Toques Accidentales -->
                                <div class="modal fade" id="modalConfirmarConclusionReclamo" tabindex="-1" aria-labelledby="modalConfirmarReclamoLabel" aria-hidden="true" data-bs-backdrop="static">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-3">
                                            <div class="modal-header border-bottom py-2.5 px-3 bg-light">
                                                <h5 class="modal-title fw-bold text-dark fs-6 d-flex align-items-center gap-2" id="modalConfirmarReclamoLabel">
                                                    <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                                    ¿Confirmar Conclusión de Reclamo?
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
                                                        <span class="text-secondary">Reclamo:</span>
                                                        <strong class="text-dark font-monospace">#<?= htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8') ?></strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1.5">
                                                        <span class="text-secondary">Tipo:</span>
                                                        <strong class="text-dark"><?= htmlspecialchars($tipoReclamoTexto, ENT_QUOTES, 'UTF-8') ?></strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1.5">
                                                        <span class="text-secondary">Socio:</span>
                                                        <strong class="text-dark text-truncate text-end ms-2" style="max-width: 220px;"><?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?></strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                                        <span class="text-secondary">Estado a Guardar:</span>
                                                        <span id="modal-resumen-estado" class="badge bg-primary text-white font-monospace">CONCLUIDO</span>
                                                    </div>
                                                    <div class="border-top pt-1.5 mt-1.5">
                                                        <span class="text-secondary d-block mb-1">Informe / Observación:</span>
                                                        <div id="modal-resumen-glosa" class="text-dark fw-medium fst-italic text-break" style="font-size: 0.8125rem;">—</div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-warning border-0 p-2 small mb-0 d-flex align-items-center gap-2">
                                                    <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                                                    <div style="font-size: 0.78rem;">
                                                        Esta acción registrará la conclusión del reclamo en el servidor central de forma definitiva.
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
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Lógica de Validación y Modal Anti-Toques Accidentales -->
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
                    alert('Por favor ingrese el informe técnico u observación de campo antes de concluir.');
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
