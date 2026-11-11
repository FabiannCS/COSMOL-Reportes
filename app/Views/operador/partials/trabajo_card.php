<?php
/**
 * Vista Parcial: Tarjeta de Trabajo para Operadores (Reconexiones y Reclamos)
 * Diseñada para máxima legibilidad técnica en campo, orden lógico y ergonomía táctil.
 *
 * @var array  $t            Datos del trabajo
 * @var string $tipo         'reclamo' o 'reconexion'
 * @var bool   $is_historial (Opcional) Indica si se renderiza en el historial
 */

$is_historial = isset($is_historial) ? (bool)$is_historial : false;
$tipo         = isset($tipo) ? $tipo : 'reconexion';

$id_trabajo = isset($t['id_reclamo']) ? $t['id_reclamo'] : (isset($t['id_reconexion']) ? $t['id_reconexion'] : 'N/A');

// Determinación del estado operativo
if (function_exists('determinarEstadoTrabajo')) {
    $estado = determinarEstadoTrabajo($t);
} elseif (class_exists('App\Services\ApiClient')) {
    $estado = \App\Services\ApiClient::determinarEstadoTrabajo($t);
} else {
    $estado = strtoupper(trim(isset($t['estado']) ? (string)$t['estado'] : 'PENDIENTE'));
}

// Estilos de badge de estado en alto contraste
$badgeClass  = 'bg-warning-subtle text-dark border-warning';
if ($estado === 'CONCLUIDA' || $estado === 'CONCLUIDO') {
    $badgeClass = 'bg-success-subtle text-success border-success';
} elseif ($estado === 'NO CONCLUIDO') {
    $badgeClass = 'bg-danger-subtle text-danger border-danger';
} elseif ($estado === 'NO PROCEDENTE' || $estado === 'ANULADO' || $estado === 'CANCELADO') {
    $badgeClass = 'bg-secondary-subtle text-secondary border-secondary';
}

$socio        = isset($t['nombre_socio']) && !empty($t['nombre_socio']) ? $t['nombre_socio'] : (isset($t['nombre']) ? $t['nombre'] : '');
$codSocio     = isset($t['cod_socio']) && !empty($t['cod_socio']) ? $t['cod_socio'] : (isset($t['codigo_socio']) ? $t['codigo_socio'] : '');
$direccion    = isset($t['direccion_predio']) && !empty($t['direccion_predio']) ? $t['direccion_predio'] : (isset($t['direccion']) ? $t['direccion'] : '');
$zona         = isset($t['zona']) ? trim((string)$t['zona']) : '';
$ruta         = isset($t['ruta']) ? trim((string)$t['ruta']) : '';

// Construcción amigable y explícita de Zona y Ruta (ej. "Zona 2 Ruta 24")
$textoZona = '';
if (!empty($zona)) {
    $textoZona = (stripos($zona, 'zona') === false ? 'Zona ' : '') . $zona;
}

$textoRuta = '';
if (!empty($ruta)) {
    $textoRuta = (stripos($ruta, 'ruta') === false ? 'Ruta ' : '') . $ruta;
}

$zonaRuta = trim($textoZona . ' ' . $textoRuta);
if (empty($zonaRuta)) {
    $zonaRuta = 'Zona / Ruta no asignada';
}

$medidor      = isset($t['medidor']) && !empty($t['medidor']) ? $t['medidor'] : (isset($t['num_medidor']) ? $t['num_medidor'] : '');
$descripcion  = isset($t['descripcion']) && !empty($t['descripcion']) ? $t['descripcion'] : ($tipo === 'reconexion' ? 'Reconexión de servicio de agua' : 'Sin descripción técnica');
$glosa        = isset($t['glosa']) ? $t['glosa'] : '';

// Validación de tipo de ubicación: GPS vs Mi Domicilio
$rawGps = isset($t['coordenadas_gps']) ? trim((string)$t['coordenadas_gps']) : '';
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
$fechaStr = $is_historial ? (isset($t['fecha_actualizacion']) ? $t['fecha_actualizacion'] : (isset($t['fecha_registro']) ? $t['fecha_registro'] : '')) : (isset($t['fecha_registro']) ? $t['fecha_registro'] : '');
$fechaFormateada = '—';
if ($fechaStr) {
    $fechaObj = date_create($fechaStr);
    $fechaFormateada = $fechaObj ? date_format($fechaObj, 'd/m/Y H:i') : htmlspecialchars($fechaStr, ENT_QUOTES, 'UTF-8');
}

