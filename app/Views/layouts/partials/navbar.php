<?php
$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : '';
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
$homeUrl       = ($rolActual === 'Operador') ? '/operador/trabajos' : '/dashboard';
?>
<header class="app-header">
    <div class="d-flex align-items-center me-auto">
        <!-- Botón Toggle de Sidebar en móvil -->
        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" aria-label="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand" href="<?= $homeUrl ?>">
            <span>COSMOL <small class="text-muted fs-6 fw-normal d-none d-sm-inline">| Reportes</small></span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <a href="/logout" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
            <span class="d-none d-sm-inline">Cerrar sesión</span>
        </a>
    </div>
</header>
