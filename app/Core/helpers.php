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

