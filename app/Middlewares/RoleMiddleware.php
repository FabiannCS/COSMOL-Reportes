<?php

namespace App\Middlewares;

class RoleMiddleware
{
    /**
     * Verifica que el rol del usuario autenticado coincida con alguno de los roles permitidos
     *
     * @param array|string $allowedRoles Lista de roles permitidos (ej. ['Administrador', 'Operador'] o 'Administrador')
     * @return void
     */
    public static function handle($allowedRoles = [])
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }

        if (is_string($allowedRoles)) {
            $allowedRoles = array_map('trim', explode(',', $allowedRoles));
        }

        $userRole = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles, true)) {
            $rolesList = implode(', ', $allowedRoles);
            $mensaje = "Esta sección está reservada exclusivamente para usuarios con rol [{$rolesList}]. Tu rol actual es [{$userRole}].";
            renderErrorView(403, 'Rol Insuficiente', $mensaje);
        }
    }
}
