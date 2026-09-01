<?php

namespace App\Core;

class Router
{
    /** @var array */
    private $routes = [];

    /**
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        $this->routes = $routes;
    }

    /**
     * Establece o añade rutas al Router
     *
     * @param array $routes
     * @return void
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * Despacha la petición actual
     *
     * @param string|null $requestUri
     * @param string|null $requestMethod
     * @return void
     */
    public function dispatch($requestUri = null, $requestMethod = null)
    {
        $uri = $requestUri !== null ? $requestUri : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
        $method = $requestMethod !== null ? $requestMethod : (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');

        // Eliminar query string (?param=val)
        $parsedUrl = parse_url($uri, PHP_URL_PATH);
        $path = '/' . trim($parsedUrl, '/');
        if ($path === '//') {
            $path = '/';
        }

        $method = strtoupper($method);

        // Buscar coincidencia exacta en las rutas registradas
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $controllerName = $route[0];
            $action = $route[1];
            $middlewares = isset($route[2]) ? $route[2] : [];

            // Ejecutar middlewares si están definidos
            if (!empty($middlewares)) {
                $this->executeMiddlewares($middlewares);
            }

            // Instanciar Controller
            $fullControllerClass = "App\\Controllers\\{$controllerName}";

            if (!class_exists($fullControllerClass)) {
                http_response_code(500);
                echo "Error 500: El controlador [{$fullControllerClass}] no fue encontrado.";
                return;
            }

            $controller = new $fullControllerClass();

            if (!method_exists($controller, $action)) {
                http_response_code(500);
                echo "Error 500: El método [{$action}] no existe en el controlador [{$fullControllerClass}].";
                return;
            }

            // Invocar el método del Controller
            $controller->$action();
            return;
        }

        // Ruta no encontrada → 404
        $this->notFound();
    }

    /**
     * Ejecuta la lista de middlewares para la ruta
     *
     * @param array $middlewares
     * @return void
     */
    private function executeMiddlewares(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $parts = explode(':', $middleware, 2);
            $name = $parts[0];
            $arg = isset($parts[1]) ? $parts[1] : null;

            $middlewareClass = "App\\Middlewares\\" . ucfirst($name) . "Middleware";
            if (class_exists($middlewareClass)) {
                if (method_exists($middlewareClass, 'handle')) {
                    if ($arg !== null) {
                        call_user_func([$middlewareClass, 'handle'], $arg);
                    } else {
                        call_user_func([$middlewareClass, 'handle']);
                    }
                }
            }
        }
    }


    /**
     * Respuesta para error 404
     *
     * @return void
     */
    private function notFound()
    {
        http_response_code(404);
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__));
        $custom404 = $basePath . '/app/Views/errors/404.php';

        if (file_exists($custom404)) {
            require $custom404;
            return;
        }

        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 — Página no encontrada</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #334155; }
        .card { background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center; max-width: 420px; }
        h1 { font-size: 3rem; margin: 0 0 0.5rem; color: #0284c7; }
        p { margin-bottom: 1.5rem; color: #64748b; }
        a { display: inline-block; background: #0284c7; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 500; }
        a:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class="card">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>La ruta solicitada no existe o no se encuentra disponible en este momento.</p>
        <a href="/">Volver al inicio</a>
    </div>
</body>
</html>';
    }
}
