<?php
/**
 * Vista: Visualización y Filtrado de Consultas y Actividad de la App Móvil
 * 
 * @var array  $consultas      Lista de consultas obtenidas
 * @var array  $tiposConsulta  Catálogo de tipos de consulta para el filtro de App
 * @var array  $totalesPorTipo Conteo de consultas agrupadas por cada tipo
 * @var array  $filtros        Filtros aplicados actualmente (fecha_inicio, fecha_fin, id_tipo, buscar)
 * @var int    $pagina         Página actual
 * @var int    $totalPaginas   Total de páginas
 * @var int    $totalRegistros Total de registros que coinciden con los filtros
 * @var int    $limit          Límite de registros por página
 */

$totalesPorTipo = isset($totalesPorTipo) ? $totalesPorTipo : [];

$getTipoStyle = function ($idTipo, $nombre) {
    switch ((int)$idTipo) {
        case 1: // Autenticación / Acceso
            return ['color' => 'primary', 'icon' => 'bi-shield-check'];
        case 2: // Consulta de Deuda
            return ['color' => 'warning', 'icon' => 'bi-cash-coin'];
        case 3: // Historial de Consumo
            return ['color' => 'info', 'icon' => 'bi-bar-chart-line-fill'];
        case 9: // Descarga de Factura PDF
            return ['color' => 'success', 'icon' => 'bi-file-earmark-pdf-fill'];
        case 10: // Pago: Multipago
            return ['color' => 'dark', 'icon' => 'bi-credit-card-2-front-fill'];
        case 11: // Pago: Pago al Paso
            return ['color' => 'warning', 'icon' => 'bi-wallet2'];
        case 12: // Pago: Código QR
            return ['color' => 'info', 'icon' => 'bi-qr-code-scan'];
        default:
            return ['color' => 'primary', 'icon' => 'bi-phone'];
    }
};

