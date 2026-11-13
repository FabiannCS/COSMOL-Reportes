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

// 1. Socio Titular
$socio = isset($t['nombre_socio']) && !empty($t['nombre_socio']) 
    ? trim((string)$t['nombre_socio']) 
    : (isset($t['nombre']) && !empty($t['nombre']) ? trim((string)$t['nombre']) : 'No especificado');

// 2. Motivo
if ($tipo === 'reconexion') {
    $motivo = !empty($t['glosa']) 
        ? trim((string)$t['glosa']) 
        : (!empty($t['descripcion']) ? trim((string)$t['descripcion']) : 'Reconexión de servicio de agua potable');
} else {
    $motivo = !empty($t['descripcion']) 
        ? trim((string)$t['descripcion']) 
        : (!empty($t['glosa']) ? trim((string)$t['glosa']) : 'Reclamo de servicio técnico');
}

// 3. Ubicación y Validación: GPS vs Domicilio Registrado
$direccion = isset($t['direccion_predio']) && !empty($t['direccion_predio']) 
    ? trim((string)$t['direccion_predio']) 
    : (isset($t['direccion']) && !empty($t['direccion']) ? trim((string)$t['direccion']) : '');

$zona      = isset($t['zona']) ? trim((string)$t['zona']) : '';
$ruta      = isset($t['ruta']) ? trim((string)$t['ruta']) : '';
$ubicacion = isset($t['ubicacion']) ? trim((string)$t['ubicacion']) : '';

// Construcción de Zona y Ruta
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

$rawGps    = isset($t['coordenadas_gps']) ? trim((string)$t['coordenadas_gps']) : '';
$dirPredio = isset($t['direccion_predio']) ? trim((string)$t['direccion_predio']) : (isset($t['direccion']) ? trim((string)$t['direccion']) : '');
$gpsInfo   = function_exists('normalizarYValidarGps') 
    ? normalizarYValidarGps($rawGps, $dirPredio) 
    : ['esGps' => false, 'coordsLimpia' => '', 'urlMaps' => '', 'esDomicilio' => true, 'enMontero' => false];

$esGpsValido     = $gpsInfo['esGps'];
$coordsGpsLimpia = $gpsInfo['coordsLimpia'];
$esMiDomicilio   = $gpsInfo['esDomicilio'];

// 4. Tipo de Reconexión (si aplica)
$idTipoReconexion = isset($t['id_tipo_reconexion']) ? (int)$t['id_tipo_reconexion'] : 1;
$tipoReconexionTexto = 'Corte normal';
$tipoReconexionDesc  = 'Sin retiro de medidor';

switch ($idTipoReconexion) {
    case 1:
        $tipoReconexionTexto = 'Corte normal';
        $tipoReconexionDesc  = 'Sin retiro de medidor';
        break;
    case 2:
        $tipoReconexionTexto = 'Con medidor';
        $tipoReconexionDesc  = 'Retiro de medidor - Requiere instalar nuevo medidor';
        break;
    case 3:
        $tipoReconexionTexto = 'Con material';
        $tipoReconexionDesc  = 'Falta de accesorios - Requiere accesorios de plomería';
        break;
    case 4:
        $tipoReconexionTexto = 'Otros';
        $tipoReconexionDesc  = 'Otro motivo registrado';
        break;
}

// Glosa histórica
$glosa = isset($t['glosa']) ? trim((string)$t['glosa']) : '';

// Formateo de fecha y SLA (24 horas)
$fechaRegistroRaw = isset($t['fecha_registro']) ? $t['fecha_registro'] : '';
$plazoSla = function_exists('calcularPlazo24h') ? calcularPlazo24h($fechaRegistroRaw, $estado) : null;

