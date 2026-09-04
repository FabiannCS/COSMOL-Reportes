<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza una vista dentro de un layout o de forma independiente
     *
     * @param string $viewName Ruta relativa dentro de app/Views (ej. 'auth/login')
     * @param array $data Variables disponibles para la vista
     * @param string|null $layout Nombre del layout en app/Views/layouts/ (o null para sin layout)
     * @return void
     */
    protected function view($viewName, $data = [], $layout = 'main')
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__));
        $viewFile = $basePath . '/app/Views/' . $viewName . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo "Error: La vista [{$viewName}] no existe en [{$viewFile}].";
            return;
        }

        // Extraer variables para que estén disponibles en la vista
        extract($data, EXTR_SKIP);

        if ($layout === null) {
            require $viewFile;
            return;
        }

        $layoutFile = $basePath . '/app/Views/layouts/' . $layout . '.php';

        // Si se especificó layout pero no existe aún, renderizar vista directa
        if (!file_exists($layoutFile)) {
            require $viewFile;
            return;
        }

        // Capturar contenido de la vista hija
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Renderizar layout principal inyectando $content
        require $layoutFile;
    }

    /**
     * Redirecciona a una URL relativa o absoluta
     *
     * @param string $url
     * @return void
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Retorna una respuesta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
