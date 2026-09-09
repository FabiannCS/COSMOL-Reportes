<?php

namespace App\Middlewares;

class CsrfMiddleware
{
    /**
     * Valida el token CSRF en peticiones HTTP que modifican el estado (POST, PUT, DELETE, PATCH)
     *
     * @return void
     */
    public static function handle()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;

            if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }

            if (!verifyCsrfToken($token)) {
                http_response_code(403);
                echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>403 — Solicitud Inválida / Sesión Expirada</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #334155; }
        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center; max-width: 440px; }
        h1 { font-size: 2.5rem; margin: 0 0 0.5rem; color: #e11d48; }
        p { margin-bottom: 1.5rem; color: #64748b; font-size: 0.95rem; line-height: 1.5; }
        a { display: inline-block; background: #0284c7; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 500; }
        a:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class='card'>
        <h1>419</h1>
        <h2>Solicitud Expirada o Inválida</h2>
        <p>La validación de seguridad (CSRF) ha fallado o su sesión ha caducado. Por favor, vuelva a intentar la acción.</p>
        <a href='javascript:history.back()'>Volver atrás</a>
    </div>
</body>
</html>";
                exit;
            }
        }
    }
}
