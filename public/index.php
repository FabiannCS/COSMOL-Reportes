<?php

/**
 * COSMOL Reportes — Front Controller
 * Punto de entrada único de la aplicación
 */

define('BASE_PATH', dirname(__DIR__));

// 1. Cargar Autoloader de Composer
$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    // Si aún no se ha corrido composer install, fallback a autoloader manual simple
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $baseDir = BASE_PATH . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    });
} else {
    require_once $autoloadPath;
}

// 2. Cargar variables de entorno desde .env
function loadEnv($envFile)
{
    if (!file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $val) = explode('=', $line, 2);
            $name = trim($name);
            $val = trim($val);

            // Eliminar comillas externas si existen
            if (preg_match('/^"([^"]*)"$/', $val, $m)) {
                $val = $m[1];
            } elseif (preg_match('/^\'([^\']*)\'$/', $val, $m)) {
                $val = $m[1];
            }

            if (getenv($name) === false) {
                putenv("{$name}={$val}");
                $_ENV[$name] = $val;
                $_SERVER[$name] = $val;
            }
        }
    }
}

loadEnv(BASE_PATH . '/.env');

// 3. Manejo de Errores según APP_DEBUG
$debug = getenv('APP_DEBUG');
if ($debug === 'true' || $debug === '1') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 4. Iniciar sesión PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 5. Cargar rutas y despachar la petición
$routesFile = BASE_PATH . '/app/Config/routes.php';
$routes = file_exists($routesFile) ? require $routesFile : [];

$router = new \App\Core\Router($routes);
$router->dispatch();
