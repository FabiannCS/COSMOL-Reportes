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

<div class="mb-4 text-center">
    <h2 class="h4 text-wrap text-break mb-0">Trabajos pendientes - <?= htmlspecialchars($especialidad) ?></h2>
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
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach ($trabajos as $t): ?>
                    <?php
                        $tipo = 'reconexion';
                        include __DIR__ . '/partials/trabajo_card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>