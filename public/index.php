<?php

define('BASE_PATH', dirname(__DIR__));
date_default_timezone_set('America/La_Paz');

$autoloadPath = BASE_PATH . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
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

// 1.5 Cargar funciones helper globales
$helpersPath = BASE_PATH . '/app/Core/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
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

// 4. Iniciar y endurecer sesión PHP
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        ini_set('session.cookie_secure', $isHttps ? '1' : '0');
        session_set_cookie_params(0, '/; SameSite=Lax', '', $isHttps, true);
    }

    session_start();
}

// 5. Cargar rutas y despachar la petición
$routesFile = BASE_PATH . '/app/Config/routes.php';
$routes = file_exists($routesFile) ? require $routesFile : [];

$router = new \App\Core\Router($routes);
$router->dispatch();
