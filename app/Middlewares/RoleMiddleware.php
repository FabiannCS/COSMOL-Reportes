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
            if ($userRole === 'Operador') {
                header('Location: /operador/trabajos');
                exit;
            }

            http_response_code(403);
            echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>403 Acceso Denegado</title></head><body style='font-family:sans-serif; text-align:center; padding:3rem;'><h1>403 — Acceso Denegado</h1><p>No posee el rol suficiente para acceder a esta sección.</p><a href='/dashboard'>Volver al Panel Principal</a></body></html>";
            exit;
        }
    }
}
