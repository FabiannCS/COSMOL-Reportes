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

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
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
    <div class="row g-3 g-md-4 mb-4">
        <!-- Total Pendientes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-dark text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Total Pendientes
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format($totalPendientes) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reconexiones -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-dark text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Reconexiones Pendientes
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format(count($reconexiones)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agua Potable -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-dark text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Agua Potable Pendiente
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format(count($reclamosAguaPotable)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alcantarillado -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-dark text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Alcantarillados Pendientes
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format(count($reclamosAlcantarillado)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla 1: Reconexiones Pendientes -->
    <?php include __DIR__ . '/partials/tabla_reconexiones.php'; ?>

    <!-- Tabla 2: Reclamos Pendientes -->
    <?php include __DIR__ . '/partials/tabla_reclamos.php'; ?>
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
