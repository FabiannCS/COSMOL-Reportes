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
    <h2 class="h4 text-wrap text-break">Mis Trabajos - <?= htmlspecialchars($especialidad) ?></h2>
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
        <h5 class="mb-0">Reconexiones Pendientes</h5>
    </div>
    <div class="card-body p-0">
        <?php if ($trabajos === null): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-cloud-slash display-4 d-block mb-3"></i>
                <p>No se pudo establecer conexión con el servidor de reconexiones.</p>
            </div>
        <?php elseif (empty($trabajos)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <p>No hay reconexiones pendientes en este momento.</p>
            </div>
        <?php else: ?>
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center d-none d-md-table-cell">Socio</th>
                            <th class="text-center">Ubicación</th>
                            <th class="text-center d-none d-lg-table-cell">Descripción</th>
                            <th class="text-center d-none d-xl-table-cell">Fecha registro</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center pe-3 d-none d-sm-table-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajos as $t): ?>
                            <tr style="cursor: pointer;" onclick="window.location='/operador/detalle?id=<?= urlencode($t['id_reconexion'] ?? '') ?>'">
                                <td class="ps-3 fw-bold">#<?= htmlspecialchars($t['id_reconexion'] ?? 'N/A') ?></td>
                                <td class="d-none d-md-table-cell">
                                    <strong><?= htmlspecialchars($t['cod_socio'] ?? 'N/A') ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars(trim($t['nombre_socio'] ?? '')) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($t['coordenadas_gps'])): ?>
                                        <a href="https://maps.google.com/?q=<?= urlencode(str_replace(' ', '', $t['coordenadas_gps'])) ?>" target="_blank" class="text-decoration-none text-primary fw-bold" onclick="event.stopPropagation();">
                                            <i class="bi bi-geo-alt-fill text-danger"></i> 
                                    <?php endif; ?>
                                    
                                    <?= htmlspecialchars($t['ubicacion'] ?? 'N/A') ?>
                                    (Z: <?= htmlspecialchars($t['zona'] ?? '-') ?>, R: <?= htmlspecialchars($t['ruta'] ?? '-') ?>)
                                    
                                    <?php if (!empty($t['coordenadas_gps'])): ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="small text-muted text-break lh-sm mt-1">
                                        <?= htmlspecialchars($t['direccion_predio'] ?? '') ?>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <?= htmlspecialchars($t['glosa'] ?? 'N/A') ?>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <?php
                                    $fechaStr = $t['fecha_registro'] ?? '';
                                    if ($fechaStr) {
                                        $fecha = date_create($fechaStr);
                                        echo $fecha ? date_format($fecha, 'd/m/Y H:i') : htmlspecialchars($fechaStr);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <?= htmlspecialchars($t['estado'] ?? 'PENDIENTE') ?>
                                    </span>
                                </td>
                                <td class="text-center pe-3 d-none d-sm-table-cell">
                                    <a href="/operador/detalle?id=<?= urlencode($t['id_reconexion'] ?? '') ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-tools"></i> Reconectar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        <?php endif; ?>
    </div>
</div>