<?php

if (!function_exists('hasPermission')) {
    /**
     * Verifica si el usuario en sesión posee un permiso específico
     *
     * @param string $clavePermiso Clave del permiso (ej. 'usuarios.crear')
     * @return bool
     */
    function hasPermission($clavePermiso)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $permisos = isset($_SESSION['usuario']['permisos']) && is_array($_SESSION['usuario']['permisos'])
            ? $_SESSION['usuario']['permisos']
            : [];

        return in_array($clavePermiso, $permisos, true);
    }
}

if (!function_exists('hasRole')) {
    /**
     * Verifica si el usuario en sesión posee un rol específico (o varios)
     *
     * @param string|array $rol Nombre del rol (ej. 'Administrador' o ['Administrador', 'Supervisor'])
     * @return bool
     */
    function hasRole($rol)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rolUsuario = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
        if (is_array($rol)) {
            return in_array($rolUsuario, $rol, true);
        }

        return $rolUsuario === $rol;
    }
}

if (!function_exists('csrfToken')) {
    /**
     * Genera u obtiene el token CSRF actual en sesión
     *
     * @return string
     */
    function csrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrfField')) {
    /**
     * Genera un campo oculto HTML con el token CSRF
     *
     * @return string
     */
    function csrfField()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verifyCsrfToken')) {
    /**
     * Verifica si el token CSRF proporcionado coincide con el de la sesión
     *
     * @param string|null $token
     * @return bool
     */
    function verifyCsrfToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('renderErrorView')) {
    /**
     * Renderiza una vista de error amigable dentro del layout o independiente
     *
     * @param int $statusCode Código de estado HTTP (ej. 403, 404, 500)
     * @param string $titulo Título principal del error
     * @param string $mensaje Descripción explicativa del error
     * @return void
     */
    function renderErrorView($statusCode = 403, $titulo = 'Acceso Denegado', $mensaje = 'No cuenta con los permisos necesarios.')
    {
        http_response_code($statusCode);

        // Si es una petición JSON/API, responder en formato JSON
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
               || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') === 0);

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'estado'  => 'error',
                'codigo'  => $statusCode,
                'mensaje' => $mensaje
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__));
        $viewFile = $basePath . '/app/Views/errors/' . $statusCode . '.php';

        if (!file_exists($viewFile)) {
            $viewFile = $basePath . '/app/Views/errors/403.php';
        }

        $data = [
            'title'      => "{$statusCode} {$titulo} — COSMOL Reportes",
            'statusCode' => $statusCode,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje
        ];

        extract($data, EXTR_SKIP);

        // Si el usuario tiene sesión activa, renderizar dentro del layout 'main'
        if (isset($_SESSION['usuario'])) {
            $layoutFile = $basePath . '/app/Views/layouts/main.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $viewFile;
                $content = ob_get_clean();
                require $layoutFile;
                exit;
            }
        }

        // Si no hay sesión o no hay layout, renderizar directamente
        require $viewFile;
        exit;
    }
}

if (!function_exists('determinarEstadoTrabajo')) {
    /**
     * Determina el estado real/operativo de un trabajo (reconexión o reclamo)
     * analizando tanto el campo 'estado' como las anotaciones en la 'glosa'
     * (ej. [NO CONCLUIDO], [NO PROCEDENTE], [SOLUCIONADO]).
     *
     * @param array|null $item
     * @return string 'PENDIENTE' | 'NO CONCLUIDO' | 'NO PROCEDENTE' | 'CONCLUIDO' | 'CONCLUIDA'
     */
    function determinarEstadoTrabajo($item)
    {
        if (!is_array($item)) {
            return 'PENDIENTE';
        }

        $estadoRaw = strtoupper(trim(isset($item['estado']) ? (string)$item['estado'] : ''));
        $glosa = strtoupper(isset($item['glosa']) ? (string)$item['glosa'] : '');

        // 1. Si el estado explícito es PENDIENTE
        if ($estadoRaw === 'PENDIENTE') {
            return 'PENDIENTE';
        }

        // 2. No concluido (en estado o glosa)
        if (
            $estadoRaw === 'NO CONCLUIDO' || 
            strpos($glosa, '[NO CONCLUIDO]') !== false || 
            (strpos($glosa, 'CONCLUSIÓN:') !== false && strpos($glosa, 'NO CONCLUIDO') !== false)
        ) {
            return 'NO CONCLUIDO';
        }

        // 3. No procedente / Falsa alarma
        if (
            $estadoRaw === 'NO PROCEDENTE' || 
            strpos($glosa, '[NO PROCEDENTE]') !== false || 
            strpos($glosa, '[FALSA_ALARMA]') !== false || 
            strpos($glosa, '[FALSA ALARMA]') !== false
        ) {
            return 'NO PROCEDENTE';
        }

        // 4. Concluido / Solucionado
        if ($estadoRaw === 'CONCLUIDA') {
            return 'CONCLUIDA';
        }

        if ($estadoRaw === 'CONCLUIDO' || strpos($glosa, '[SOLUCIONADO]') !== false || strpos($glosa, '[CONCLUIDO]') !== false) {
            return 'CONCLUIDO';
        }

        return !empty($estadoRaw) ? $estadoRaw : 'PENDIENTE';
    }
}

