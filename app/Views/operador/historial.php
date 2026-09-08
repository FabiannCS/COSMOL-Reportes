<?php
/**
 * @var string $title
 * @var string $especialidad
 * @var array|null $trabajos
 * @var string|null $error
 */
?>
<div class="mb-4">
    <h2 class="h4 text-wrap text-break mb-0 text-center">Historial de Trabajos - <?= htmlspecialchars($especialidad) ?></h2>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    .task-card { transition: all 0.2s ease-in-out; }
    .task-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<div class="card shadow-sm border-0">
    <div class="card-header bg-secondary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Trabajos Concluidos</h5>
    </div>
    <div class="card-body bg-light p-3 p-md-4">
        <?php if ($trabajos === null): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-cloud-slash display-4 d-block mb-3"></i>
                <p>No se pudo establecer conexión con el servidor.</p>
            </div>
        <?php elseif (empty($trabajos)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                <p>No tienes trabajos en tu historial.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach ($trabajos as $t): ?>
                    <?php 
                        $tipo = ($especialidad === 'Reconexión') ? 'reconexion' : 'reclamo';
                        $is_historial = true;
                        include __DIR__ . '/partials/trabajo_card.php';
                    ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
