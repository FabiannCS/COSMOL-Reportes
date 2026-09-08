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
            $_SESSION['error'] = 'No cuenta con los permisos necesarios para realizar esta acción.';

            $userRole = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
            $referer  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

            if (!empty($referer) && strpos($referer, isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') !== false) {
                header("Location: {$referer}");
                exit;
            }

            if ($userRole === 'Operador') {
                header('Location: /operador/trabajos');
                exit;
            }

            header('Location: /dashboard');
            exit;
        }
    }
}
