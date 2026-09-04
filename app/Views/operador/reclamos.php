<?php
/**
 * @var string $title
 * @var string $especialidad
 * @var array|null $trabajos
 * @var string|null $error
 */

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);

$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="mb-4">
    <h2 class="h4 text-wrap text-break mb-0">Mis Reclamos - <?= htmlspecialchars($especialidad) ?></h2>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error || $sesionError): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error ?: $sesionError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Reclamos Pendientes</h5>
    </div>
    <div class="card-body p-0">
        <?php if ($trabajos === null): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-cloud-slash display-4 d-block mb-3"></i>
                <p>No se pudo establecer conexión con el servidor de reclamos.</p>
            </div>
        <?php elseif (empty($trabajos)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <p>No hay reclamos pendientes para su especialidad en este momento.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 60px;">ID</th>
                            <th class="d-none d-md-table-cell">Socio</th>
                            <th>Ubicación</th>
                            <th class="d-none d-lg-table-cell">Descripción</th>
                            <th class="text-center d-none d-md-table-cell" style="width: 80px;">Foto</th>
                            <th class="d-none d-xl-table-cell" style="width: 150px;">Fecha</th>
                            <th class="text-center" style="width: 110px;">Estado</th>
                            <th class="text-center pe-3 d-none d-sm-table-cell" style="width: 110px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajos as $t): ?>
                            <tr style="cursor: pointer;" onclick="window.location='/operador/detalle?id=<?= urlencode($t['id_reclamo'] ?? '') ?>'">
                                <td class="ps-3 fw-bold align-middle text-nowrap">
                                    #<?= htmlspecialchars($t['id_reclamo'] ?? 'N/A') ?>
                                </td>
                                <td class="d-none d-md-table-cell align-middle">
                                    <?php if (!empty($t['nombre_socio'])): ?>
                                        <div class="fw-semibold text-break small"><?= htmlspecialchars(trim($t['nombre_socio'])) ?></div>
                                        <?php if (!empty($t['cod_socio'])): ?>
                                            <div class="text-muted" style="font-size: 0.85rem;">Cód. <?= htmlspecialchars($t['cod_socio']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="align-middle">
                                    <?php if (!empty($t['coordenadas_gps'])): ?>
                                        <?php
                                            $coords = preg_replace('/\s+/', '', $t['coordenadas_gps']);
                                            $urlVerMapa = "https://www.google.com/maps?q={$coords}";
                                            $urlRuta    = "https://www.google.com/maps/dir/?api=1&destination={$coords}";
                                        ?>
                                        <a href="<?= htmlspecialchars($urlVerMapa) ?>" target="_blank" class="text-decoration-none text-primary fw-bold" onclick="event.stopPropagation();" title="Ver ubicación en Google Maps">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($t['ubicacion'] ?? 'N/A') ?>
                                        </a>
                                        (Z: <?= htmlspecialchars($t['zona'] ?? '-') ?>, R: <?= htmlspecialchars($t['ruta'] ?? '-') ?>)
                                        <a href="<?= htmlspecialchars($urlRuta) ?>" target="_blank" class="badge bg-light text-success border text-decoration-none ms-1" onclick="event.stopPropagation();" title="Trazar ruta (Cómo llegar) en Google Maps">
                                            <i class="bi bi-sign-turn-right-fill"></i> Ruta
                                        </a>
                                    <?php else: ?>
                                        <span class="fw-bold"><?= htmlspecialchars($t['ubicacion'] ?? 'N/A') ?></span>
                                        (Z: <?= htmlspecialchars($t['zona'] ?? '-') ?>, R: <?= htmlspecialchars($t['ruta'] ?? '-') ?>)
                                    <?php endif; ?>
                                    <div class="small text-muted text-break lh-sm mt-1">
                                        <?= htmlspecialchars($t['direccion_predio'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell align-middle">
                                    <div class="fw-semibold text-break"><?= htmlspecialchars($t['descripcion'] ?? 'Sin descripción') ?></div>
                                </td>
                                <td class="text-center d-none d-md-table-cell align-middle">
                                    <?php if (!empty($t['foto'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" title="Tiene fotografía adjunta">
                                            <i class="bi bi-camera-fill me-1"></i>Foto
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-xl-table-cell align-middle small text-muted">
                                    <?php
                                        $fechaStr = $t['fecha_registro'] ?? '';
                                        if ($fechaStr) {
                                            $fecha = date_create($fechaStr);
                                            echo $fecha ? date_format($fecha, 'd/m/Y H:i') : htmlspecialchars($fechaStr);
                                        } else {
                                            echo '—';
                                        }
                                    ?>
                                </td>
                                <td class="text-center align-middle">
                                    <?php
                                        $estado = strtoupper(trim($t['estado'] ?? 'PENDIENTE'));
                                        $badgeClass = 'bg-warning text-dark';
                                        if ($estado === 'CONCLUIDA' || $estado === 'CONCLUIDO') {
                                            $badgeClass = 'bg-success text-white';
                                        } elseif ($estado === 'ANULADO' || $estado === 'CANCELADO') {
                                            $badgeClass = 'bg-danger text-white';
                                        }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($estado) ?></span>
                                </td>
                                <td class="text-center pe-3 d-none d-sm-table-cell align-middle">
                                    <a href="/operador/detalle?id=<?= urlencode($t['id_reclamo'] ?? '') ?>" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">
                                        Detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

