<?php

namespace App\Middlewares;

class PermissionMiddleware
{
    /**
     * Verifica que el usuario autenticado posea el o los permisos requeridos
     *
     * @param string|array $requiredPermissions Permiso o lista de permisos (ej. 'usuarios.ver' o 'reportes.ver,trabajos.ver')
     * @return void
     */
    public static function handle($requiredPermissions = '')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }

        if (is_string($requiredPermissions)) {
            $requiredPermissions = array_map('trim', explode(',', $requiredPermissions));
        } else {
            $requiredPermissions = (array)$requiredPermissions;
        }

        $userPermisos = isset($_SESSION['usuario']['permisos']) && is_array($_SESSION['usuario']['permisos'])
            ? $_SESSION['usuario']['permisos']
            : [];

        $hasPermission = false;
        if (empty($requiredPermissions)) {
            $hasPermission = true;
        } else {
            foreach ($requiredPermissions as $perm) {
                if (in_array($perm, $userPermisos, true)) {
                    $hasPermission = true;
                    break;
                }
            }
        }

        if (!$hasPermission) {
            $permList = implode(', ', $requiredPermissions);
            $mensaje = "No cuentas con los permisos necesarios ({$permList}) para acceder a esta sección. Si requieres acceso, solicita al Administrador del Sistema que configure tus permisos.";
            renderErrorView(403, 'Acceso Denegado', $mensaje);
        }
    }
}
