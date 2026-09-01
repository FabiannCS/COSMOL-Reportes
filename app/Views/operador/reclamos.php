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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-tools"></i> Mis Reclamos — <?= htmlspecialchars($especialidad) ?></h2>
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
                            <th class="ps-3">ID Reclamo</th>
                            <th>Ubicación / Ruta</th>
                            <th>Descripción</th>
                            <th>Glosa del Cliente</th>
                            <th>Registro</th>
                            <th class="text-center pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajos as $t): ?>
                            <tr>
                                <td class="ps-3 fw-bold">#<?= htmlspecialchars($t['id_reclamo'] ?? 'N/A') ?></td>
                                <td>
                                    <?= htmlspecialchars($t['ubicacion'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted">Zona: <?= htmlspecialchars($t['zona'] ?? '-') ?>, Ruta: <?= htmlspecialchars($t['ruta'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['descripcion'] ?? 'Sin descripción') ?></strong>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($t['glosa'] ?? '') ?>">
                                        <?= htmlspecialchars($t['glosa'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($t['usuario_registro']) && $t['usuario_registro'] == 2): ?>
                                        <span class="badge bg-info text-dark"><i class="bi bi-robot"></i> Bot de WhatsApp</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-person"></i> Presencial/Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="/operador/detalle?id=<?= urlencode($t['id_reclamo'] ?? '') ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detalle
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
