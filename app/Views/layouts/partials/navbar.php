<?php
$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : '';
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
$homeUrl       = ($rolActual === 'Operador') ? '/operador/trabajos' : '/dashboard';
?>
<header class="app-header">
    <div class="d-flex align-items-center me-auto">
        <!-- Botón Toggle de Sidebar en móvil -->
        <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-lg-none me-2" type="button" aria-label="Abrir menú" title="Abrir menú">
            <i class="bi bi-list fs-5"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $homeUrl ?>">
            <img src="/assets/img/logo.jpeg" alt="COSMOL Logo" style="height: 32px; width: auto; object-fit: contain;">
            <span class="text-muted fs-6 fw-normal d-none d-sm-inline">| Reportes</span>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">
        <span class="text-secondary d-none d-md-inline" style="font-size: 0.875rem;">
            <i class="bi bi-person-fill me-1"></i> USUARIO: <?= htmlspecialchars($rolActual) ?>
        </span>
        
        <a href="/logout" class="btn btn-sm d-flex align-items-center gap-1 hoover" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</header>
