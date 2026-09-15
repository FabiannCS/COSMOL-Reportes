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
        if (function_exists('renderErrorView')) {
            renderErrorView(404, 'Página No Encontrada', 'La ruta solicitada no existe o no se encuentra disponible en este momento.');
        }

        http_response_code(404);
        echo '404 Página no encontrada';
        exit;
    }
}
