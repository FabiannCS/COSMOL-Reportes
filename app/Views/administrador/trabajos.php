<?php
$reconexiones           = isset($reconexiones) ? $reconexiones : [];
$reclamos               = isset($reclamos) ? $reclamos : [];
$reconexionesPaginadas  = isset($reconexionesPaginadas) ? $reconexionesPaginadas : [];
$reclamosPaginados      = isset($reclamosPaginados) ? $reclamosPaginados : [];
$reclamosAguaPotable    = isset($reclamosAguaPotable) ? $reclamosAguaPotable : [];
$reclamosAlcantarillado = isset($reclamosAlcantarillado) ? $reclamosAlcantarillado : [];
$errores                = isset($errores) ? $errores : [];
$pRec                   = isset($pRec) ? (int)$pRec : 1;
$totalPaginasRec        = isset($totalPaginasRec) ? (int)$totalPaginasRec : 1;
$pRecl                  = isset($pRecl) ? (int)$pRecl : 1;
$totalPaginasRecl       = isset($totalPaginasRecl) ? (int)$totalPaginasRecl : 1;

$totalPendientes = count($reconexiones) + count($reclamos);
?>

<div class="container-fluid p-0">
    <!-- Encabezado Responsive -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                Supervisión de Trabajos
            </h1>
        </div>
        <a href="/administrador/trabajos" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center justify-content-center gap-1 shadow-sm w-100 w-sm-auto">
            <i class="bi bi-arrow-clockwise"></i>
            <span>Actualizar</span>
        </a>
    </div>

    <!-- Alertas de Error / Conexión -->
    <?php if (!empty($errores)): ?>
        <?php foreach ($errores as $err): ?>
            <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
                <div class="small flex-grow-1"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tarjetas de Resumen (Métricas) Responsive (2x2 en móviles, 4x1 en desktop) -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small fw-bold text-truncate mb-1">Total Pendientes</div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= number_format($totalPendientes) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small fw-bold text-truncate mb-1">Reconexiones</div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= number_format(count($reconexiones)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small fw-bold text-truncate mb-1">
                        <i class="bi bi-droplet-fill text-info me-1"></i>Agua Potable
                    </div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= number_format(count($reclamosAguaPotable)) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body p-2 p-md-3">
                    <div class="text-muted small fw-bold text-truncate mb-1">
                        <i class="bi bi-moisture text-success me-1"></i>Alcantarillado
                    </div>
                    <div class="h4 fw-bold mb-0 text-dark"><?= number_format(count($reclamosAlcantarillado)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla 1: Reconexiones Pendientes -->
    <div class="card border-0 shadow-sm mb-4" id="tabla-reconexiones">
        <div class="card-header bg-white border-bottom py-2 py-sm-3 px-3 px-sm-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2 fs-6 fs-sm-5">
                <i class="bi bi-plug-fill text-warning"></i>
                <span>Reconexiones Pendientes</span>
            </h5>
            <span class="badge bg-warning text-dark rounded-pill px-2 px-sm-3 py-1 py-sm-2 small">
                <?= count($reconexiones) ?> pendientes
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-2 ps-sm-3 text-nowrap">ID</th>
                            <th scope="col">Socio</th>
                            <th scope="col" class="d-none d-md-table-cell">Descripción</th>
                            <th scope="col">Ubicación</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center text-nowrap">Zona</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center text-nowrap">Ruta</th>
                            <th scope="col" class="text-center pe-2 pe-sm-3 text-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reconexionesPaginadas)): ?>
                            <?php foreach ($reconexionesPaginadas as $rec): ?>
                                <?php
                                $codSocio    = isset($rec['cod_socio']) ? trim($rec['cod_socio']) : '';
                                $nombreSocio = isset($rec['nombre_socio']) ? trim($rec['nombre_socio']) : '';
                                $coords      = isset($rec['coordenadas_gps']) ? preg_replace('/\s+/', '', $rec['coordenadas_gps']) : '';
                                ?>
                                <tr>
                                    <td class="ps-2 ps-sm-3 fw-bold text-muted">
                                        #<?= htmlspecialchars(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark small text-wrap text-break">
                                            <?= htmlspecialchars($nombreSocio ? $nombreSocio : 'Sin nombre', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <?php if ($codSocio): ?>
                                            <div class="text-muted font-monospace" style="font-size: 0.78rem;">
                                                <i class="bi bi-person-badge me-1"></i>Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-secondary small">
                                        <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars(isset($rec['descripcion']) ? $rec['descripcion'] : '', ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars(isset($rec['descripcion']) ? $rec['descripcion'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <div class="fw-semibold text-dark">
                                            <?= htmlspecialchars(isset($rec['ubicacion']) ? $rec['ubicacion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                            <span class="text-muted d-lg-none">(Z:<?= htmlspecialchars(isset($rec['zona']) ? $rec['zona'] : '-', ENT_QUOTES, 'UTF-8') ?>, R:<?= htmlspecialchars(isset($rec['ruta']) ? $rec['ruta'] : '-', ENT_QUOTES, 'UTF-8') ?>)</span>
                                            <?php if (!empty($coords)): ?>
                                                <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" 
                                                   target="_blank" 
                                                   class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none ms-1" 
                                                   title="Ver mapa en nueva pestaña">
                                                    <i class="bi bi-geo-alt-fill"></i> GPS
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($rec['direccion_predio'])): ?>
                                            <div class="text-muted text-truncate d-none d-sm-block mt-1" style="max-width: 160px;" title="<?= htmlspecialchars($rec['direccion_predio'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($rec['direccion_predio'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell small text-muted text-center">
                                        <?= htmlspecialchars(isset($rec['zona']) ? $rec['zona'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell small text-muted text-center">
                                        <?= htmlspecialchars(isset($rec['ruta']) ? $rec['ruta'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-center pe-2 pe-sm-3">
                                        <div class="d-inline-flex gap-1">
                                            <a href="/administrador/trabajos/detalle?tipo=reconexion&id=<?= urlencode(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '') ?>" 
                                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" 
                                               title="Ver Ficha Completa">
                                                <i class="bi bi-eye"></i>
                                                <span class="d-none d-sm-inline">Detalle</span>
                                            </a>
                                            <?php if (!empty($coords)): ?>
                                                <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center" 
                                                   title="Abrir ubicación en Google Maps">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                    No hay reconexiones pendientes en este momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación Reconexiones -->
            <?php if ($totalPaginasRec > 1): ?>
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 p-3 border-top bg-light">
                    <div class="small text-muted">
                        Página <strong><?= $pRec ?></strong> de <strong><?= $totalPaginasRec ?></strong> (Total: <?= count($reconexiones) ?>)
                    </div>
                    <nav aria-label="Paginación Reconexiones">
                        <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                            <li class="page-item <?= ($pRec <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec - 1 ?>&precl=<?= $pRecl ?>#tabla-reconexiones" <?= ($pRec <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginasRec; $i++): ?>
                                <li class="page-item <?= ($i === $pRec) ? 'active' : '' ?>">
                                    <a class="page-link" href="?prec=<?= $i ?>&precl=<?= $pRecl ?>#tabla-reconexiones"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pRec >= $totalPaginasRec) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec + 1 ?>&precl=<?= $pRecl ?>#tabla-reconexiones" <?= ($pRec >= $totalPaginasRec) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla 2: Reclamos Pendientes -->
    <div class="card border-0 shadow-sm" id="tabla-reclamos">
        <div class="card-header bg-white border-bottom py-2 py-sm-3 px-3 px-sm-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2 fs-6 fs-sm-5">
                <i class="bi bi-exclamation-octagon-fill text-info"></i>
                <span>Reclamos Pendientes</span>
            </h5>
            <span class="badge bg-info text-dark rounded-pill px-2 px-sm-3 py-1 py-sm-2 small">
                <?= count($reclamos) ?> pendientes
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-2 ps-sm-3 text-nowrap">ID</th>
                            <th scope="col" class="d-none d-sm-table-cell text-center text-nowrap">Tipo</th>
                            <th scope="col">Socio</th>
                            <th scope="col" class="d-none d-md-table-cell">Descripción</th>
                            <th scope="col">Ubicación</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center text-nowrap">Zona</th>
                            <th scope="col" class="d-none d-lg-table-cell text-center text-nowrap">Ruta</th>
                            <th scope="col" class="text-center pe-2 pe-sm-3 text-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reclamosPaginados)): ?>
                            <?php foreach ($reclamosPaginados as $recl): ?>
                                <?php
                                $tipoReclamo = 'Reclamo';
                                $tipoBadge   = 'secondary';
                                if (isset($recl['id_tipo_reclamo'])) {
                                    if ((int)$recl['id_tipo_reclamo'] === 2) {
                                        $tipoReclamo = 'Agua Potable';
                                        $tipoBadge   = 'info';
                                    } elseif ((int)$recl['id_tipo_reclamo'] === 3) {
                                        $tipoReclamo = 'Alcantarillado';
                                        $tipoBadge   = 'success';
                                    }
                                }
                                $codSocio    = isset($recl['cod_socio']) ? trim($recl['cod_socio']) : '';
                                $nombreSocio = isset($recl['nombre_socio']) ? trim($recl['nombre_socio']) : '';
                                $coords      = isset($recl['coordenadas_gps']) ? preg_replace('/\s+/', '', $recl['coordenadas_gps']) : '';
                                ?>
                                <tr>
                                    <td class="ps-2 ps-sm-3 fw-bold text-muted">
                                        #<?= htmlspecialchars(isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="d-none d-sm-table-cell text-center">
                                        <span class="badge bg-<?= $tipoBadge ?>-subtle text-<?= $tipoBadge ?>-emphasis border border-<?= $tipoBadge ?>-subtle px-2 py-1 small">
                                            <?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-sm-none mb-1">
                                            <span class="badge bg-<?= $tipoBadge ?>-subtle text-<?= $tipoBadge ?>-emphasis border border-<?= $tipoBadge ?>-subtle px-1 py-0" style="font-size: 0.72rem;">
                                                <?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-dark small text-wrap text-break">
                                            <?= htmlspecialchars($nombreSocio ? $nombreSocio : 'Sin nombre', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <?php if ($codSocio): ?>
                                            <div class="text-muted font-monospace" style="font-size: 0.78rem;">
                                                <i class="bi bi-person-badge me-1"></i>Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-secondary small">
                                        <div class="text-truncate" style="max-width: 140px;" title="<?= htmlspecialchars(isset($recl['descripcion']) ? $recl['descripcion'] : '', ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars(isset($recl['descripcion']) ? $recl['descripcion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <div class="fw-semibold text-dark">
                                            <?= htmlspecialchars(isset($recl['ubicacion']) ? $recl['ubicacion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                            <span class="text-muted d-lg-none">(Z:<?= htmlspecialchars(isset($recl['zona']) ? $recl['zona'] : '-', ENT_QUOTES, 'UTF-8') ?>, R:<?= htmlspecialchars(isset($recl['ruta']) ? $recl['ruta'] : '-', ENT_QUOTES, 'UTF-8') ?>)</span>
                                            <?php if (!empty($coords)): ?>
                                                <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" 
                                                   target="_blank" 
                                                   class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none ms-1" 
                                                   title="Ver mapa en nueva pestaña">
                                                    <i class="bi bi-geo-alt-fill"></i> GPS
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($recl['direccion_predio'])): ?>
                                            <div class="text-muted text-truncate d-none d-sm-block mt-1" style="max-width: 160px;" title="<?= htmlspecialchars($recl['direccion_predio'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($recl['direccion_predio'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell small text-muted text-center">
                                        <?= htmlspecialchars(isset($recl['zona']) ? $recl['zona'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell small text-muted text-center">
                                        <?= htmlspecialchars(isset($recl['ruta']) ? $recl['ruta'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-center pe-2 pe-sm-3">
                                        <div class="d-inline-flex gap-1">
                                            <a href="/administrador/trabajos/detalle?tipo=reclamo&id=<?= urlencode(isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '') ?>" 
                                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" 
                                               title="Ver Ficha Completa">
                                                <i class="bi bi-eye"></i>
                                                <span class="d-none d-sm-inline">Detalle</span>
                                            </a>
                                            <?php if (!empty($coords)): ?>
                                                <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center" 
                                                   title="Abrir ubicación en Google Maps">
                                                    <i class="bi bi-geo-alt-fill"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                    No hay reclamos pendientes en este momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación Reclamos -->
            <?php if ($totalPaginasRecl > 1): ?>
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 p-3 border-top bg-light">
                    <div class="small text-muted">
                        Página <strong><?= $pRecl ?></strong> de <strong><?= $totalPaginasRecl ?></strong> (Total: <?= count($reclamos) ?>)
                    </div>
                    <nav aria-label="Paginación Reclamos">
                        <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                            <li class="page-item <?= ($pRecl <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $pRecl - 1 ?>#tabla-reclamos" <?= ($pRecl <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginasRecl; $i++): ?>
                                <li class="page-item <?= ($i === $pRecl) ? 'active' : '' ?>">
                                    <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $i ?>#tabla-reclamos"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pRecl >= $totalPaginasRecl) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $pRecl + 1 ?>#tabla-reclamos" <?= ($pRecl >= $totalPaginasRecl) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fix for BFCache and table responsiveness bug on iOS/Safari -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    window.addEventListener("pageshow", function(event) {
        // If the page is restored from the back-forward cache (BFCache)
        if (event.persisted) {
            // Force a reflow on table-responsive containers to fix layout breaking on back navigation
            var tables = document.querySelectorAll('.table-responsive');
            tables.forEach(function(container) {
                var currentDisplay = container.style.display;
                container.style.display = 'none';
                container.offsetHeight; // trigger reflow
                container.style.display = currentDisplay || '';
            });
        }
    });
});
</script>
