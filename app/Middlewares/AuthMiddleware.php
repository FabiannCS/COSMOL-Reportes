<?php

namespace App\Middlewares;

class AuthMiddleware
{
    /**
     * Verifica que exista una sesión activa de usuario
     *
     * @return void
     */
    public static function handle()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            header('Location: /login');
            exit;
        }
    }
}
