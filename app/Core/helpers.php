<?php

if (!function_exists('hasPermission')) {
    /**
     * Verifica si el usuario en sesión posee un permiso específico
     *
     * @param string $clavePermiso Clave del permiso (ej. 'usuarios.crear')
     * @return bool
     */
    function hasPermission($clavePermiso)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $permisos = isset($_SESSION['usuario']['permisos']) && is_array($_SESSION['usuario']['permisos'])
            ? $_SESSION['usuario']['permisos']
            : [];

        return in_array($clavePermiso, $permisos, true);
    }
}