$queryParams = [];
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

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-phone-fill text-primary me-2"></i>Consultas App Móvil
            </h1>
            <p class="text-muted small mb-0">Auditoría en tiempo real de accesos, histórico de consumo, facturas digitales y pagos desde la aplicación móvil.</p>
        </div>
        <div>
            <?php if (hasPermission('reportes.exportar')): ?>
                <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm d-inline-flex align-items-center gap-1 shadow-sm w-100 w-sm-auto">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Exportar a CSV</span>
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm disabled w-100 w-sm-auto" disabled title="No posee permiso para exportar reportes">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i>
                    <span>Exportar a CSV</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjetas de Métricas por Tipo de Consulta (KPIs clicables para filtrar) -->
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
                $urlFiltroTipo = '/reportes/app-movil' . (!empty($paramsFiltro) ? '?' . http_build_query($paramsFiltro) : '');
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-xl-2">
                    <a href="<?= htmlspecialchars($urlFiltroTipo, ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="<?= $esTipoActivo ? 'Clic para quitar filtro' : 'Filtrar por ' . htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="card border-0 shadow-sm border-start border-<?= $estilo['color'] ?> border-4 h-100 py-3 <?= $esTipoActivo ? 'bg-light border-top border-end border-bottom border-primary-subtle' : 'bg-white' ?>">
                            <div class="card-body text-center p-2">
                                <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.78rem; letter-spacing: 0.4px;">
                                    <i class="bi <?= $estilo['icon'] ?> text-<?= $estilo['color'] ?> me-1"></i>
                                    <span title="<?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="display-6 fw-bold text-dark mb-0">
                                    <?= number_format($totalStat) ?>
                                </div>
                                <?php if ($esTipoActivo): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size: 0.68rem;">
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
                <i class="bi bi-funnel-fill text-secondary me-2"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="/reportes/app-movil" class="row g-3">
                <!-- Filtro: Fecha Inicio -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="fecha_inicio" class="form-label small fw-semibold">Fecha Inicio</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_inicio" name="fecha_inicio"
                           value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Filtro: Fecha Fin -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="fecha_fin" class="form-label small fw-semibold">Fecha Fin</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_fin" name="fecha_fin"
                           value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Filtro: Tipo de Evento -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="id_tipo" class="form-label small fw-semibold">Tipo de Evento</label>
                    <select class="form-select form-select-sm" id="id_tipo" name="id_tipo">
                        <option value="">-- Todos los Eventos --</option>
                        <?php foreach ($tiposConsulta as $tipo): ?>
                            <option value="<?= $tipo['id_tipo'] ?>"
                                <?= (isset($filtros['id_tipo']) && (int)$filtros['id_tipo'] === (int)$tipo['id_tipo']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tipo['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro: Búsqueda rápida -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label for="buscar" class="form-label small fw-semibold">Buscar Socio o Teléfono</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" id="buscar" name="buscar" 
                               placeholder="Cód. socio, nombre, teléfono..."
                               value="<?= htmlspecialchars($filtros['buscar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-primary" title="Buscar">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="/reportes/app-movil" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-funnel me-1"></i>Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Resultados -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table text-primary me-2"></i>Historial de Movimientos de la App
                </h5>
                <small class="text-muted">Mostrando <?= $desde ?> - <?= $hasta ?> de <?= number_format($totalRegistros) ?> eventos registrados.</small>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                <i class="bi bi-phone me-1"></i>Canal: App Móvil
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="px-4 py-3" style="width: 80px;"># ID</th>
                            <th scope="col" class="py-3" style="width: 130px;">Cód. Socio</th>
                            <th scope="col" class="py-3">Nombre del Titular</th>
                            <th scope="col" class="py-3" style="width: 170px;">Teléfono</th>
                            <th scope="col" class="py-3" style="width: 220px;">Acción / Evento</th>
                            <th scope="col" class="py-3" style="width: 170px;">Fecha y Hora</th>
                            <th scope="col" class="py-3 text-center px-4" style="width: 140px;">Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($consultas)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                                        <p class="mb-0 fw-semibold">No se encontraron movimientos registrados para la App Móvil.</p>
                                        <small class="text-muted">Prueba modificando los filtros de fecha o búsqueda.</small>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($consultas as $row): ?>
                                <?php
                                $estilo = $getTipoStyle($row['id_tipo'] ?? 0, $row['tipo'] ?? '');
                                ?>
                                <tr>
                                    <!-- ID -->
                                    <td class="px-4 py-3 text-muted fw-bold small">
                                        #<?= (int)$row['id_consulta'] ?>
                                    </td>

                                    <!-- Código de Socio -->
                                    <td class="py-3">
                                        <span class="badge bg-light text-dark border font-monospace">
                                            <i class="bi bi-person-badge me-1 text-secondary"></i><?= htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <!-- Nombre -->
                                    <td class="py-3 fw-semibold text-dark">
                                        <?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Socio', ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <!-- Teléfono (Limpio, sin palabra WhatsApp) -->
                                    <td class="py-3">
                                        <?php if (!empty($row['telefono'])): ?>
                                            <span class="badge bg-light text-dark border font-monospace small">
                                                <i class="bi bi-telephone-fill text-primary me-1"></i><?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">No registrado</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Tipo de Evento / Acción -->
                                    <td class="py-3">
                                        <span class="badge bg-<?= $estilo['color'] ?>-subtle text-<?= $estilo['color'] ?> border border-<?= $estilo['color'] ?>-subtle px-2 py-1">
                                            <i class="bi <?= $estilo['icon'] ?> me-1"></i><?= htmlspecialchars($row['tipo'] ?? 'Consulta', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>

                                    <!-- Fecha y Hora -->
                                    <td class="py-3 text-secondary small">
                                        <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>

                                    <!-- Origen / Sistema (Temporal para validación) -->
                                    <td class="py-3 text-center px-4">
                                        <span class="badge bg-primary text-white shadow-sm">
                                            <i class="bi bi-phone-fill me-1"></i>App Móvil
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <span class="small text-muted">
                    Página <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong> (Total: <?= number_format($totalRegistros) ?> registros)
                </span>
                <nav aria-label="Navegación de páginas">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Botón Anterior -->
                        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl($pagina - 1), ENT_QUOTES, 'UTF-8') ?>" aria-label="Anterior">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Páginas numeradas -->
                        <?php
                        $rango = 2;
                        $inicio = max(1, $pagina - $rango);
                        $fin    = min($totalPaginas, $pagina + $rango);

                        if ($inicio > 1): ?>
                            <li class="page-item"><a class="page-link" href="<?= htmlspecialchars($pageUrl(1), ENT_QUOTES, 'UTF-8') ?>">1</a></li>
                            <?php if ($inicio > 2): ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                            <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= htmlspecialchars($pageUrl($i), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($fin < $totalPaginas): ?>
                            <?php if ($fin < $totalPaginas - 1): ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="<?= htmlspecialchars($pageUrl($totalPaginas), ENT_QUOTES, 'UTF-8') ?>"><?= $totalPaginas ?></a></li>
                        <?php endif; ?>

                        <!-- Botón Siguiente -->
                        <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl($pagina + 1), ENT_QUOTES, 'UTF-8') ?>" aria-label="Siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
