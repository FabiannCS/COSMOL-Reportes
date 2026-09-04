<?php
/**
 * Vista: Supervisión Global de Trabajos (Administrador)
 * @var array $reconexiones @var array $reclamosAguaPotable @var array $reclamosAlcantarillado
 * @var array $errores @var array $reclamos
 * Los datos (reconexiones, reclamos, métricas) son inyectados por AdministradorController::trabajos()
 */
?>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1"><i class="bi bi-clipboard-data me-2"></i>Supervisión de Trabajos</h1>
        </div>
        <a href="/administrador/trabajos" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
        </a>
    </div>

    <!-- Errores de API -->
    <?php if (!empty($errores)): ?>
        <?php foreach ($errores as $err): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Tarjetas de Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted mb-1 fw-bold fs-5">Total Pendientes</div>
                    <div class="h3 fw-bold mb-0">
                        <?= count($reconexiones) + count($reclamos) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted mb-1 fw-bold fs-5">Reconexiones</div>
                    <div class="h3 fw-bold mb-0"><?= count($reconexiones) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted mb-1 fw-bold fs-5"><i class="bi bi-droplet-fill text-info"></i> Agua Potable</div>
                    <div class="h3 fw-bold mb-0"><?= count($reclamosAguaPotable) ?></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted mb-1 fw-bold fs-5"><i class="bi bi-moisture text-success"></i> Alcantarillado</div>
                    <div class="h3 fw-bold mb-0"><?= count($reclamosAlcantarillado) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Reconexiones Pendientes -->
    <div class="card border-0 shadow-sm mb-4" id="tabla-reconexiones">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Reconexiones Pendientes
                <span class="badge bg-warning text-dark ms-2"><?= count($reconexiones) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Socio</th>
                            <th>Descripción</th>
                            <th>Ubicación</th>
                            <th>Zona</th>
                            <th>Ruta</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reconexionesPaginadas)): ?>
                            <?php foreach ($reconexionesPaginadas as $rec): ?>
                                <tr>
                                    <td><span class="fw-bold"><?= htmlspecialchars(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '—', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <?php
                                        $codSocio    = isset($rec['cod_socio']) ? $rec['cod_socio'] : '';
                                        $nombreSocio = isset($rec['nombre_socio']) ? $rec['nombre_socio'] : '';
                                        echo htmlspecialchars($codSocio . ($nombreSocio ? ' - ' . $nombreSocio : ''), ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td class="text-truncate" style="max-width:200px;" title="<?= htmlspecialchars(isset($rec['descripcion']) ? $rec['descripcion'] : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(isset($rec['descripcion']) ? $rec['descripcion'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars(isset($rec['ubicacion']) ? $rec['ubicacion'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(isset($rec['zona']) ? $rec['zona'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(isset($rec['ruta']) ? $rec['ruta'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-center">
                                        <a href="/administrador/trabajos/detalle?tipo=reconexion&id=<?= urlencode(isset($rec['id_reconexion']) ? $rec['id_reconexion'] : '') ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                            Detalle
                                        </a>
                                        <?php if (!empty($rec['coordenadas_gps'])): ?>
                                            <a href="https://www.google.com/maps?q=<?= urlencode($rec['coordenadas_gps']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-success" title="Ver ubicación">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                    No hay reconexiones pendientes.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($totalPaginasRec) && $totalPaginasRec > 1): ?>
                <div class="d-flex justify-content-center mt-4 pb-3">
                    <nav aria-label="Paginación Reconexiones">
                        <ul class="pagination mb-0">
                            <li class="page-item <?= ($pRec <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec - 1 ?>&precl=<?= $pRecl ?? 1 ?>#tabla-reconexiones" <?= ($pRec <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginasRec; $i++): ?>
                                <li class="page-item <?= ($i === $pRec) ? 'active' : '' ?>">
                                    <a class="page-link" href="?prec=<?= $i ?>&precl=<?= $pRecl ?? 1 ?>#tabla-reconexiones"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pRec >= $totalPaginasRec) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec + 1 ?>&precl=<?= $pRecl ?? 1 ?>#tabla-reconexiones" <?= ($pRec >= $totalPaginasRec) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla de Reclamos Pendientes -->
    <div class="card border-0 shadow-sm" id="tabla-reclamos">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Reclamos Pendientes
                <span class="badge bg-info ms-2"><?= count($reclamos) ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Socio</th>
                            <th>Descripción</th>
                            <th>Ubicación</th>
                            <th>Zona</th>
                            <th>Ruta</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($reclamosPaginados)): ?>
                            <?php foreach ($reclamosPaginados as $recl): ?>
                                <?php
                                $tipoReclamo = '';
                                $tipoBadge   = 'secondary';
                                if (isset($recl['id_tipo_reclamo'])) {
                                    if ((int)$recl['id_tipo_reclamo'] === 2) {
                                        $tipoReclamo = 'Agua Potable';
                                        $tipoBadge   = 'info';
                                    } elseif ((int)$recl['id_tipo_reclamo'] === 3) {
                                        $tipoReclamo = 'Alcantarillado';
                                        $tipoBadge   = 'success';
                                    } else {
                                        $tipoReclamo = 'Tipo ' . $recl['id_tipo_reclamo'];
                                    }
                                }
                                ?>
                                <tr>
                                    <td><span class="fw-bold"><?= htmlspecialchars(isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '—', ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><span class="badge bg-<?= $tipoBadge ?>"><?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <?php
                                        $codSocio    = isset($recl['cod_socio']) ? $recl['cod_socio'] : '';
                                        $nombreSocio = isset($recl['nombre_socio']) ? $recl['nombre_socio'] : '';
                                        echo htmlspecialchars($codSocio . ($nombreSocio ? ' - ' . $nombreSocio : ''), ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                    <td class="text-truncate" style="max-width:200px;" title="<?= htmlspecialchars(isset($recl['descripcion']) ? $recl['descripcion'] : '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(isset($recl['descripcion']) ? $recl['descripcion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td><?= htmlspecialchars(isset($recl['ubicacion']) ? $recl['ubicacion'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(isset($recl['zona']) ? $recl['zona'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars(isset($recl['ruta']) ? $recl['ruta'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-center">
                                        <a href="/administrador/trabajos/detalle?tipo=reclamo&id=<?= urlencode(isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '') ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                            Detalle
                                        </a>
                                        <?php if (!empty($recl['coordenadas_gps'])): ?>
                                            <a href="https://www.google.com/maps?q=<?= urlencode($recl['coordenadas_gps']) ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-success" title="Ver ubicación">
                                                <i class="bi bi-geo-alt-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                                    No hay reclamos pendientes.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($totalPaginasRecl) && $totalPaginasRecl > 1): ?>
                <div class="d-flex justify-content-center mt-4 pb-3">
                    <nav aria-label="Paginación Reclamos">
                        <ul class="pagination mb-0">
                            <li class="page-item <?= ($pRecl <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec ?? 1 ?>&precl=<?= $pRecl - 1 ?>#tabla-reclamos" <?= ($pRecl <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginasRecl; $i++): ?>
                                <li class="page-item <?= ($i === $pRecl) ? 'active' : '' ?>">
                                    <a class="page-link" href="?prec=<?= $pRec ?? 1 ?>&precl=<?= $i ?>#tabla-reclamos"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($pRecl >= $totalPaginasRecl) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec ?? 1 ?>&precl=<?= $pRecl + 1 ?>#tabla-reclamos" <?= ($pRecl >= $totalPaginasRecl) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
