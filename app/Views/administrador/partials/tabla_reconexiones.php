<?php
/**
 * Vista Parcial: Tabla de Reconexiones
 * @var array $reconexiones
 * @var array $reconexionesPaginadas
 * @var int $pRec
 * @var int $totalPaginasRec
 * @var int $pRecl
 */
?>
<div class="card border-0 shadow-sm mb-4" id="tabla-reconexiones">
    <div class="card-header bg-white border-bottom py-2 py-sm-3 px-3 px-sm-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2 fs-6 fs-sm-5">
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
                            $estCalc     = isset($rec['estado_calculado']) ? $rec['estado_calculado'] : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($rec) : 'PENDIENTE');
                            ?>
                            <tr>
                                <td class="ps-2 ps-sm-3 fw-bold text-muted">
                                    #<?= htmlspecialchars(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                        <div class="d-sm-none mt-1">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.72rem;">No Concluido</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small text-wrap text-break">
                                        <?= htmlspecialchars($nombreSocio ? $nombreSocio : 'Sin nombre', ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1 d-none d-sm-inline-block">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>No Concluido
                                            </span>
                                        <?php endif; ?>
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
                                        <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                            <a href="/administrador/trabajos/detalle?tipo=reconexion&id=<?= urlencode(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '') ?>" 
                                                class="btn btn-sm btn-outline-warning text-dark d-inline-flex align-items-center gap-1" 
                                                title="Editar / Concluir Reconexión">
                                                <i class="bi bi-pencil-square"></i>
                                                <span class="d-none d-sm-inline">Editar</span>
                                            </a>
                                        <?php else: ?>
                                            <a href="/administrador/trabajos/detalle?tipo=reconexion&id=<?= urlencode(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '') ?>" 
                                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" 
                                                title="Concluir Reconexión">
                                                <i class="bi bi-play-circle"></i>
                                                <span class="d-none d-sm-inline">Concluir</span>
                                            </a>
                                        <?php endif; ?>
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
