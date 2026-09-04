<?php

namespace App\Controllers;

use App\Core\Controller;

class AuthController extends Controller
{
    /**
     * Muestra la vista del formulario de login
     */
    public function showLogin()
    {
        $this->view('auth/login', [
            'title' => 'Iniciar Sesión — COSMOL Reportes'
        ], null);
    }

    /**
     * Muestra el panel principal / Dashboard
     */
    public function dashboard()
    {
        $this->view('dashboard/index', [
            'title' => 'Dashboard — COSMOL Reportes'
        ]);
    }

    /**
     * Procesa la autenticación
     */
    public function login()
    {
        // Se implementará en la Fase 4
        $this->redirect('/login');
    }

    /**
     * Cierra la sesión activa
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->redirect('/login');
    }
}
