<?php
$usuarioNombre = isset($_SESSION['usuario']['nombre']) ? $_SESSION['usuario']['nombre'] : 'Usuario';
$rolActual     = isset($_SESSION['usuario']['rol'])    ? $_SESSION['usuario']['rol']    : 'Administrador';
$currentUri    = isset($_SERVER['REQUEST_URI'])        ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/dashboard';

// Helper para verificar ruta activa
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
        <li class="nav-item">
            <a class="nav-link <?= $isActive('/dashboard') ?>" href="/dashboard">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($rolActual === 'Administrador'): ?>
            <!-- Menú Administrador -->
            <li class="sidebar-section-title">Administración</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/seguridad/usuarios') ?>" href="/seguridad/usuarios">
                    <i class="bi bi-people-fill"></i>
                    <span>Usuarios</span>
                </a>
            </li>

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

            <li class="sidebar-section-title">Reportes</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/reportes') ?>" href="/reportes/visualizar">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Consultas Chatbot</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rolActual === 'Operador'): ?>
            <!-- Menú Operador -->
            <li class="sidebar-section-title">Operaciones</li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/trabajos') ?>" href="/operador/trabajos">
                    <i class="bi bi-card-checklist"></i>
                    <span>Mis Trabajos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/registrar') ?>" href="/operador/registrar">
                    <i class="bi bi-check2-circle"></i>
                    <span>Registrar Concluido</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $isActive('/operador/historial') ?>" href="/operador/historial">
                    <i class="bi bi-clock-history"></i>
                    <span>Historial de Trabajos</span>
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
