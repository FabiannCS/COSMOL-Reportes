<?php
/**
 * Vista de Error 403 — Acceso Denegado / Permisos Insuficientes
 * Compatible con layout principal 'main' o renderizado independiente
 *
 * @var string $title
 * @var int $statusCode
 * @var string $titulo
 * @var string $mensaje
 */

$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : 'No identificado';
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : 'Sin rol';
$requestedUri  = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
$homeUrl       = ($rolActual === 'Operador') ? '/operador/trabajos' : '/dashboard';
?>

<div class="row justify-content-center mt-3">
    <div class="col-12 col-md-10 col-lg-8">
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 text-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-shield-lock-fill me-2 text-danger"></i> <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
                </h5>
            </div>
            
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle" style="width: 72px; height: 72px;">
                        <i class="bi bi-shield-slash display-5"></i>
                    </span>
                </div>

                <h4 class="fw-bold text-dark mb-2">Permisos Insuficientes (Error 403)</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 560px;">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="card bg-light border-0 text-start p-3 mb-4 mx-auto" style="max-width: 560px;">
                    <div class="fw-bold text-secondary mb-2 small text-uppercase">
                        <i class="bi bi-info-circle me-1"></i> Detalles de la solicitud:
                    </div>
                    <ul class="list-unstyled mb-0 small text-dark">
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-person-badge text-primary me-2"></i>
                            <span><strong>Usuario:</strong> <?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-person-workspace text-primary me-2"></i>
                            <span><strong>Rol asignado:</strong> <?= htmlspecialchars($rolActual, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                        <li class="py-1 d-flex align-items-center">
                            <i class="bi bi-link-45deg text-primary me-2"></i>
                            <span><strong>Ruta solicitada:</strong> <code><?= htmlspecialchars($requestedUri, ENT_QUOTES, 'UTF-8') ?></code></span>
                        </li>
                    </ul>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver atrás
                    </a>
                    <a href="<?= $homeUrl ?>" class="btn btn-primary px-3">
                        <i class="bi bi-house-door me-1"></i> Ir al Panel Principal
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
