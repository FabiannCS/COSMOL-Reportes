<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Especialidad;

class UsuarioController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuarioModel = new Usuario();
        $rolModel = new Rol();
        $especialidadModel = new Especialidad();

        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : null;
        $usuarios = $usuarioModel->all($buscar);
        $roles = $rolModel->allActive();
        $especialidades = $especialidadModel->getAll();

        // ── Métricas ────────────────────────────────────────────────
        $totalUsuarios  = count($usuarios);
        $activos        = 0;
        $inactivos      = 0;
        $porRol         = [];
        $porEspecialidad = [];

        foreach ($usuarios as $u) {
            if (!empty($u['estado'])) {
                $activos++;
            } else {
                $inactivos++;
            }

            $rol = isset($u['nombre_rol']) ? $u['nombre_rol'] : 'Sin rol';
            if (!isset($porRol[$rol])) {
                $porRol[$rol] = 0;
            }
            $porRol[$rol]++;

            if (!empty($u['nombre_especialidad'])) {
                $esp = $u['nombre_especialidad'];
                if (!isset($porEspecialidad[$esp])) {
                    $porEspecialidad[$esp] = 0;
                }
                $porEspecialidad[$esp]++;
            }
        }

        $porPagina = 10;
        $paginaActual = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($paginaActual < 1) $paginaActual = 1;
        
        $totalPaginas = ceil($totalUsuarios / $porPagina);
        if ($paginaActual > $totalPaginas && $totalPaginas > 0) $paginaActual = $totalPaginas;
        
        $offset = ($paginaActual - 1) * $porPagina;
        $usuariosPaginados = array_slice($usuarios, $offset, $porPagina);

        $mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['mensaje'], $_SESSION['error']);

        $this->view('administrador/usuarios', [
            'title'           => 'Gestión de Personal y Usuarios',
            'usuarios'        => $usuariosPaginados,
            'roles'           => $roles,
            'especialidades'  => $especialidades,
            'totalUsuarios'   => $totalUsuarios,
            'activos'         => $activos,
            'inactivos'       => $inactivos,
            'porRol'          => $porRol,
            'porEspecialidad' => $porEspecialidad,
            'paginaActual'    => $paginaActual,
            'totalPaginas'    => $totalPaginas,
            'mensaje'         => $mensaje,
            'error'           => $error,
            'buscar'          => $buscar
        ], 'main');
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $idRol          = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $idEspecialidad = !empty($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : null;
        $estado         = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if (empty($username) || empty($password) || $idRol <= 0) {
            $_SESSION['error'] = 'Todos los campos obligatorios deben ser completados.';
            $this->redirect('/administrador/usuarios');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/administrador/usuarios');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->existsUsername($username)) {
            $_SESSION['error'] = "El nombre de usuario '{$username}' ya se encuentra registrado.";
            $this->redirect('/administrador/usuarios');
        }

        $creado = $usuarioModel->create([
            'username'        => $username,
            'password'        => $password,
            'id_rol'          => $idRol,
            'id_especialidad' => $idEspecialidad,
            'estado'          => $estado
        ]);

        if ($creado) {
            $_SESSION['mensaje'] = "Usuario '{$username}' creado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al registrar el usuario.';
        }

        $this->redirect('/administrador/usuarios');
    }

    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario      = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        $username       = isset($_POST['username']) ? trim($_POST['username']) : '';
        $idRol          = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $idEspecialidad = !empty($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : null;
        $password       = isset($_POST['password']) ? trim($_POST['password']) : '';

        if ($idUsuario <= 0 || empty($username) || $idRol <= 0) {
            $_SESSION['error'] = 'Datos inválidos para actualizar el usuario.';
            $this->redirect('/administrador/usuarios');
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->existsUsername($username, $idUsuario)) {
            $_SESSION['error'] = "El nombre de usuario '{$username}' ya está en uso por otra cuenta.";
            $this->redirect('/administrador/usuarios');
        }

        if (!empty($password) && strlen($password) < 6) {
            $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
            $this->redirect('/administrador/usuarios');
        }

        $actualizado = $usuarioModel->update($idUsuario, [
            'username'        => $username,
            'id_rol'          => $idRol,
            'id_especialidad' => $idEspecialidad,
            'password'        => $password
        ]);

        if ($actualizado) {
            $_SESSION['mensaje'] = "Usuario '{$username}' actualizado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al actualizar los datos del usuario.';
        }

        $this->redirect('/administrador/usuarios');
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
            $this->redirect('/administrador/usuarios');
        }

        // Prevención de auto-bloqueo del administrador en sesión
        $sesionUserId = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
        if ($idUsuario === $sesionUserId) {
            $_SESSION['error'] = 'No puede desactivar su propia cuenta de usuario en sesión activa.';
            $this->redirect('/administrador/usuarios');
        }

        $usuarioModel = new Usuario();
        $actualizado = $usuarioModel->toggleEstado($idUsuario, $nuevoEstado);

        if ($actualizado) {
            $estadoTexto = ($nuevoEstado === 1) ? 'activado' : 'desactivado';
            $_SESSION['mensaje'] = "El estado del usuario fue {$estadoTexto} correctamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al cambiar el estado del usuario.';
        }

        $this->redirect('/administrador/usuarios');
    }
}
