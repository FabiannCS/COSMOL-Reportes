<?php
/**
 * Vista: Visualización y Filtrado de Consultas y Actividad de la App Móvil
 * 
 * @var string $vista            Modo de vista activo: 'socios' o 'acciones'
 * @var array  $socios           Lista de socios/usuarios únicos agrupados (cuando vista == 'socios')
 * @var array  $accionesPorSocio Lista de acciones indexada por socio_key (cuando vista == 'socios')
 * @var array  $consultas        Lista de consultas detalladas (cuando vista == 'acciones')
 * @var array  $tiposConsulta    Catálogo de tipos de consulta para el filtro de App
 * @var array  $totalesPorTipo   Conteo de consultas agrupadas por cada tipo
 * @var array  $filtros          Filtros aplicados actualmente (fecha_inicio, fecha_fin, id_tipo, buscar)
 * @var int    $pagina           Página actual
 * @var int    $totalPaginas     Total de páginas
 * @var int    $totalRegistros   Total de registros de la vista actual
 * @var int    $totalSocios      Total de usuarios/socios únicos
 * @var int    $totalConsultas   Total de consultas registradas
 * @var int    $limit            Límite de registros por página
 */

$totalesPorTipo = isset($totalesPorTipo) ? $totalesPorTipo : [];
$vista          = isset($vista) ? $vista : 'socios';

// Asignación de estilos semánticos limpios por tipo de evento
$getTipoBadgeClass = function ($idTipo) {
    switch ((int)$idTipo) {
        case 1: // Autenticación / Acceso
            return 'bg-primary-subtle text-primary border-primary-subtle';
        case 2: // Consulta de Deuda
            return 'bg-warning-subtle text-dark border-warning-subtle';
        case 3: // Historial de Facturas
            return 'bg-info-subtle text-info-emphasis border-info-subtle';
        case 5: // Solicitud de Reconexión
            return 'bg-warning-subtle text-warning-emphasis border-warning-subtle';
        case 9: // Descarga de Factura PDF
            return 'bg-success-subtle text-success border-success-subtle';
        case 10: // Pago: Multipago
            return 'bg-secondary-subtle text-secondary border-secondary-subtle';
        case 11: // Pago: Pago al Paso
            return 'bg-warning-subtle text-dark border-warning-subtle';
        case 12: // Pago: Código QR
            return 'bg-info-subtle text-primary border-info-subtle';
        default:
            return 'bg-light text-secondary border-secondary-subtle';
    }
};

$tarjetasConfig = [
    1  => ['label' => 'Autenticación / Acceso',   'border' => '#0284c7'], // Azul COSMOL
    5  => ['label' => 'Solicitud de Reconexión', 'border' => '#ea580c'], // Naranja cálido
    9  => ['label' => 'Descarga de Factura',      'border' => '#059669'], // Verde Esmeralda
    10 => ['label' => 'Pago Multipago',            'border' => '#6366f1'], // Índigo
    12 => ['label' => 'Pago Código QR',            'border' => '#8b5cf6'], // Violeta
];

$getTipoBorderColor = function ($idTipo) use ($tarjetasConfig) {
    if (isset($tarjetasConfig[(int)$idTipo])) {
        return $tarjetasConfig[(int)$idTipo]['border'];
    }
    switch ((int)$idTipo) {
        case 2:  return '#f59e0b'; // Ámbar
        case 3:  return '#0ea5e9'; // Cian
        case 5:  return '#ea580c'; // Naranja
        case 11: return '#d97706'; // Naranja
        default: return '#64748b'; // Pizarra
    }
};

$queryParams = [];
$queryParams['vista'] = $vista;
if (!empty($filtros['fecha_inicio'])) {
    $queryParams['fecha_inicio'] = $filtros['fecha_inicio'];
}
if (!empty($filtros['fecha_fin'])) {
    $queryParams['fecha_fin'] = $filtros['fecha_fin'];
}
if (!empty($filtros['id_tipo'])) {
    $queryParams['id_tipo'] = $filtros['id_tipo'];
}
if (!empty($filtros['buscar'])) {
    $queryParams['buscar'] = $filtros['buscar'];
}

