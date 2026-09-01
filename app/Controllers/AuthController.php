<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya está autenticado, redirigir según su rol
        if (isset($_SESSION['usuario'])) {
            $rol = isset($_SESSION['usuario']['nombre_rol']) ? $_SESSION['usuario']['nombre_rol'] : '';
            if ($rol === 'Operador') {
                $this->redirect('/operador/trabajos');
            }
            $this->redirect('/dashboard');
        }

        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['error']);

        $this->view('auth/login', [
            'title' => 'Iniciar Sesión — COSMOL Reportes',
            'error' => $error
        ], null);
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Por favor, ingrese su usuario y contraseña.';
            $this->redirect('/login');
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Guardar datos del usuario en sesión (sin el hash de contraseña)
            $_SESSION['usuario'] = [
                'id_usuario' => $user['id_usuario'],
                'username'   => $user['username'],
                'id_rol'     => $user['id_rol'],
                'nombre_rol' => $user['nombre_rol']
            ];

            // Redirigir según el rol del usuario
            if ($user['nombre_rol'] === 'Operador') {
                $this->redirect('/operador/trabajos');
            }

            $this->redirect('/dashboard');
        }

        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        $this->redirect('/login');
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        $this->redirect('/login');
    }

    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
        $rol = isset($usuario['nombre_rol']) ? $usuario['nombre_rol'] : '';

        // El Operador no tiene acceso al Dashboard
        if ($rol === 'Operador') {
            $this->redirect('/operador/trabajos');
        }

        $this->view('dashboard/index', [
            'title'   => 'Dashboard — COSMOL Reportes',
            'usuario' => $usuario
        ], 'main');
    }
}