// Botón de acción con texto explícito
if ($is_historial) {
    $textoBoton = 'Ver Detalle';
    $btnClass   = 'btn-outline-secondary';
} else {
    $textoBoton = ($tipo === 'reconexion') ? 'Atender Reconexión' : 'Atender Reclamo';
    $btnClass   = 'btn-primary';
}
?>
<div class="col col-trabajo">
    <div class="card h-100 bg-white d-flex flex-column rounded-2 shadow-sm" 
         style="border: 1px solid #94a3b8 !important;">
        
        <!-- 1. Encabezado de la Tarjeta: Identificación (Sin background), Estado y Fecha -->
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3" 
             style="border-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-secondary fw-semibold font-monospace" style="font-size: 0.85rem;">
                    Orden #<?= htmlspecialchars($id_trabajo, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="badge border fw-bold <?= $badgeClass ?>" style="font-size: 0.72rem; letter-spacing: 0.03em;">
                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <span class="text-secondary small font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" title="Fecha de asignación">
                <i class="bi bi-calendar3"></i>
                <?= $fechaFormateada ?>
            </span>
        </div>

        <!-- 2. Fila Clave Operativa: Zona y Ruta (Ej. "Zona 2 Ruta 24") -->
        <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center justify-content-between" 
             style="border-color: #e2e8f0 !important;">
            <div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem; letter-spacing: 0.02em;">
                <i class="bi bi-geo-alt-fill text-primary me-1"></i><?= htmlspecialchars($zonaRuta, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <div class="card-body p-3 d-flex flex-column gap-3">
            
            <!-- 3. Dirección del Predio -->
            <div class="p-2.5 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                <span class="text-secondary fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                    Dirección
                </span>
                <div class="fw-semibold text-dark text-break fs-6 mb-0" style="line-height: 1.35;">
                    <?= !empty($direccion) ? htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') : '<span class="text-muted fst-italic">Dirección no especificada</span>' ?>
                </div>
            </div>

            <!-- 4. Identificación del Socio Titular -->
            <div>
                <div class="d-flex justify-content-between align-items-baseline mb-0.5">
                    <span class="text-secondary fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                        Socio Titular
                    </span>
                    <?php if (!empty($codSocio)): ?>
                        <span class="text-secondary small" style="font-size: 0.78rem;">
                            Cód: <strong class="text-dark font-monospace"><?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="fw-bold text-dark text-truncate fs-6" title="<?= htmlspecialchars(trim($socio), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(!empty($socio) ? trim($socio) : 'No especificado', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>

            <!-- 5. Datos Técnicos: Medidor y Motivo -->
            <?php if (!empty($medidor)): ?>
                <div class="row g-2">
                    <div class="col-5">
                        <div class="p-2 rounded bg-light border text-center h-100 d-flex flex-column justify-content-center" style="border-color: #e2e8f0 !important;">
                            <span class="text-secondary fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                                <i class="bi bi-speedometer2 text-primary me-0.5"></i> Medidor
                            </span>
                            <span class="font-monospace fw-bold text-dark text-truncate d-block" style="font-size: 0.92rem;">
                                <?= htmlspecialchars($medidor, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="p-2 rounded bg-light border h-100 d-flex flex-column justify-content-center" style="border-color: #e2e8f0 !important;">
                            <span class="text-secondary fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                                Motivo / Detalle
                            </span>
                            <span class="text-dark small text-truncate d-block fw-medium" title="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-2 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                    <span class="text-secondary fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                        Motivo / Detalle
                    </span>
                    <span class="text-dark small d-block fw-medium text-break">
                        <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($is_historial && !empty($glosa)): ?>
                <div class="p-2 rounded bg-light border-start border-3 border-secondary text-break small">
                    <strong class="text-dark d-block" style="font-size: 0.72rem; letter-spacing: 0.04em;">CONCLUSIÓN REGISTRADA:</strong>
                    <span class="text-dark fst-italic"><?= htmlspecialchars($glosa, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- 6. Pie de Tarjeta: Acciones Operativas y Accesos Directos -->
        <div class="card-footer bg-white border-top p-2.5 p-md-3 d-flex flex-wrap align-items-center justify-content-between gap-2" 
             style="border-color: #e2e8f0 !important;">
            <?php if ($esGpsValido): ?>
                <?php 
                    $urlGps = "https://www.google.com/maps?q={$coordsGpsLimpia}";
                ?>
                <a href="<?= htmlspecialchars($urlGps, ENT_QUOTES, 'UTF-8') ?>" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn btn-outline-danger btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-2.5" 
                   style="min-height: 44px; font-size: 0.8125rem;" 
                   title="Abrir ubicación en Google Maps">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Mapa GPS</span>
                </a>
            <?php endif; ?>

            <a href="/operador/detalle?id=<?= urlencode($id_trabajo) ?>" 
               class="btn <?= $btnClass ?> fw-bold px-3 d-inline-flex align-items-center justify-content-center gap-1.5 <?= $esGpsValido ? 'flex-grow-1' : 'w-100' ?>" 
               style="min-height: 44px; font-size: 0.875rem;">
                <span><?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8') ?></span>
                <i class="bi bi-arrow-right-short fs-5"></i>
            </a>
        </div>
    </div>
</div>