$exportQuery = http_build_query($queryParams);
$exportUrl   = '/reportes/app-movil/exportar' . ($exportQuery ? '?' . $exportQuery : '');

$pageUrl = function ($pageNum) use ($queryParams) {
    $p = array_merge($queryParams, ['p' => $pageNum]);
    return '/reportes/app-movil?' . http_build_query($p);
};

// URLs para alternar entre modos de vista
$paramsSocios   = array_merge($queryParams, ['vista' => 'socios', 'p' => 1]);
$paramsAcciones = array_merge($queryParams, ['vista' => 'acciones', 'p' => 1]);
$urlVistaSocios   = '/reportes/app-movil?' . http_build_query($paramsSocios);
$urlVistaAcciones = '/reportes/app-movil?' . http_build_query($paramsAcciones);

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);

$hayFiltrosActivos = (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']) || !empty($filtros['id_tipo']) || !empty($filtros['buscar']));
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Auditoría y Consultas App Móvil</h1>
            <p class="text-secondary small mb-0">Control de autenticaciones, consultas de estado de cuenta, descargas de facturas y pasarelas de pago.</p>
        </div>
        <div>
            <?php if (hasPermission('reportes.exportar')): ?>
                <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-success btn-sm py-1 px-3 shadow-sm" style="font-size: 0.78rem; font-weight: 500;">
                    Exportar a CSV (<?= $vista === 'socios' ? 'Socios' : 'Eventos' ?>)
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 shadow-sm disabled" disabled title="No posee permiso para exportar reportes" style="font-size: 0.78rem; font-weight: 500;">
                    Exportar a CSV
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de Auditoría por Tipo de Consulta (KPIs Clicables para Filtrado Rápido) -->
    <?php if (!empty($totalesPorTipo)): ?>
        <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-xl-5 mb-4">
            <?php foreach ($totalesPorTipo as $tipoStat): ?>
                <?php
                $idTipoStat = (int)$tipoStat['id_tipo'];
                // Conservar únicamente las 5 tarjetas prioritarias configuradas
                if (!isset($tarjetasConfig[$idTipoStat])) {
                    continue;
                }

                $nombreStat   = $tarjetasConfig[$idTipoStat]['label'];
                $borderColor  = $tarjetasConfig[$idTipoStat]['border'];
                $totalStat    = (int)$tipoStat['total'];
                $esTipoActivo = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === $idTipoStat);

                $paramsFiltro = $queryParams;
                if ($esTipoActivo) {
                    unset($paramsFiltro['id_tipo']);
                } else {
                    $paramsFiltro['id_tipo'] = $idTipoStat;
                }
                $paramsFiltro['p'] = 1;
                $urlFiltroTipo = '/reportes/app-movil' . (!empty($paramsFiltro) ? '?' . http_build_query($paramsFiltro) : '');
                ?>
                <div class="col">
                    <a href="<?= htmlspecialchars($urlFiltroTipo, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="<?= $esTipoActivo ? 'Clic para quitar filtro' : 'Filtrar por ' . htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 <?= $esTipoActivo ? 'bg-primary-subtle' : 'bg-white' ?>" 
                             style="border: 1px solid <?= $esTipoActivo ? '#0284c7' : '#cbd5e1' ?>; border-top: 3px solid <?= $borderColor ?>;">
                            <div class="card-body p-3 text-center">
                                <div class="text-secondary small fw-semibold text-uppercase text-truncate mb-1" style="font-size: 0.72rem; letter-spacing: 0.04em;" title="<?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="h3 fw-bold text-dark mb-0 font-monospace">
                                    <?= number_format($totalStat) ?>
                                </div>
                                <?php if ($esTipoActivo): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.62rem;">
                                            Filtro activo
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Tarjeta de Filtros de Búsqueda -->
    <div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
        <div class="card-body p-3">
            <form action="/reportes/app-movil" method="GET" class="row g-2 align-items-end m-0">
                <input type="hidden" name="vista" value="<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>">

                <!-- Fecha Inicio -->
                <div class="col-6 col-md-2">
                    <label for="fecha_inicio" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Desde</label>
                    <input type="date" 
                           class="form-control form-control-sm" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Fecha Fin -->
                <div class="col-6 col-md-2">
                    <label for="fecha_fin" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Hasta</label>
                    <input type="date" 
                           class="form-control form-control-sm" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Tipo de Evento -->
                <div class="col-12 col-md-3">
                    <label for="id_tipo" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Tipo de Evento</label>
                    <select class="form-select form-select-sm" id="id_tipo" name="id_tipo">
                        <option value="">Todos los eventos de la App</option>
                        <?php foreach ($tiposConsulta as $tipo): ?>
                            <option value="<?= (int)$tipo['id_tipo'] ?>" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === (int)$tipo['id_tipo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Búsqueda General -->
                <div class="col-12 col-md-3">
                    <label for="buscar" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Buscar</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="buscar" 
                           name="buscar" 
                           placeholder="Cód. socio, nombre o teléfono..."
                           value="<?= htmlspecialchars($filtros['buscar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Botones Filtrar y Limpiar -->
                <div class="col-12 col-md-2 d-flex gap-1.5">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        Filtrar
                    </button>
                    <?php if ($hayFiltrosActivos): ?>
                        <a href="/reportes/app-movil?vista=<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-outline-secondary px-2.5 flex-shrink-0" title="Restablecer filtros">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Selector de Modos de Vista (Segmentos COSMOL) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div class="p-1 rounded-3 border d-inline-flex flex-wrap gap-1 w-100 w-md-auto" style="background-color: #f1f5f9; border-color: #e2e8f0 !important;" role="group" aria-label="Modos de Vista">
            <a href="<?= htmlspecialchars($urlVistaSocios, ENT_QUOTES, 'UTF-8') ?>" 
               class="filtro-vista-btn <?= $vista === 'socios' ? 'active' : '' ?>">
                Por Socios (Usuario Únicos) <span class="badge rounded-pill ms-1.5 font-monospace filtro-badge"><?= number_format($totalSocios) ?></span>
            </a>
            <a href="<?= htmlspecialchars($urlVistaAcciones, ENT_QUOTES, 'UTF-8') ?>" 
               class="filtro-vista-btn <?= $vista === 'acciones' ? 'active' : '' ?>">
                Registro Detallado de Eventos <span class="badge rounded-pill ms-1.5 font-monospace filtro-badge"><?= number_format($totalConsultas) ?></span>
            </a>
        </div>
        <div class="small text-secondary d-none d-md-block">
            Mostrando <?= $desde ?> - <?= $hasta ?> de <?= number_format($totalRegistros) ?> registros
        </div>
    </div>

    <!-- Contenido de la Tabla según la Vista Activa -->
    <div class="card shadow-sm border rounded-3 mb-4" id="tabla-reportes" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                    <?= ($vista === 'socios') ? 'Usuarios Únicos de la App Móvil' : 'Historial Detallado de Eventos' ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?= ($vista === 'socios') ? 'Agrupación individual de socios y su volumen de actividad digital.' : 'Cronología detallada de todas las solicitudes y accesos realizados.' ?>
                </p>
            </div>
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5">
                Total: <?= number_format($totalRegistros) ?>
            </span>
        </div>

        <div class="card-body p-0">
            <?php if ($vista === 'socios'): ?>
                <!-- VISTA 1: AGRUPADO POR SOCIO / USUARIO ÚNICO (PRINCIPAL) -->
                
                <!-- Versión Escritorio: Tabla de Alta Densidad -->
                <div class="d-none d-md-block table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-3 ps-sm-4 text-center text-secondary text-uppercase fw-semibold" style="width: 60px; font-size: 0.72rem; letter-spacing: 0.05em;">#</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Cód. Socio</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Titular del Servicio</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Teléfono</th>
                                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Total Eventos</th>
                                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 0.72rem; letter-spacing: 0.05em;">Última Actividad</th>
                                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($socios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        <p class="mb-1 fw-semibold text-dark">No se encontraron usuarios</p>
                                        <p class="small text-muted mb-0">No existen registros que coincidan con los criterios de búsqueda aplicados.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($socios as $idx => $s): ?>
                                    <?php 
                                    $numUsuario = $desde + $idx;
                                    $sKey = $s['socio_key'];
                                    $misAcciones = isset($accionesPorSocio[$sKey]) ? $accionesPorSocio[$sKey] : [];
                                    $collapseId = 'collapse-app-socio-' . $idx;
                                    $codSocio = !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C';
                                    ?>
                                    <tr class="align-middle">
                                        <td class="ps-3 ps-sm-4 text-center text-muted font-monospace small">
                                            #<?= $numUsuario ?>
                                        </td>
                                        <td>
                                            <span class="font-monospace text-secondary small">
                                                <?= $codSocio ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Sin nombre registrado', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </td>
                                        <td class="font-monospace small text-secondary">
                                            <?= !empty($s['telefono']) ? htmlspecialchars($s['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">
                                                <?= (int)$s['total_consultas'] ?> <?= (int)$s['total_consultas'] === 1 ? 'evento' : 'eventos' ?>
                                            </span>
                                        </td>
                                        <td class="text-center text-muted font-monospace small">
                                            <div><?= htmlspecialchars($s['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div style="font-size: 0.72rem;"><?= htmlspecialchars($s['ultima_hora'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td class="text-end pe-3 pe-sm-4 text-nowrap">
                                            <button class="btn btn-sm btn-outline-primary px-2.5 py-1"
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#<?= $collapseId ?>" 
                                                    aria-expanded="false" 
                                                    aria-controls="<?= $collapseId ?>">
                                                Ver actividad (<?= (int)$s['total_consultas'] ?>)
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Fila Colapsable de Detalle de Actividad -->
                                    <tr class="p-0 border-0">
                                        <td colspan="7" class="p-0 border-0">
                                            <div class="collapse" id="<?= $collapseId ?>">
                                                <div class="p-3 bg-light border-top border-bottom" style="border-color: #e2e8f0;">
                                                    <div class="card border rounded-3 shadow-sm bg-white" style="border-color: #cbd5e1;">
                                                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom" style="border-color: #e2e8f0;">
                                                            <div class="text-dark small fw-semibold">
                                                                Actividad de <?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Titular', ENT_QUOTES, 'UTF-8') ?> (Cód. <?= $codSocio ?>)
                                                            </div>
                                                            <span class="badge bg-light text-secondary border fw-normal">
                                                                <?= count($misAcciones) ?> <?= count($misAcciones) === 1 ? 'registro' : 'registros' ?>
                                                            </span>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover mb-0 align-middle">
                                                                    <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                                        <tr>
                                                                            <th class="ps-3 py-2 text-secondary text-uppercase fw-semibold" style="width: 50px; font-size: 0.68rem;">#</th>
                                                                            <th class="py-2 text-secondary text-uppercase fw-semibold" style="font-size: 0.68rem;">Acción / Evento</th>
                                                                            <th class="py-2 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 0.68rem;">Teléfono</th>
                                                                            <th class="pe-3 py-2 text-end text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.68rem;">Fecha y Hora</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if (empty($misAcciones)): ?>
                                                                            <tr>
                                                                                <td colspan="4" class="text-center py-3 text-muted small">
                                                                                    No hay eventos adicionales para este socio en el filtro actual.
                                                                                </td>
                                                                            </tr>
                                                                        <?php else: ?>
                                                                            <?php foreach ($misAcciones as $accIdx => $acc): ?>
                                                                                <?php $badgeClass = $getTipoBadgeClass($acc['id_tipo'] ?? 0); ?>
                                                                                <tr>
                                                                                    <td class="ps-3 py-2 text-muted font-monospace small"><?= $accIdx + 1 ?></td>
                                                                                    <td class="py-2">
                                                                                        <span class="badge border fw-normal py-1 px-2 <?= $badgeClass ?>">
                                                                                            <?= htmlspecialchars($acc['tipo'] ?? 'Consulta', ENT_QUOTES, 'UTF-8') ?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="py-2 font-monospace small text-secondary">
                                                                                        <?= !empty($acc['telefono']) ? htmlspecialchars($acc['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                                                                                    </td>
                                                                                    <td class="pe-3 py-2 text-end text-muted font-monospace small">
                                                                                        <?= htmlspecialchars($acc['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($acc['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                                                                    </td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Versión Móvil: Fichas de Socios Adaptadas (< 768px) -->
                <div class="d-block d-md-none p-3" id="listaMobileSocios">
                    <?php if (empty($socios)): ?>
                        <div class="text-center py-4 text-secondary">
                            <p class="mb-1 fw-semibold text-dark">No se encontraron usuarios</p>
                            <p class="small text-muted mb-0">No existen registros que coincidan con los criterios aplicados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($socios as $idx => $s): ?>
                            <?php 
                            $numUsuario = $desde + $idx;
                            $sKey = $s['socio_key'];
                            $misAcciones = isset($accionesPorSocio[$sKey]) ? $accionesPorSocio[$sKey] : [];
                            $collapseId = 'collapse-mobile-socio-' . $idx;
                            $codSocio = !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C';
                            ?>
                            <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-monospace fw-bold text-muted small">#<?= $numUsuario ?> &bull; Cód. <?= $codSocio ?></span>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= (int)$s['total_consultas'] ?> <?= (int)$s['total_consultas'] === 1 ? 'evento' : 'eventos' ?>
                                    </span>
                                </div>
                                <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Sin nombre registrado', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="d-flex justify-content-between align-items-center small text-secondary mt-1">
                                    <span>Tel: <?= !empty($s['telefono']) ? htmlspecialchars($s['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?></span>
                                    <span class="text-muted font-monospace" style="font-size: 0.74rem;">Último: <?= htmlspecialchars($s['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <div class="mt-2.5 pt-2 border-top" style="border-color: #f1f5f9;">
                                    <button class="btn btn-sm btn-outline-primary w-100 py-1"
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#<?= $collapseId ?>" 
                                            aria-expanded="false" 
                                            aria-controls="<?= $collapseId ?>">
                                        Ver actividad detallada (<?= (int)$s['total_consultas'] ?>)
                                    </button>

                                    <div class="collapse mt-2" id="<?= $collapseId ?>">
                                        <div class="border rounded-2 p-2 bg-light">
                                            <?php if (empty($misAcciones)): ?>
                                                <div class="text-center text-muted small py-2">Sin actividad adicional</div>
                                            <?php else: ?>
                                                <?php foreach ($misAcciones as $acc): ?>
                                                    <?php $badgeClass = $getTipoBadgeClass($acc['id_tipo'] ?? 0); ?>
                                                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-color: #e2e8f0;">
                                                        <span class="badge border fw-normal <?= $badgeClass ?>" style="font-size: 0.7rem;">
                                                            <?= htmlspecialchars($acc['tipo'] ?? 'Consulta', ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                        <span class="font-monospace text-muted" style="font-size: 0.72rem;">
                                                            <?= htmlspecialchars($acc['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- VISTA 2: REGISTRO DETALLADO DE EVENTOS -->
                
                <!-- Versión Escritorio -->
                <div class="d-none d-md-block table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 80px; font-size: 0.72rem; letter-spacing: 0.05em;"># ID</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Cód. Socio</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Titular</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Teléfono</th>
                                <th class="text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 0.72rem; letter-spacing: 0.05em;">Acción / Evento</th>
                                <th class="pe-3 pe-sm-4 text-end text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($consultas)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <p class="mb-1 fw-semibold text-dark">No se encontraron movimientos registrados para la App Móvil</p>
                                        <p class="small text-muted mb-0">Pruebe modificando los filtros de fecha o búsqueda.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($consultas as $row): ?>
                                    <?php $badgeClass = $getTipoBadgeClass($row['id_tipo'] ?? 0); ?>
                                    <tr>
                                        <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                                            <?= (int)$row['id_consulta'] ?>
                                        </td>
                                        <td class="font-monospace text-secondary small">
                                            <?= htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Socio', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="font-monospace text-secondary small">
                                            <?= !empty($row['telefono']) ? htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                                        </td>
                                        <td>
                                            <span class="badge border fw-normal py-1 px-2 <?= $badgeClass ?>">
                                                <?= htmlspecialchars($row['tipo'] ?? 'Consulta', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="pe-3 pe-sm-4 text-end text-muted font-monospace small">
                                            <?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Versión Móvil: Fichas de Eventos (< 768px) -->
                <div class="d-block d-md-none p-3" id="listaMobileEventos">
                    <?php if (empty($consultas)): ?>
                        <div class="text-center py-4 text-secondary">
                            <p class="mb-1 fw-semibold text-dark">No se encontraron movimientos</p>
                            <p class="small text-muted mb-0">No hay registros con los filtros actuales.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($consultas as $row): ?>
                            <?php $badgeClass = $getTipoBadgeClass($row['id_tipo'] ?? 0); ?>
                            <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-monospace fw-bold text-muted small">#<?= (int)$row['id_consulta'] ?> &bull; Cód. <?= htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="badge border fw-normal <?= $badgeClass ?>" style="font-size: 0.72rem;">
                                        <?= htmlspecialchars($row['tipo'] ?? 'Consulta', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Socio', ENT_QUOTES, 'UTF-8') ?></div>
                                <div class="d-flex justify-content-between align-items-center small text-secondary mt-2 pt-2 border-top" style="border-color: #f1f5f9;">
                                    <span>Tel: <?= !empty($row['telefono']) ? htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?></span>
                                    <span class="text-muted font-monospace" style="font-size: 0.74rem;">
                                        <?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <!-- Paginación y Resumen -->
            <?php if ($totalPaginas > 1 || $totalRegistros > 0): ?>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top bg-light gap-3" style="border-color: #e2e8f0;">
                    <div class="small text-secondary">
                        <?php if ($vista === 'socios'): ?>
                            Mostrando usuarios del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de <strong><?= number_format($totalRegistros) ?> usuarios únicos</strong>
                        <?php else: ?>
                            Mostrando eventos del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de <strong><?= number_format($totalRegistros) ?> eventos registrados</strong>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center justify-content-md-end">
                                <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina - 1) ?>#tabla-reportes" <?= ($pagina <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        Anterior
                                    </a>
                                </li>

                                <?php
                                $startPage = max(1, $pagina - 2);
                                $endPage   = min($totalPaginas, $pagina + 2);
                                if ($startPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>#tabla-reportes">1</a></li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $pageUrl($i) ?>#tabla-reportes"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPaginas): ?>
                                    <?php if ($endPage < $totalPaginas - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pageUrl($totalPaginas) ?>#tabla-reportes"><?= $totalPaginas ?></a></li>
                                <?php endif; ?>

                                <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina + 1) ?>#tabla-reportes" <?= ($pagina >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        Siguiente
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