$fechaStr = $is_historial ? (isset($t['fecha_actualizacion']) ? $t['fecha_actualizacion'] : $fechaRegistroRaw) : $fechaRegistroRaw;
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
        
        <!-- Encabezado de la Tarjeta: Identificación, Estado y Fecha / Plazo (24h) -->
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
            <div class="text-end d-flex flex-column align-items-end">
                <span class="text-secondary small font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" title="Fecha de llegada de la solicitud">
                    <i class="bi bi-clock text-muted" aria-hidden="true"></i>
                    <?= $fechaFormateada ?>
                </span>
                <?php if (!$is_historial && $plazoSla && $plazoSla['estadoPlazo'] !== 'desconocido'): ?>
                    <span class="badge <?= $plazoSla['badgeClass'] ?> mt-1 font-monospace" style="font-size: 0.68rem;" title="Plazo de atención: 24h desde la solicitud (Límite: <?= htmlspecialchars($plazoSla['fechaLimite'], ENT_QUOTES, 'UTF-8') ?>)">
                        <?= htmlspecialchars($plazoSla['label'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cuerpo de la Tarjeta con Orden y Separación Consistente -->
        <div class="card-body p-3 d-flex flex-column">
            
            <!-- 1. Socio Titular -->
            <div class="py-1">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.90rem; letter-spacing: 0.02em;">
                    Socio Titular
                </div>
                <div class="text-secondary fs-6 text-break">
                    <?= htmlspecialchars($socio, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>

            <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

            <!-- 2. Motivo -->
            <div class="py-1">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.90rem; letter-spacing: 0.02em;">
                    Motivo
                </div>
                <div class="text-secondary fs-6 text-break">
                    <?= htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>

            <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

            <!-- 3. Ubicación -->
            <div class="py-1">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.90rem; letter-spacing: 0.02em;">
                    Ubicación
                </div>
                <div class="text-secondary fs-6 text-break">
                    <?php if ($esGpsValido): ?>
                        <div>Ubicación referencial enviada por el socio (GPS)</div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 mt-1.5 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                            <div class="fw-bold text-danger font-monospace" style="font-size: 0.82rem; letter-spacing: 0.02em;">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>Coordenadas GPS
                            </div>
                        </div>
                    <?php else: ?>
                        <div>
                            <?= !empty($direccion) ? htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') : '<span class="text-muted fst-italic">Dirección no especificada</span>' ?>
                            <span class="text-muted small ms-1">(Domicilio registrado)</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 mt-1.5 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                            <div class="fw-bold text-dark font-monospace" style="font-size: 0.85rem; letter-spacing: 0.02em;">
                                <i class="bi bi-geo-alt-fill text-primary me-1"></i><?= htmlspecialchars($zonaRuta, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <?php if (!empty($ubicacion)): ?>
                                <span class="text-secondary font-monospace small" style="font-size: 0.75rem;">
                                    U-Z-R: <strong class="text-dark"><?= htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($tipo === 'reconexion'): ?>
                <!-- 4. Tipo de Reconexión (solo en caso de ser reconexión) -->
                <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">
                <div class="py-1">
                    <div class="fw-bold text-dark mb-1" style="font-size: 0.90rem; letter-spacing: 0.02em;">
                        Tipo de Reconexión
                    </div>
                    <div class="text-secondary fs-6">
                        <?= htmlspecialchars($tipoReconexionTexto, ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($tipoReconexionDesc)): ?>
                            <span class="text-muted small ms-1">(<?= htmlspecialchars($tipoReconexionDesc, ENT_QUOTES, 'UTF-8') ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($is_historial && !empty($glosa)): ?>
                <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">
                <div class="py-1">
                    <div class="fw-bold text-dark mb-1" style="font-size: 0.90rem; letter-spacing: 0.02em;">
                        Conclusión Registrada
                    </div>
                    <div class="text-secondary fs-6 fst-italic text-break">
                        <?= htmlspecialchars($glosa, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pie de Tarjeta: Acciones Operativas y Accesos Directos -->
        <div class="card-footer bg-white border-top p-2.5 p-md-3 d-flex flex-wrap align-items-center justify-content-between gap-2 mt-auto" 
             style="border-color: #e2e8f0 !important;">
            <?php if ($esGpsValido): ?>
                <a href="<?= htmlspecialchars($gpsInfo['urlMaps'], ENT_QUOTES, 'UTF-8') ?>" 
                   target="_blank" 
                   rel="noopener noreferrer" 
                   class="btn btn-outline-danger btn-sm fw-semibold d-inline-flex align-items-center gap-1 px-2.5 <?= empty($gpsInfo['enMontero']) ? 'border-warning text-warning-emphasis' : '' ?>" 
                   style="min-height: 44px; font-size: 0.8125rem;" 
                   title="<?= empty($gpsInfo['enMontero']) ? 'Ubicación GPS referencial (fuera de Montero)' : 'Abrir ubicación GPS en Google Maps' ?>">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
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
