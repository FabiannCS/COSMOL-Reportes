<?php
/**
 * Vista de Error 404 — Página no encontrada
 *
 * @var string|null $title
 */

$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : null;
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : null;
$requestedUri  = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
$homeUrl       = ($rolActual === 'Operador') ? '/operador/trabajos' : '/dashboard';
?>

<div class="row justify-content-center mt-3">
    <div class="col-12 col-md-10 col-lg-8">
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 text-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-question-diamond-fill me-2 text-primary"></i> Página No Encontrada
                </h5>
            </div>
            
            <div class="card-body p-4 text-center">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 72px; height: 72px;">
                        <i class="bi bi-compass display-5"></i>
                    </span>
                </div>

                <h4 class="fw-bold text-dark mb-2">Error 404 — Ruta No Disponible</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 560px;">
                    La página solicitada <code><?= htmlspecialchars($requestedUri, ENT_QUOTES, 'UTF-8') ?></code> no existe, ha sido movida o no se encuentra disponible.
                </p>

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
