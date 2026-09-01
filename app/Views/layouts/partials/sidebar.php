<?php
$usuarioNombre = isset($_SESSION['usuario']['username'])   ? $_SESSION['usuario']['username']   : '';
$rolActual     = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
$currentUri    = isset($_SERVER['REQUEST_URI'])          ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/dashboard';

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
        <!-- Dashboard: Solo para Administrador y Supervisor -->
        <?php if ($rolActual === 'Administrador' || $rolActual === 'Supervisor'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $isActive('/dashboard') ?>" href="/dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rolActual === 'Administrador'): ?>
            <!-- Módulo Seguridad -->
            <li class="sidebar-section-title">Seguridad</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/seguridad/usuarios') ?>" href="/seguridad/usuarios">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= ($isActive('/seguridad/roles') || $isActive('/seguridad/permisos')) ?>" href="/seguridad/roles">
                    <i class="bi bi-shield-shaded"></i>
                    <span>Roles y Permisos</span>
                </a>
            </li>

            <!-- Módulo Administración Operativa -->
            <li class="sidebar-section-title">Administración</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/administrador/trabajos') ?>" href="/administrador/trabajos">
                    <i class="bi bi-briefcase-fill"></i>
                    <span>Gestión de Trabajos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/administrador/trabajos/crear') ?>" href="/administrador/trabajos/crear">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Registrar Trabajo</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/administrador/estados') ?>" href="/administrador/estados">
                    <i class="bi bi-tags-fill"></i>
                    <span>Estados</span>
                </a>
            </li>

            <!-- Módulo Reportes -->
            <li class="sidebar-section-title">Reportes</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/reportes') ?>" href="/reportes/visualizar">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Consultas Chatbot</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rolActual === 'Supervisor'): ?>
            <!-- Módulo Reportes para Supervisor -->
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
        <?php endif; ?>
    </ul>

    <!-- Tarjeta inferior de Usuario -->
    <div class="sidebar-user-badge mt-auto">
        <div class="user-avatar">
            <?= htmlspecialchars(strtoupper(substr($usuarioNombre, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="user-role"><?= htmlspecialchars($rolActual, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</aside>