if (!function_exists('calcularPlazo24h')) {
    /**
     * Calcula la fecha/hora de llegada y el estado del plazo de 24 horas
     * para la atención de solicitudes de reconexión y reclamos.
     *
     * @param string|null $fechaRegistro Fecha de registro de la solicitud
     * @param string $estado Estado del trabajo ('PENDIENTE', 'CONCLUIDO', etc.)
     * @return array
     */
    function calcularPlazo24h($fechaRegistro, $estado = 'PENDIENTE')
    {
        if (empty($fechaRegistro)) {
            return [
                'fechaLlegada'    => '—',
                'fechaSolo'       => '—',
                'horaLlegada'     => '—',
                'fechaLimite'     => '—',
                'label'           => 'Sin fecha',
                'badgeClass'      => 'bg-light text-secondary border border-secondary-subtle',
                'estadoPlazo'     => 'desconocido',
                'horasRestantes'  => null,
                'minutosRestantes'=> null,
                'esVencido'       => false,
            ];
        }

        $tsRegistro = strtotime($fechaRegistro);
        if (!$tsRegistro) {
            return [
                'fechaLlegada'    => htmlspecialchars((string)$fechaRegistro, ENT_QUOTES, 'UTF-8'),
                'fechaSolo'       => '—',
                'horaLlegada'     => '—',
                'fechaLimite'     => '—',
                'label'           => 'Fecha no válida',
                'badgeClass'      => 'bg-light text-secondary border border-secondary-subtle',
                'estadoPlazo'     => 'desconocido',
                'horasRestantes'  => null,
                'minutosRestantes'=> null,
                'esVencido'       => false,
            ];
        }

        $tsLimite     = $tsRegistro + (24 * 3600);
        $fechaLlegada = date('d/m/Y H:i', $tsRegistro);
        $fechaSolo    = date('d/m/Y', $tsRegistro);
        $horaLlegada  = date('H:i', $tsRegistro);
        $fechaLimite  = date('d/m/Y H:i', $tsLimite);

        $ahora        = time();
        $diffSegundos = $tsLimite - $ahora;

        $estadoUpper = strtoupper(trim((string)$estado));
        $esConcluido = ($estadoUpper === 'CONCLUIDO' || $estadoUpper === 'CONCLUIDA');

        if ($esConcluido) {
            return [
                'fechaLlegada'    => $fechaLlegada,
                'fechaSolo'       => $fechaSolo,
                'horaLlegada'     => $horaLlegada,
                'fechaLimite'     => $fechaLimite,
                'label'           => 'Concluido',
                'badgeClass'      => 'bg-success-subtle text-success border border-success-subtle',
                'estadoPlazo'     => 'concluido',
                'horasRestantes'  => 0,
                'minutosRestantes'=> 0,
                'esVencido'       => false,
            ];
        }

        if ($diffSegundos >= 0) {
            $horas   = (int)floor($diffSegundos / 3600);
            $minutos = (int)floor(($diffSegundos % 3600) / 60);

            if ($horas >= 6) {
                $label      = "Quedan {$horas}h {$minutos}m";
                $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                $estadoPlazo= 'a_tiempo';
            } else {
                $label      = ($horas > 0) ? "Quedan {$horas}h {$minutos}m" : "Quedan {$minutos}m";
                $badgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
                $estadoPlazo= 'por_vencer';
            }

            return [
                'fechaLlegada'    => $fechaLlegada,
                'fechaSolo'       => $fechaSolo,
                'horaLlegada'     => $horaLlegada,
                'fechaLimite'     => $fechaLimite,
                'label'           => $label,
                'badgeClass'      => $badgeClass,
                'estadoPlazo'     => $estadoPlazo,
                'horasRestantes'  => $horas,
                'minutosRestantes'=> $minutos,
                'esVencido'       => false,
            ];
        } else {
            $diffAbs = abs($diffSegundos);
            $horas   = (int)floor($diffAbs / 3600);
            $minutos = (int)floor(($diffAbs % 3600) / 60);

            if ($horas >= 24) {
                $dias = (int)floor($horas / 24);
                $restoHoras = $horas % 24;
                $label = "Vencido hace {$dias}d " . ($restoHoras > 0 ? "{$restoHoras}h" : '');
                $label = trim($label);
            } else {
                $label = "Vencido hace {$horas}h {$minutos}m";
            }

            return [
                'fechaLlegada'    => $fechaLlegada,
                'fechaSolo'       => $fechaSolo,
                'horaLlegada'     => $horaLlegada,
                'fechaLimite'     => $fechaLimite,
                'label'           => $label,
                'badgeClass'      => 'bg-danger-subtle text-danger border border-danger',
                'estadoPlazo'     => 'vencido',
                'horasRestantes'  => -$horas,
                'minutosRestantes'=> -$minutos,
                'esVencido'       => true,
            ];
        }
    }
}



