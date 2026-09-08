<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

class PerfilController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
        
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($idUsuario);
        
        if (!$usuario) {
            $_SESSION['error'] = 'No se encontró la información del usuario.';
            $this->redirect('/');
        }

        $especialidad = !empty($usuario['nombre_especialidad']) ? $usuario['nombre_especialidad'] : null;

        $this->view('perfil/index', [
            'title' => 'Mi Perfil',
            'usuario' => $usuario,
            'especialidad' => $especialidad
        ], 'main');
    }
}
