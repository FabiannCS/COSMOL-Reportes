<?php
/**
 * Vista: Visualización y Filtrado de Reportes de Consultas del Chatbot
 * 
 * @var string $vista            Modo de vista activo: 'socios' o 'acciones'
 * @var array  $socios           Lista de socios/usuarios únicos agrupados (cuando vista == 'socios')
 * @var array  $accionesPorSocio Lista de acciones indexada por socio_key (cuando vista == 'socios')
 * @var array  $consultas        Lista de consultas detalladas (cuando vista == 'acciones')
 * @var array  $tiposConsulta    Catálogo de tipos de consulta para el filtro
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

$getTipoStyle = function ($idTipo, $nombre) {
    switch ((int)$idTipo) {
        case 1: // Autenticación / Acceso
            return ['color' => 'secondary', 'icon' => 'bi-shield-check'];
        case 2: // Consulta de Deuda
            return ['color' => 'warning', 'icon' => 'bi-cash-coin'];
        case 3: // Historial de Facturas
            return ['color' => 'info', 'icon' => 'bi-receipt-cutoff'];
        case 4: // Registro de Reclamo
            return ['color' => 'danger', 'icon' => 'bi-exclamation-triangle-fill'];
        case 5: // Solicitud de Reconexión
            return ['color' => 'success', 'icon' => 'bi-arrow-repeat'];
        case 6: // Información de Oficinas
            return ['color' => 'dark', 'icon' => 'bi-building-fill'];
        case 7: // Derivación a Agente
            return ['color' => 'primary', 'icon' => 'bi-headset'];
        case 8: // Estado de Solicitudes
            return ['color' => 'info', 'icon' => 'bi-clock-history'];
        default:
            return ['color' => 'primary', 'icon' => 'bi-chat-dots-fill'];
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
$exportUrl   = '/reportes/exportar' . ($exportQuery ? '?' . $exportQuery : '');

$pageUrl = function ($pageNum) use ($queryParams) {
    $p = array_merge($queryParams, ['p' => $pageNum]);
    return '/reportes/visualizar?' . http_build_query($p);
};

// URLs para alternar entre modos de vista
$paramsSocios   = array_merge($queryParams, ['vista' => 'socios', 'p' => 1]);
$paramsAcciones = array_merge($queryParams, ['vista' => 'acciones', 'p' => 1]);
$urlVistaSocios   = '/reportes/visualizar?' . http_build_query($paramsSocios);
$urlVistaAcciones = '/reportes/visualizar?' . http_build_query($paramsAcciones);

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-chat-dots-fill text-primary me-2"></i>Consultas Chatbot WhatsApp
            </h1>
            <p class="text-muted small mb-0">Auditoría, usuarios individuales y desglose de consultas registradas mediante el chatbot de COSMOL.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (hasPermission('reportes.exportar')): ?>
                <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Exportar a CSV (<?= $vista === 'socios' ? 'Socios' : 'Acciones' ?>)</span>
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm disabled" disabled title="No posee permiso para exportar reportes">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Exportar a CSV</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de Métricas por Tipo de Consulta -->
    <?php if (!empty($totalesPorTipo)): ?>
        <div class="row g-3 g-md-4 mb-4">
            <?php foreach ($totalesPorTipo as $tipoStat): ?>
                <?php
                $idTipoStat   = (int)$tipoStat['id_tipo'];
                $nombreStat   = $tipoStat['nombre'];
                $totalStat    = (int)$tipoStat['total'];
                $estilo       = $getTipoStyle($idTipoStat, $nombreStat);
                $esTipoActivo = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === $idTipoStat);

                // Parámetros de URL al hacer clic en la tarjeta
                $paramsFiltro = $queryParams;
                if ($esTipoActivo) {
                    unset($paramsFiltro['id_tipo']);
                } else {
                    $paramsFiltro['id_tipo'] = $idTipoStat;
                }
                $paramsFiltro['p'] = 1;
                $urlFiltroTipo = '/reportes/visualizar' . (!empty($paramsFiltro) ? '?' . http_build_query($paramsFiltro) : '');
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <a href="<?= htmlspecialchars($urlFiltroTipo, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="<?= $esTipoActivo ? 'Clic para quitar filtro' : 'Filtrar por ' . htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="card border-0 shadow-sm border-start border-<?= $estilo['color'] ?> border-4 h-100 py-3 <?= $esTipoActivo ? 'bg-light border-top border-end border-bottom border-primary-subtle' : 'bg-white' ?>">
                            <div class="card-body text-center p-2">
                                <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                                    <i class="bi <?= $estilo['icon'] ?> text-<?= $estilo['color'] ?> me-1"></i>
                                    <span><?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="display-6 fw-bold text-dark mb-0">
                                    <?= number_format($totalStat) ?>
                                </div>
                                <?php if ($esTipoActivo): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-funnel-fill me-1"></i>Filtro activo (Quitar)
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

    <!-- Tarjeta de Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold text-dark">
                <i class="bi bi-funnel me-2 text-primary"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="/reportes/visualizar" method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="vista" value="<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>">

                <div class="col-12 col-md-3">
                    <label for="buscar" class="form-label fw-semibold small text-muted">Búsqueda</label>
                    <input type="text" 
                           class="form-control" 
                           id="buscar" 
                           name="buscar" 
                           placeholder="Cód. Socio, Nombre o Teléfono..."
                           value="<?= htmlspecialchars($filtros['buscar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="fecha_inicio" class="form-label fw-semibold small text-muted">Fecha Inicio</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="fecha_fin" class="form-label fw-semibold small text-muted">Fecha Fin</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label for="id_tipo" class="form-label fw-semibold small text-muted">Tipo de Consulta</label>
                    <select class="form-select" id="id_tipo" name="id_tipo">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tiposConsulta as $tipo): ?>
                            <option value="<?= (int)$tipo['id_tipo'] ?>" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === (int)$tipo['id_tipo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-search"></i>
                        <span>Filtrar</span>
                    </button>
                    <a href="/reportes/visualizar?vista=<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1" title="Limpiar Filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Selector de Modos de Vista (Pestañas) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div class="btn-group shadow-sm" role="group" aria-label="Modos de Vista">
            <a href="<?= htmlspecialchars($urlVistaSocios, ENT_QUOTES, 'UTF-8') ?>" 
               class="btn <?= $vista === 'socios' ? 'btn-primary fw-semibold' : 'btn-outline-primary bg-white' ?>">
                <i class="bi bi-people-fill me-1"></i> Por Socios (Usuarios Únicos)
                <span class="badge <?= $vista === 'socios' ? 'bg-white text-primary' : 'bg-primary text-white' ?> ms-2 rounded-pill">
                    <?= number_format($totalSocios) ?>
                </span>
            </a>
            <a href="<?= htmlspecialchars($urlVistaAcciones, ENT_QUOTES, 'UTF-8') ?>" 
               class="btn <?= $vista === 'acciones' ? 'btn-primary fw-semibold' : 'btn-outline-primary bg-white' ?>">
                <i class="bi bi-list-check me-1"></i> Registro de Acciones (Detallado)
                <span class="badge <?= $vista === 'acciones' ? 'bg-white text-primary' : 'bg-primary text-white' ?> ms-2 rounded-pill">
                    <?= number_format($totalConsultas) ?>
                </span>
            </a>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                <i class="bi bi-person-check-fill me-1"></i> <?= number_format($totalSocios) ?> Usuarios Únicos
            </span>
            <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-3 py-2 rounded-pill small">
                <i class="bi bi-chat-dots-fill me-1"></i> <?= number_format($totalConsultas) ?> Consultas Totales
            </span>
        </div>
    </div>

    <!-- Contenido según la Vista Activa -->
    <div class="card border-0 shadow-sm mb-4" id="tabla-reportes">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h5 class="card-title mb-0 fw-semibold text-dark">
                <?php if ($vista === 'socios'): ?>
                    <i class="bi bi-people-fill me-2 text-primary"></i>Usuarios Únicos del Chatbot
                <?php else: ?>
                    <i class="bi bi-table me-2 text-primary"></i>Historial Detallado de Acciones
                <?php endif; ?>
            </h5>
            <span class="badge bg-light text-dark border px-3 py-2">
                <?php if ($vista === 'socios'): ?>
                    Mostrando usuarios <?= $desde ?> - <?= $hasta ?> de <?= number_format($totalRegistros) ?>
                <?php else: ?>
                    Mostrando acciones <?= $desde ?> - <?= $hasta ?> de <?= number_format($totalRegistros) ?>
                <?php endif; ?>
            </span>
        </div>

        <div class="card-body p-0">
            <?php if ($vista === 'socios'): ?>
                <!-- VISTA 1: AGRUPADO POR SOCIO / USUARIO ÚNICO (PRINCIPAL) -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center" style="width: 70px;">#</th>
                                <th scope="col" class="py-3" style="width: 140px;">Cód. Socio</th>
                                <th scope="col" class="py-3">Nombre del Socio / Solicitante</th>
                                <th scope="col" class="py-3" style="width: 170px;">Teléfono WhatsApp</th>
                                <th scope="col" class="py-3 text-center" style="width: 160px;">Total Consultas</th>
                                <th scope="col" class="py-3" style="width: 180px;">Última Actividad</th>
                                <th scope="col" class="py-3 text-end px-4" style="width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($socios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-people display-4 d-block mb-2 text-secondary opacity-50"></i>
                                        <h6 class="fw-semibold mb-1">No se encontraron usuarios</h6>
                                        <p class="small text-muted mb-0">No existen usuarios que coincidan con los criterios de filtrado aplicados.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($socios as $idx => $s): ?>
                                    <?php 
                                    $numUsuario = $desde + $idx;
                                    $sKey = $s['socio_key'];
                                    $misAcciones = isset($accionesPorSocio[$sKey]) ? $accionesPorSocio[$sKey] : [];
                                    $collapseId = 'collapse-socio-' . $idx;
                                    ?>
                                    <tr class="align-middle">
                                        <!-- Enumeración correlativa de usuarios (NO por ID de BD) -->
                                        <td class="px-4 py-3 text-center fw-bold text-primary">
                                            #<?= $numUsuario ?>
                                        </td>

                                        <!-- Cód. Socio cliqueable para abrir desglose -->
                                        <td class="py-3">
                                            <a class="badge bg-light text-dark border font-monospace text-decoration-none px-2 py-1 fs-6 d-inline-flex align-items-center gap-1"
                                               role="button"
                                               data-bs-toggle="collapse" 
                                               href="#<?= $collapseId ?>" 
                                               aria-expanded="false" 
                                               aria-controls="<?= $collapseId ?>"
                                               title="Clic para ver acciones de este socio">
                                                <i class="bi bi-person-badge text-primary"></i>
                                                <?= !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C' ?>
                                            </a>
                                        </td>

                                        <!-- Nombre del Socio / Solicitante -->
                                        <td class="py-3 fw-semibold text-dark">
                                            <a class="text-dark text-decoration-none"
                                               role="button"
                                               data-bs-toggle="collapse" 
                                               href="#<?= $collapseId ?>" 
                                               aria-expanded="false" 
                                               aria-controls="<?= $collapseId ?>">
                                                <?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </td>

                                        <!-- Teléfono WhatsApp -->
                                        <td class="py-3">
                                            <?php if (!empty($s['telefono'])): ?>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($s['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">No registrado</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Total de Consultas Realizadas -->
                                        <td class="py-3 text-center">
                                            <span class="badge bg-primary rounded-pill px-3 py-1">
                                                <i class="bi bi-chat-left-text-fill me-1"></i>
                                                <?= (int)$s['total_consultas'] ?> <?= (int)$s['total_consultas'] === 1 ? 'consulta' : 'consultas' ?>
                                            </span>
                                        </td>

                                        <!-- Última Actividad (Fecha y Hora) -->
                                        <td class="py-3 text-secondary small">
                                            <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($s['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($s['ultima_hora'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>

                                        <!-- Botón para Desplegar Acciones -->
                                        <td class="py-3 text-end px-4">
                                            <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm"
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#<?= $collapseId ?>" 
                                                    aria-expanded="false" 
                                                    aria-controls="<?= $collapseId ?>">
                                                <i class="bi bi-eye"></i>
                                                <span>Ver acciones (<?= (int)$s['total_consultas'] ?>)</span>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Fila Colapsable de Acciones del Usuario -->
                                    <tr class="p-0 border-0">
                                        <td colspan="7" class="p-0 border-0">
                                            <div class="collapse" id="<?= $collapseId ?>">
                                                <div class="p-3 bg-light border-top border-bottom">
                                                    <div class="card border shadow-sm">
                                                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                                            <div class="fw-semibold text-dark small">
                                                                <i class="bi bi-clock-history me-1 text-primary"></i>
                                                                Detalle de consultas del socio <strong><?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Titular', ENT_QUOTES, 'UTF-8') ?></strong>
                                                                (Cód. <?= !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C' ?>)
                                                            </div>
                                                            <span class="badge bg-secondary rounded-pill small">
                                                                <?= count($misAcciones) ?> registros
                                                            </span>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-striped mb-0 align-middle">
                                                                    <thead class="table-light text-muted small">
                                                                        <tr>
                                                                            <th class="px-3 py-2" style="width: 50px;">#</th>
                                                                            <th class="py-2" style="width: 170px;">Teléfono</th>
                                                                            <th class="py-2">Tipo de Consulta</th>
                                                                            <th class="py-2" style="width: 180px;">Fecha y Hora</th>
                                                                            <th class="py-2 text-center" style="width: 160px;">Atendido por</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if (empty($misAcciones)): ?>
                                                                            <tr>
                                                                                <td colspan="5" class="text-center py-3 text-muted small">
                                                                                    No hay consultas adicionales para este socio en el filtro actual.
                                                                                </td>
                                                                            </tr>
                                                                        <?php else: ?>
                                                                            <?php foreach ($misAcciones as $accIdx => $acc): ?>
                                                                                <?php $estiloAcc = $getTipoStyle($acc['id_tipo'] ?? 0, $acc['tipo'] ?? ''); ?>
                                                                                <tr>
                                                                                    <td class="px-3 py-2 text-muted small"><?= $accIdx + 1 ?></td>
                                                                                    <td class="py-2">
                                                                                        <?php if (!empty($acc['telefono'])): ?>
                                                                                            <span class="badge bg-light text-dark border font-monospace small">
                                                                                                <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($acc['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                                                                            </span>
                                                                                        <?php else: ?>
                                                                                            <span class="text-muted small">-</span>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                    <td class="py-2">
                                                                                        <span class="badge bg-<?= $estiloAcc['color'] ?>-subtle text-<?= $estiloAcc['color'] ?> border border-<?= $estiloAcc['color'] ?>-subtle px-2 py-1">
                                                                                            <i class="bi <?= $estiloAcc['icon'] ?> me-1"></i><?= htmlspecialchars($acc['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="py-2 small text-secondary">
                                                                                        <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($acc['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                                                                        <div class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($acc['hora_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                                                                    </td>
                                                                                    <td class="py-2 text-center">
                                                                                        <?php if (!empty($acc['username'])): ?>
                                                                                            <span class="badge bg-light text-dark border small">
                                                                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($acc['username'], ENT_QUOTES, 'UTF-8') ?>
                                                                                            </span>
                                                                                        <?php else: ?>
                                                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
                                                                                                <i class="bi bi-robot me-1"></i>Chatbot
                                                                                            </span>
                                                                                        <?php endif; ?>
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

            <?php else: ?>
                <!-- VISTA 2: REGISTRO DETALLADO DE ACCIONES (SIN COLUMNA DE UBICACIÓN) -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="px-4 py-3" style="width: 80px;">ID</th>
                                <th scope="col" class="py-3" style="width: 130px;">Cód. Socio</th>
                                <th scope="col" class="py-3">Nombres / Solicitante</th>
                                <th scope="col" class="py-3" style="width: 160px;">Teléfono WhatsApp</th>
                                <th scope="col" class="py-3">Tipo de Consulta</th>
                                <th scope="col" class="py-3" style="width: 180px;">Fecha y Hora</th>
                                <th scope="col" class="py-3 text-center" style="width: 150px;">Atendido por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($consultas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        <h6 class="fw-semibold mb-1">No se encontraron consultas</h6>
                                        <p class="small text-muted mb-0">No existen registros que coincidan con los criterios de búsqueda aplicados.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($consultas as $row): ?>
                                    <?php $estilo = $getTipoStyle($row['id_tipo'] ?? 0, $row['tipo'] ?? ''); ?>
                                    <tr>
                                        <td class="px-4 py-3 fw-bold text-muted">#<?= (int)$row['id_consulta'] ?></td>
                                        <td class="py-3">
                                            <?php if (!empty($row['codigo_socio'])): ?>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border font-monospace">S/C</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 fw-semibold text-dark">
                                            <?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3">
                                            <?php if (!empty($row['telefono'])): ?>
                                                <span class="badge bg-light text-dark border font-monospace">
                                                    <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-<?= $estilo['color'] ?>-subtle text-<?= $estilo['color'] ?> border border-<?= $estilo['color'] ?>-subtle px-2 py-1">
                                                <i class="bi <?= $estilo['icon'] ?> me-1"></i><?= htmlspecialchars($row['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 text-secondary small">
                                            <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td class="py-3 text-center">
                                            <?php if (!empty($row['username'])): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                    <i class="bi bi-robot me-1"></i>Chatbot
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Paginación y Resumen -->
            <?php if ($totalPaginas > 1 || $totalRegistros > 0): ?>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top bg-light gap-3">
                    <div class="small text-muted">
                        <?php if ($vista === 'socios'): ?>
                            Mostrando usuarios del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de un total de <strong><?= number_format($totalRegistros) ?> usuarios únicos</strong>
                        <?php else: ?>
                            Mostrando acciones del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de un total de <strong><?= number_format($totalRegistros) ?> acciones registradas</strong>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Botón Anterior -->
                                <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina - 1) ?>#tabla-reportes" <?= ($pagina <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <!-- Números de Página -->
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

                                <!-- Botón Siguiente -->
                                <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina + 1) ?>#tabla-reportes" <?= ($pagina >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        <i class="bi bi-chevron-right"></i>
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
