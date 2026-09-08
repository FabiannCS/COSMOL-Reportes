<?php
$reconexiones = isset($reconexiones) ? $reconexiones : [];
$reclamos     = isset($reclamos) ? $reclamos : [];
$errores      = isset($errores) ? $errores : [];

$totalConcluidos = count($reconexiones) + count($reclamos);

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                Historial de Trabajos Concluidos
            </h1>
            <p class="text-muted mb-0">Visualiza los trabajos operativos que has finalizado.</p>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
            <div class="small flex-grow-1"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($sesionError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0"></i>
            <div class="small flex-grow-1"><?= htmlspecialchars($sesionError, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjeta resumen -->
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-success text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Total Concluidos por ti
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format($totalConcluidos) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Historial -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h5 class="card-title mb-0 fw-bold d-flex align-items-center text-dark">
                <i class="bi bi-journal-check text-success me-2 fs-4"></i>
                Registro de Actividades
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>ID Trabajo</th>
                            <th>Socio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reconexiones) && empty($reclamos)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1 text-secondary opacity-50"></i></div>
                                    Aún no has concluido ningún trabajo.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reconexiones as $rec): ?>
                                <tr>
                                    <td><span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge me-1"></i> Reconexión</span></td>
                                    <td class="fw-bold">#<?= htmlspecialchars($rec['id_reconexion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($rec['nombre_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small">Cod: <?= htmlspecialchars($rec['cod_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">
                                            <?= htmlspecialchars(isset($rec['estado']) ? $rec['estado'] : 'Concluida', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/administrador/trabajos/detalle?tipo=reconexion&id=<?= urlencode($rec['id_reconexion'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ($reclamos as $reclamo): ?>
                                <tr>
                                    <td>
                                        <?php if (isset($reclamo['id_tipo_reclamo']) && $reclamo['id_tipo_reclamo'] == 2): ?>
                                            <span class="badge bg-info text-dark"><i class="bi bi-droplet me-1"></i> Agua Potable</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white"><i class="bi bi-water me-1"></i> Alcantarillado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold">#<?= htmlspecialchars($reclamo['id_reclamo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($reclamo['nombre_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small">Cod: <?= htmlspecialchars($reclamo['cod_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">
                                            <?= htmlspecialchars(isset($reclamo['estado']) ? $reclamo['estado'] : 'Solucionado', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/administrador/trabajos/detalle?tipo=reclamo&id=<?= urlencode($reclamo['id_reclamo'] ?? '') ?>" class="btn btn-sm btn-outline-primary">
                                            Ver Detalles
                                        </a>
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
