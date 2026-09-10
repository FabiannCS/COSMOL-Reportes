<?php
$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : '';
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
$permisos      = isset($_SESSION['usuario']['permisos']) && is_array($_SESSION['usuario']['permisos']) 
    ? $_SESSION['usuario']['permisos'] 
    : [];

$hasPermission = function ($clave) use ($permisos) {
    return in_array($clave, $permisos, true);
};

$currentUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/dashboard';

$isActive = function ($path) use ($currentUri) {
    if ($path === '/dashboard' && ($currentUri === '/' || $currentUri === '/dashboard')) {
        return 'active';
    }
    return ($currentUri === $path || strpos($currentUri, $path) === 0) ? 'active' : '';
};
?>
<aside class="app-sidebar" id="appSidebar">
    <!-- Cabecera del Sidebar (Control de plegado en escritorio) -->
    <div class="sidebar-header d-none d-lg-flex align-items-center justify-content-between">
        <span class="sidebar-brand-text">Menú</span>
        <button id="sidebarCollapseBtn" class="sidebar-collapse-btn" type="button" aria-label="Plegar barra lateral" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Plegar menú lateral">
            <i class="bi bi-chevron-left sidebar-collapse-icon"></i>
        </button>
    </div>

    <!-- Cabecera en Móvil (Cerrar menú lateral) -->
    <div class="sidebar-header-mobile d-flex d-lg-none align-items-center justify-content-between">
        <span class="sidebar-brand-text text-white">Menú</span>
        <button id="sidebarCloseMobileBtn" class="sidebar-close-mobile-btn" type="button" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Navegación Principal -->
    <ul class="sidebar-nav flex-grow-1">
        <!-- Dashboard: Visible para Administradores y Supervisores con permisos -->
        <?php if ($rolActual !== 'Operador' && ($hasPermission('reportes.ver') || $hasPermission('trabajos.ver') || $hasPermission('usuarios.ver') || $hasPermission('roles.ver'))): ?>
            <li class="nav-item">
                <a class="nav-link <?= $isActive('/dashboard') ?>" href="/dashboard" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span style="color: #f8fafc;">Dashboard</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Módulo Seguridad -->
        <?php if ($hasPermission('roles.ver') || $hasPermission('roles.permisos')): ?>
            <li class="sidebar-section-title">Seguridad</li>

            <li class="nav-item">
                <a class="nav-link <?= ($isActive('/seguridad/roles') || $isActive('/seguridad/permisos')) ?>" href="/seguridad/roles" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Roles y Permisos">
                    <i class="bi bi-shield-shaded"></i>
                    <span style="color: #f8fafc;">Roles y Permisos</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Módulo Administración Operativa (Oculto para Operador) -->
        <?php if ($rolActual !== 'Operador' && ($hasPermission('trabajos.ver') || $hasPermission('usuarios.ver'))): ?>
            <li class="sidebar-section-title">Administración</li>

            <?php if ($hasPermission('trabajos.ver')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/trabajos') && !$isActive('/administrador/trabajos/detalle') ? 'active' : '' ?>" href="/administrador/trabajos" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Supervisión de Trabajos">
                        <i class="bi bi-clipboard-data"></i>
                        <span style="color: #f8fafc;">Supervisión de Trabajos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/historial') ?>" href="/administrador/historial" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Trabajos Concluidos">
                        <i class="bi bi-clock-history"></i>
                        <span style="color: #f8fafc;">Trabajos Concluidos</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($hasPermission('usuarios.ver')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/usuarios') ?>" href="/administrador/usuarios" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Gestión de Personal">
                        <i class="bi bi-person-lines-fill"></i>
                        <span style="color: #f8fafc;">Gestión de Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Módulo Reportes (Oculto para Operador) -->
        <?php if ($rolActual !== 'Operador' && $hasPermission('reportes.ver')): ?>
            <li class="sidebar-section-title">Reportes</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/reportes') ?>" href="/reportes/visualizar" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Consultas Chatbot">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span style="color: #f8fafc;">Consultas Chatbot</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rolActual === 'Operador'): ?>
            <!-- Módulo Operador -->
            <li class="sidebar-section-title">Mis Operaciones</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/trabajos') ?>" href="/operador/trabajos" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Mis Trabajos">
                    <i class="bi bi-card-checklist"></i>
                    <span>Mis Trabajos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/historial') ?>" href="/operador/historial" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Historial de Trabajos">
                    <i class="bi bi-clock-history"></i>
                    <span>Historial</span>
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <!-- Tarjeta inferior de Usuario (Enlace al Perfil) -->
    <a href="/perfil" class="sidebar-user-badge mt-auto text-decoration-none d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Mi Perfil (<?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?>)">
        <div class="user-avatar">
            <?= htmlspecialchars(strtoupper(substr($usuarioNombre, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="user-role"><?= htmlspecialchars($rolActual, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </a>
</aside>
