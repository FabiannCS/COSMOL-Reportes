<?php
/**
 * Vista: Visualización y Filtrado de Reportes de la App de Socios
 * 
 * @var array  $consultas      Lista de consultas obtenidas
 * @var array  $tiposConsulta  Catálogo de tipos de consulta para el filtro
 * @var array  $totalesPorTipo Conteo asociativo de consultas [id_tipo => total]
 * @var array  $filtros        Filtros aplicados actualmente (fecha_inicio, fecha_fin, id_tipo, buscar)
 * @var int    $pagina         Página actual
 * @var int    $totalPaginas   Total de páginas
 * @var int    $totalRegistros Total de registros que coinciden con los filtros
 * @var int    $limit          Límite de registros por página
 */

$totalesPorTipo = isset($totalesPorTipo) ? $totalesPorTipo : [];

$getTipoStyle = function ($idTipo) {
    switch ((int)$idTipo) {
        case 1: // Autenticación / Acceso
            return ['color' => 'primary', 'icon' => 'bi-shield-lock-fill'];
        case 2: // Consulta de Deuda
            return ['color' => 'warning', 'icon' => 'bi-cash-coin'];
        case 3: // Historial de Facturas
            return ['color' => 'info', 'icon' => 'bi-receipt-cutoff'];
        case 9: // Descarga de Factura PDF
            return ['color' => 'danger', 'icon' => 'bi-file-earmark-pdf-fill'];
        case 10: // Intento de Pago
            return ['color' => 'success', 'icon' => 'bi-credit-card-2-front-fill'];
        default:
            return ['color' => 'secondary', 'icon' => 'bi-phone-fill'];
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

$pageUrl = function ($pageNum) use ($queryParams) {
    $p = array_merge($queryParams, ['p' => $pageNum]);
    return '/reportes/app-socios?' . http_build_query($p);
};

// Generador de URL para tarjetas interactivas de KPI
$kpiCardUrl = function ($idTipoFiltro) use ($queryParams, $filtros) {
    $p = $queryParams;
    if (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === (int)$idTipoFiltro) {
        unset($p['id_tipo']); // Quitar filtro si ya está activo
    } else {
        $p['id_tipo'] = $idTipoFiltro;
    }
    $p['p'] = 1;
    return '/reportes/app-socios' . (!empty($p) ? '?' . http_build_query($p) : '');
};

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                <i class="bi bi-phone text-primary me-2"></i>Reporte de Movimientos — App de Socios
            </h1>
            <p class="text-muted small mb-0">Auditoría en tiempo real de consultas, descargas de facturas PDF y pagos desde la aplicación móvil.</p>
        </div>
        <div>
            <span class="badge bg-primary px-3 py-2 fs-7 shadow-sm">
                <i class="bi bi-check-circle-fill me-1"></i> Canal: App Móvil (id_usuario = 3)
            </span>
        </div>
    </div>

    <!-- Tarjetas de Métricas KPI Resumen -->
    <div class="row g-3 g-md-4 mb-4">
        <!-- 1. Accesos y Logins -->
        <?php $esActivo1 = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 1); ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= htmlspecialchars($kpiCardUrl(1), ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="Filtrar por Accesos y Logins">
                <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-3 <?= $esActivo1 ? 'bg-light border-top border-end border-bottom border-primary-subtle' : 'bg-white' ?>">
                    <div class="card-body text-center p-2">
                        <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                            <i class="bi bi-shield-lock-fill text-primary me-1"></i>
                            <span>Accesos y Logins</span>
                        </div>
                        <div class="display-6 fw-bold text-dark mb-0">
                            <?= number_format($totalesPorTipo[1] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 2. Consultas de Deuda -->
        <?php $esActivo2 = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 2); ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= htmlspecialchars($kpiCardUrl(2), ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="Filtrar por Consultas de Deuda">
                <div class="card border-0 shadow-sm border-start border-warning border-4 h-100 py-3 <?= $esActivo2 ? 'bg-light border-top border-end border-bottom border-warning-subtle' : 'bg-white' ?>">
                    <div class="card-body text-center p-2">
                        <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                            <i class="bi bi-cash-coin text-warning me-1"></i>
                            <span>Consultas de Deuda</span>
                        </div>
                        <div class="display-6 fw-bold text-dark mb-0">
                            <?= number_format($totalesPorTipo[2] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. Historial de Facturas -->
        <?php $esActivo3 = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 3); ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <a href="<?= htmlspecialchars($kpiCardUrl(3), ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none d-block h-100" title="Filtrar por Historial de Facturas">
                <div class="card border-0 shadow-sm border-start border-info border-4 h-100 py-3 <?= $esActivo3 ? 'bg-light border-top border-end border-bottom border-info-subtle' : 'bg-white' ?>">
                    <div class="card-body text-center p-2">
                        <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                            <i class="bi bi-receipt-cutoff text-info me-1"></i>
                            <span>Historial de Facturas</span>
                        </div>
                        <div class="display-6 fw-bold text-dark mb-0">
                            <?= number_format($totalesPorTipo[3] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- 4. Descargas PDF y Pagos -->
        <?php $totalPdfPagos = ($totalesPorTipo[9] ?? 0) + ($totalesPorTipo[10] ?? 0); ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-3 bg-white">
                <div class="card-body text-center p-2">
                    <div class="text-xs fw-bold text-dark text-uppercase mb-2 text-truncate px-1" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                        <i class="bi bi-credit-card-2-front-fill text-success me-1"></i>
                        <span>Descargas PDF y Pagos</span>
                    </div>
                    <div class="display-6 fw-bold text-dark mb-0">
                        <?= number_format($totalPdfPagos) ?>
                    </div>
                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                        PDFs: <?= number_format($totalesPorTipo[9] ?? 0) ?> | Pagos: <?= number_format($totalesPorTipo[10] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Filtros de Búsqueda -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fw-semibold text-dark">
                <i class="bi bi-funnel me-2 text-primary"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body p-3 p-md-4">
            <form action="/reportes/app-socios" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label for="buscar" class="form-label fw-semibold small text-muted">Búsqueda General</label>
                    <input type="text" 
                           class="form-control" 
                           id="buscar" 
                           name="buscar" 
                           placeholder="Cód. Socio, Nombre o Teléfono..."
                           value="<?= htmlspecialchars($filtros['buscar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="fecha_inicio" class="form-label fw-semibold small text-muted">Fecha Desde</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-2">
                    <label for="fecha_fin" class="form-label fw-semibold small text-muted">Fecha Hasta</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-3">
                    <label for="id_tipo" class="form-label fw-semibold small text-muted">Tipo de Movimiento</label>
                    <select class="form-select" id="id_tipo" name="id_tipo">
                        <option value="">-- Todos los Movimientos --</option>
                        <option value="1" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 1) ? 'selected' : '' ?>>Autenticación / Acceso</option>
                        <option value="2" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 2) ? 'selected' : '' ?>>Consulta de Deuda</option>
                        <option value="3" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 3) ? 'selected' : '' ?>>Historial de Facturas</option>
                        <option value="9" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 9) ? 'selected' : '' ?>>Descarga de Factura PDF</option>
                        <option value="10" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 10) ? 'selected' : '' ?>>Intento de Pago</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-search"></i>
                        <span>Filtrar</span>
                    </button>
                    <a href="/reportes/app-socios" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1" title="Limpiar Filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjeta de Resultados / Tabla -->
    <div class="card border-0 shadow-sm mb-4" id="tabla-reportes-app">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-semibold text-dark">
                <i class="bi bi-table me-2 text-primary"></i>Historial de Movimientos de la App
            </h5>
            <span class="badge bg-light text-dark border">
                Total: <?= number_format($totalRegistros) ?> registros
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3 py-3" style="width: 80px;"># ID</th>
                            <th style="width: 120px;">Cód. Socio</th>
                            <th>Nombre Titular</th>
                            <th style="width: 150px;">Teléfono</th>
                            <th>Acción Realizada</th>
                            <th style="width: 160px;">Fecha y Hora</th>
                            <th class="text-center" style="width: 120px;">Canal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($consultas)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-phone fs-1 d-block mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-1 fw-semibold">No se encontraron movimientos registrados para la App de Socios.</p>
                                    <span class="small">Cuando la App despache eventos a la API, aparecerán reflejados aquí en tiempo real.</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($consultas as $row): ?>
                                <?php 
                                $idTipoRow = (int)($row['id_tipo'] ?? 0);
                                $estiloRow = $getTipoStyle($idTipoRow);
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted"><?= (int)$row['id_consulta'] ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            <?= htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?= htmlspecialchars($row['nombres'] ?? 'No disponible', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php if (!empty($row['telefono'])): ?>
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Sin teléfono</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $estiloRow['color'] ?>-subtle text-<?= $estiloRow['color'] ?> border border-<?= $estiloRow['color'] ?>-subtle px-2 py-1">
                                            <i class="bi <?= $estiloRow['icon'] ?> me-1"></i>
                                            <?= htmlspecialchars($row['tipo'] ?? 'Consulta General', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <div><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div><i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary px-2 py-1">
                                            <i class="bi bi-phone me-1"></i>APP_MOVIL
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación y Resumen -->
            <?php if ($totalPaginas > 1 || $totalRegistros > 0): ?>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top bg-light gap-3">
                    <div class="small text-muted">
                        Mostrando registros del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de un total de <strong><?= number_format($totalRegistros) ?></strong>
                    </div>
                    <?php if ($totalPaginas > 1): ?>
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Botón Anterior -->
                                <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina - 1) ?>#tabla-reportes-app" <?= ($pagina <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <!-- Números de Página -->
                                <?php
                                $startPage = max(1, $pagina - 2);
                                $endPage   = min($totalPaginas, $pagina + 2);
                                if ($startPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>#tabla-reportes-app">1</a></li>
                                    <?php if ($startPage > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $pageUrl($i) ?>#tabla-reportes-app"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPaginas): ?>
                                    <?php if ($endPage < $totalPaginas - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                    <li class="page-item"><a class="page-link" href="<?= $pageUrl($totalPaginas) ?>#tabla-reportes-app"><?= $totalPaginas ?></a></li>
                                <?php endif; ?>

                                <!-- Botón Siguiente -->
                                <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($pagina + 1) ?>#tabla-reportes-app" <?= ($pagina >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
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
