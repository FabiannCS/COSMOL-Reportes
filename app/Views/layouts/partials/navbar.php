<?php
$usuarioNombre = isset($_SESSION['usuario']['nombre']) ? $_SESSION['usuario']['nombre'] : 'Usuario';
$usuarioRol    = isset($_SESSION['usuario']['rol'])    ? $_SESSION['usuario']['rol']    : '';
?>
<header class="app-header">
    <div class="d-flex align-items-center me-auto">
        <!-- Botón Toggle de Sidebar en móvil -->
        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand" href="/dashboard">
            <i class="bi bi-water"></i>
            <span>COSMOL <small class="text-muted fs-6 fw-normal d-none d-sm-inline">| Reportes</small></span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Información del usuario logueado -->
        <div class="d-none d-md-flex align-items-center text-end">
            <div>
                <div class="fw-semibold text-dark small"><?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($usuarioRol)): ?>
                    <span class="badge bg-light text-secondary border small"><?= htmlspecialchars($usuarioRol, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón de Cerrar Sesión -->
        <a href="/logout" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Salir</span>
        </a>
    </div>
</header>
