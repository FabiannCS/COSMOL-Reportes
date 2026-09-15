<?php
/**
 * @var string $title
 * @var string|null $error
 */
$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);

$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="row justify-content-center mt-3">
    <div class="col-12 col-md-10 col-lg-8">
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error || $sesionError): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error ? $error : $sesionError, ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 text-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-exclamation me-2 text-warning"></i> Estado de Cuenta Operador
                </h5>
            </div>
            
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 72px; height: 72px;">
                        <i class="bi bi-person-gear display-5"></i>
                    </span>
                </div>

                <h4 class="fw-bold text-dark mb-2">Sin Especialidad Asignada</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 540px;">
                    Tu cuenta tiene asignado el rol de <strong>Operador</strong>, pero aún no tiene una especialidad operativa registrada. 
                    Debes solicitar al <strong>Administrador del Sistema</strong> que configure tu especialidad para habilitar tu bandeja de trabajos.
                </p>

                <div class="card bg-light border-0 text-start p-3 mb-4 mx-auto" style="max-width: 540px;">
                    <div class="fw-bold text-secondary mb-2 small text-uppercase">
                        <i class="bi bi-info-circle me-1"></i> Especialidades disponibles en COSMOL:
                    </div>
                    <ul class="list-unstyled mb-0 small text-dark">
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-check2-circle text-primary me-2"></i>
                            <span><strong>Reconexión:</strong> Gestión y conclusión de reconexiones de servicio.</span>
                        </li>
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-check2-circle text-primary me-2"></i>
                            <span><strong>Maestro de alcantarillado:</strong> Atención a reclamos de alcantarillado.</span>
                        </li>
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-check2-circle text-primary me-2"></i>
                            <span><strong>Agua Potable:</strong> Atención a reclamos de la red de agua potable.</span>
                        </li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="/perfil" class="btn btn-outline-primary px-3">
                        <i class="bi bi-person-circle me-1"></i> Ver Mi Perfil
                    </a>
                    <a href="/logout" class="btn btn-outline-danger px-3">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
