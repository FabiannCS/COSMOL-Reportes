<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;

class UsuarioController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuarioModel = new Usuario();
        $rolModel = new Rol();

        $usuarios = $usuarioModel->all();
        $roles = $rolModel->allActive();

        $mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['mensaje'], $_SESSION['error']);

        $this->view('seguridad/usuarios', [
            'title'    => 'Gestión de Usuarios — COSMOL Reportes',
            'usuarios' => $usuarios,
            'roles'    => $roles,
            'mensaje'  => $mensaje,
            'error'    => $error
        ], 'main');
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $idRol    = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $estado   = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($username) || empty($password) || $idRol <= 0) {
            $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
            $this->redirect('/seguridad/usuarios');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/seguridad/usuarios');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->existsUsername($username)) {
            $_SESSION['error'] = "El nombre de usuario '{$username}' ya se encuentra registrado.";
            $this->redirect('/seguridad/usuarios');
        }

        $creado = $usuarioModel->create([
            'username' => $username,
            'password' => $password,
            'id_rol'   => $idRol,
            'estado'   => $estado
        ]);

        if ($creado) {
            $_SESSION['mensaje'] = "Usuario '{$username}' creado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al registrar el usuario.';
        }

        $this->redirect('/seguridad/usuarios');
    }

    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        $username  = isset($_POST['username']) ? trim($_POST['username']) : '';
        $idRol     = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $password  = isset($_POST['password']) ? trim($_POST['password']) : '';

        if ($idUsuario <= 0 || empty($username) || $idRol <= 0) {
            $_SESSION['error'] = 'Datos inválidos para actualizar el usuario.';
            $this->redirect('/seguridad/usuarios');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->existsUsername($username, $idUsuario)) {
            $_SESSION['error'] = "El nombre de usuario '{$username}' ya está en uso por otra cuenta.";
            $this->redirect('/seguridad/usuarios');
        }

        if (!empty($password) && strlen($password) < 6) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/seguridad/usuarios');
        }

        $actualizado = $usuarioModel->update($idUsuario, [
            'username' => $username,
            'id_rol'   => $idRol,
            'password' => $password
        ]);

        if ($actualizado) {
            $_SESSION['mensaje'] = "Usuario '{$username}' actualizado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al actualizar los datos del usuario.';
        }

        $this->redirect('/seguridad/usuarios');
    }

    public function toggleEstado()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario   = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        $nuevoEstado = isset($_POST['nuevo_estado']) ? (int)$_POST['nuevo_estado'] : 0;

        if ($idUsuario <= 0) {
            $_SESSION['error'] = 'Identificador de usuario inválido.';
            $this->redirect('/seguridad/usuarios');
        }

        // Prevención de auto-bloqueo del administrador en sesión
        $sesionUserId = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
        if ($idUsuario === $sesionUserId) {
            $_SESSION['error'] = 'No puede desactivar su propia cuenta de usuario en sesión activa.';
            $this->redirect('/seguridad/usuarios');
        }

        $usuarioModel = new Usuario();
        $actualizado = $usuarioModel->toggleEstado($idUsuario, $nuevoEstado);

        if ($actualizado) {
            $estadoTexto = ($nuevoEstado === 1) ? 'activado' : 'desactivado';
            $_SESSION['mensaje'] = "El estado del usuario fue {$estadoTexto} correctamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al cambiar el estado del usuario.';
        }

        $this->redirect('/seguridad/usuarios');
    }
}
