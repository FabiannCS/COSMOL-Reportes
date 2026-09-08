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
    <!-- Navegación Principal -->
    <ul class="sidebar-nav flex-grow-1">
        <!-- Dashboard: Visible para Administradores y Supervisores con permisos -->
        <?php if ($rolActual !== 'Operador' && ($hasPermission('reportes.ver') || $hasPermission('trabajos.ver') || $hasPermission('usuarios.ver') || $hasPermission('roles.ver'))): ?>
            <li class="nav-item">
                <a class="nav-link <?= $isActive('/dashboard') ?>" href="/dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Módulo Seguridad -->
        <?php if ($hasPermission('roles.ver') || $hasPermission('roles.permisos')): ?>
            <li class="sidebar-section-title">Seguridad</li>

            <li class="nav-item">
                <a class="nav-link <?= ($isActive('/seguridad/roles') || $isActive('/seguridad/permisos')) ?>" href="/seguridad/roles">
                    <i class="bi bi-shield-shaded"></i>
                    <span>Roles y Permisos</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Módulo Administración Operativa (Oculto para Operador) -->
        <?php if ($rolActual !== 'Operador' && ($hasPermission('trabajos.ver') || $hasPermission('usuarios.ver'))): ?>
            <li class="sidebar-section-title">Administración</li>

            <?php if ($hasPermission('trabajos.ver')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/trabajos') && !$isActive('/administrador/trabajos/detalle') ? 'active' : '' ?>" href="/administrador/trabajos">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Supervisión de Trabajos</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/historial') ?>" href="/administrador/historial">
                        <i class="bi bi-clock-history"></i>
                        <span>Trabajos Concluidos</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($hasPermission('usuarios.ver')): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('/administrador/usuarios') ?>" href="/administrador/usuarios">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Gestión de Personal</span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Módulo Reportes (Oculto para Operador) -->
        <?php if ($rolActual !== 'Operador' && $hasPermission('reportes.ver')): ?>
            <li class="sidebar-section-title">Reportes</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/reportes') ?>" href="/reportes/visualizar">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Consultas Chatbot</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rolActual === 'Operador'): ?>
            <!-- Módulo Operador -->
            <li class="sidebar-section-title">Mis Operaciones</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/trabajos') ?>" href="/operador/trabajos">
                    <i class="bi bi-card-checklist"></i>
                    <span>Mis Trabajos</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/historial') ?>" href="/operador/historial">
                    <i class="bi bi-clock-history"></i>
                    <span>Historial</span>
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <!-- Tarjeta inferior de Usuario (Enlace al Perfil) -->
    <a href="/perfil" class="sidebar-user-badge mt-auto text-decoration-none d-flex align-items-center">
        <div class="user-avatar">
            <?= htmlspecialchars(strtoupper(substr($usuarioNombre, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="user-role"><?= htmlspecialchars($rolActual, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </a>
</aside>
