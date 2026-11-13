<?php
/**
 * Vista: Detalle Supervisado de Reclamo (Módulo Administrador)
 * Muestra la ficha completa y gestión de un reclamo técnico con diseño claro, modular y funcional.
 *
 * @var array|null  $trabajo
 * @var string|null $apiFotoBaseUrl
 * @var string|null $error
 * @var string|null $urlVolver
 * @var string|null $textoVolver
 * @var string|null $origen
 */

$error = isset($error) && $error ? $error : (isset($_SESSION['error']) ? $_SESSION['error'] : null);
unset($_SESSION['error']);

$urlVolver   = isset($urlVolver) && !empty($urlVolver) ? $urlVolver : '/administrador/trabajos';
$textoVolver = isset($textoVolver) && !empty($textoVolver) ? $textoVolver : 'Volver a la lista';
$origen      = isset($origen) ? $origen : '';

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

// Formateo de fecha y cálculo de plazo SLA (24h)
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

$plazoSla = function_exists('calcularPlazo24h') ? calcularPlazo24h($fechaStr, $estadoCalculado) : null;

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

$esAdmin = function_exists('hasRole') ? hasRole('Administrador') : (isset($_SESSION['usuario']['nombre_rol']) && $_SESSION['usuario']['nombre_rol'] === 'Administrador');

$esPendiente     = ($estadoCalculado === 'PENDIENTE' && empty($trabajo['estado_interno']));
$mostrarOperador = $esAdmin && !$esPendiente && ($esEstadoNoConcluido || $esFinalizadoEnApi);

$operadorMostrar = $mostrarOperador ? (!empty($trabajo['operador_nombre']) 
    ? $trabajo['operador_nombre'] 
    : (!empty($trabajo['operador_reporta']) 
        ? $trabajo['operador_reporta'] 
        : (!empty($trabajo['nombre_operador']) ? $trabajo['nombre_operador'] : ''))) : '';

$fechaOperador = !empty($trabajo['fecha_trabajo']) 
    ? $trabajo['fecha_trabajo'] 
    : (!empty($trabajo['fecha_seguimiento']) ? $trabajo['fecha_seguimiento'] : '');
$fechaOperadorFormateada = '';
if (!empty($fechaOperador)) {
    $fo = date_create($fechaOperador);
    $fechaOperadorFormateada = $fo ? date_format($fo, 'd/m/Y H:i') : $fechaOperador;
}
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4 border-bottom pb-3" style="border-color: #e2e8f0 !important;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h1 class="h3 fw-bold mb-0 text-dark">
                    Reclamo <span class="font-monospace text-primary">#<?= htmlspecialchars($idReclamo, ENT_QUOTES, 'UTF-8') ?></span>
                </h1>
                <?php if ($plazoSla && $plazoSla['estadoPlazo'] !== 'desconocido'): ?>
                    <span class="badge <?= $plazoSla['badgeClass'] ?> font-monospace" style="font-size: 0.75rem;" title="Plazo de resolución: 24h desde la solicitud (Límite: <?= htmlspecialchars($plazoSla['fechaLimite'], ENT_QUOTES, 'UTF-8') ?>)">
                        <?= htmlspecialchars($plazoSla['label'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-secondary small mb-0">
                <span>Solicitud: <strong class="text-dark font-monospace"><?= $fechaFormateada ?></strong></span>
                <?php if (!empty($mostrarOperador) && !empty($operadorMostrar)): ?>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= htmlspecialchars($urlVolver, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="min-height: 38px;">
            <i class="bi bi-arrow-left"></i>
            <span><?= htmlspecialchars($textoVolver, ENT_QUOTES, 'UTF-8') ?></span>
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
            <h2 class="h5 fw-bold text-dark mb-2">No se pudo cargar el reclamo</h2>
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
                <?php include __DIR__ . '/partials/trabajo_historial_detalle/reclamo_info.php'; ?>
            </div>

            <!-- Columna Derecha: Gestión / Conclusión del Reclamo -->
            <div class="col-12 col-lg-5">
                <?php include __DIR__ . '/partials/trabajo_historial_detalle/reclamo_conclusion.php'; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Lógica JS y Modal -->
<?php include __DIR__ . '/partials/trabajo_historial_detalle/reclamo_scripts.php'; ?>
