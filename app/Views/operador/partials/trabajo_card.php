<?php
/**
 * Vista Parcial: Tarjeta de Trabajo para Operadores (Reconexiones y Reclamos)
 * Diseñada para máxima simplicidad, claridad explícita y sin iconos superfluos.
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
$borderCard  = '#cbd5e1';
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
$medidor      = isset($t['medidor']) && !empty($t['medidor']) ? $t['medidor'] : (isset($t['num_medidor']) ? $t['num_medidor'] : '');
$descripcion  = isset($t['descripcion']) && !empty($t['descripcion']) ? $t['descripcion'] : ($tipo === 'reconexion' ? 'Reconexión de servicio de agua' : 'Sin descripción técnica');
$glosa        = isset($t['glosa']) ? $t['glosa'] : '';
$coordsGps    = isset($t['coordenadas_gps']) && !empty($t['coordenadas_gps']) ? trim((string)$t['coordenadas_gps']) : '';
$foto         = isset($t['foto']) && !empty($t['foto']) ? $t['foto'] : null;

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

// Texto compuesto para búsqueda interactiva
$busquedaTexto = trim($id_trabajo . ' ' . $socio . ' ' . $codSocio . ' ' . $direccion . ' ' . $zona . ' ' . $ruta . ' ' . $medidor . ' ' . $descripcion);
?>
<div class="col col-trabajo" data-busqueda="<?= htmlspecialchars(strtolower($busquedaTexto), ENT_QUOTES, 'UTF-8') ?>">
    <div class="card h-100 shadow-sm border bg-white d-flex flex-column rounded-2" 
         style="border-color: #cbd5e1 !important;">
        
        <!-- Encabezado de la Tarjeta: Número de Orden, Estado y Fecha -->
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3" 
             style="border-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark font-monospace" style="font-size: 0.9rem;">
                    Orden #<?= htmlspecialchars($id_trabajo, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="badge border fw-bold <?= $badgeClass ?>" style="font-size: 0.72rem; letter-spacing: 0.03em;">
                    <?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <span class="text-secondary small font-monospace" style="font-size: 0.75rem;">
                <?= $fechaFormateada ?>
            </span>
        </div>

        <div class="card-body p-3 d-flex flex-column gap-2">
            <!-- Datos del Socio -->
            <div>
                <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                    Socio Titular
                </div>
                <div class="fw-bold text-dark text-truncate fs-6" title="<?= htmlspecialchars(trim($socio), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(!empty($socio) ? trim($socio) : 'No especificado', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php if (!empty($codSocio)): ?>
                    <div class="text-secondary small" style="font-size: 0.8125rem;">
                        Código de Socio: <strong class="text-dark font-monospace"><?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recuadro Técnico: Dirección, Zona, Ruta y Medidor -->
            <div class="p-2.5 rounded bg-light border small" style="border-color: #e2e8f0 !important; font-size: 0.8125rem;">
                <?php if (!empty($direccion)): ?>
                    <div class="mb-1.5 text-break">
                        <strong class="text-dark">Dirección:</strong> 
                        <span class="text-dark"><?= htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-x-3 gap-y-1 text-secondary">
                    <?php if (!empty($zona)): ?>
                        <span><strong class="text-dark">Zona:</strong> <?= htmlspecialchars($zona, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($ruta)): ?>
                        <span class="ms-2"><strong class="text-dark">Ruta:</strong> <?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if (!empty($medidor)): ?>
                        <span class="ms-2"><strong class="text-dark">Medidor:</strong> <strong class="font-monospace text-dark"><?= htmlspecialchars($medidor, ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detalle o Motivo del Trabajo -->
            <div class="small text-secondary flex-grow-1" style="font-size: 0.8125rem;">
                <strong class="text-dark">Detalle:</strong> <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>

                <?php if ($is_historial && !empty($glosa)): ?>
                    <div class="mt-2 p-2 rounded bg-light border-start border-3 border-secondary text-break">
                        <strong class="text-dark d-block" style="font-size: 0.72rem; letter-spacing: 0.04em;">CONCLUSIÓN REGISTRADA:</strong>
                        <span class="text-dark fst-italic"><?= htmlspecialchars($glosa, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pie de Tarjeta: Botones Claros y Explícitos -->
        <div class="card-footer bg-white border-top p-2.5 p-md-3 d-flex flex-wrap align-items-center justify-content-between gap-2" 
             style="border-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center gap-1.5">
                <?php if (!empty($coordsGps)): ?>
                    <?php 
                        $coordsLimpia = preg_replace('/\s+/', '', $coordsGps);
                        $urlGps = "https://www.google.com/maps?q={$coordsLimpia}";
                    ?>
                    <a href="<?= htmlspecialchars($urlGps, ENT_QUOTES, 'UTF-8') ?>" 
                       target="_blank" 
                       rel="noopener noreferrer" 
                       class="btn btn-sm btn-outline-danger fw-semibold px-2.5 py-1" 
                       style="font-size: 0.78rem;" 
                       title="Abrir ubicación exacta en Google Maps">
                        Abrir Mapa GPS
                    </a>
                <?php endif; ?>

                <?php if (!empty($foto)): ?>
                    <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">
                        Tiene Foto
                    </span>
                <?php endif; ?>
            </div>

            <a href="/operador/detalle?id=<?= urlencode($id_trabajo) ?>" 
               class="btn btn-sm <?= $btnClass ?> fw-bold px-3 py-1.5" 
               style="font-size: 0.85rem; min-height: 40px; display: inline-flex; align-items: center; justify-content: center;">
                <?= htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
    </div>
</div>
