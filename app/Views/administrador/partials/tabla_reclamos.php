<?php
/**
 * Vista Parcial: Tabla de Reclamos
 * @var array $reclamos
 * @var array $reclamosPaginados
 * @var int $pRecl
 * @var int $totalPaginasRecl
 * @var int $pRec
 */
?>
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
                                            title="Concluir Reclamo">
                                            <i class="bi bi-play-circle"></i>
                                            <span class="d-none d-sm-inline">Concluir</span>
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
