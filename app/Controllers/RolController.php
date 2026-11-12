<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Rol;
use App\Models\Permiso;

class RolController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rolModel = new Rol();
        $roles = $rolModel->all();

        $mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
        $error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['mensaje'], $_SESSION['error']);

        $this->view('seguridad/roles', [
            'title'   => 'Gestión de Roles — COSMOL Reportes',
            'roles'   => $roles,
            'mensaje' => $mensaje,
            'error'   => $error
        ], 'main');
    }

    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $nombreRol   = isset($_POST['nombre_rol']) ? trim($_POST['nombre_rol']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

        if (empty($nombreRol) || empty($descripcion)) {
            $_SESSION['error'] = 'El nombre y la descripción del rol son obligatorios.';
            $this->redirect('/seguridad/roles');
        }

        $rolModel = new Rol();

        if ($rolModel->existsNombre($nombreRol)) {
            $_SESSION['error'] = "El rol '{$nombreRol}' ya existe en el sistema.";
            $this->redirect('/seguridad/roles');
        }

        $creado = $rolModel->create([
            'nombre_rol'  => $nombreRol,
            'descripcion' => $descripcion
        ]);

        if ($creado) {
            $_SESSION['mensaje'] = "Rol '{$nombreRol}' creado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al registrar el nuevo rol.';
        }

        $this->redirect('/seguridad/roles');
    }

    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idRol       = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $nombreRol   = isset($_POST['nombre_rol']) ? trim($_POST['nombre_rol']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

        if ($idRol <= 0 || empty($nombreRol) || empty($descripcion)) {
            $_SESSION['error'] = 'Datos inválidos para actualizar el rol.';
            $this->redirect('/seguridad/roles');
        }

        $rolModel = new Rol();
        $rolActual = $rolModel->findById($idRol);

        if (!$rolActual) {
            $_SESSION['error'] = 'El rol que intenta editar no existe.';
            $this->redirect('/seguridad/roles');
        }

        // Si es rol protegido del núcleo, conservar el nombre original
        if ($rolModel->isProtected($idRol)) {
            $nombreRol = $rolActual['nombre_rol'];
        } else {
            if ($rolModel->existsNombre($nombreRol, $idRol)) {
                $_SESSION['error'] = "Ya existe otro rol con el nombre '{$nombreRol}'.";
                $this->redirect('/seguridad/roles');
            }
        }

        $actualizado = $rolModel->update($idRol, [
            'nombre_rol'  => $nombreRol,
            'descripcion' => $descripcion
        ]);

        if ($actualizado) {
            $_SESSION['mensaje'] = "Rol '{$nombreRol}' actualizado exitosamente.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al actualizar el rol.';
        }

        $this->redirect('/seguridad/roles');
    }

    public function permisos()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rolModel     = new Rol();
        $permisoModel = new Permiso();

        $roles = $rolModel->allActive();

        $idRol = isset($_GET['rol']) ? (int)$_GET['rol'] : 0;
        if ($idRol <= 0 && !empty($roles)) {
            $idRol = (int)$roles[0]['id_rol'];
        }

        $rolActual = $rolModel->findById($idRol);
        if (!$rolActual && !empty($roles)) {
            $idRol = (int)$roles[0]['id_rol'];
            $rolActual = $rolModel->findById($idRol);
        }

        $permisosAgrupados = $permisoModel->allGroupedByModule();
        $permisosAsignados = $permisoModel->getIdsByRol($idRol);

        $mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
        $error   = isset($_SESSION['error']) ? $_SESSION['error'] : null;
        unset($_SESSION['mensaje'], $_SESSION['error']);

        $this->view('seguridad/permisos', [
            'title'             => 'Matriz de Permisos — COSMOL Reportes',
            'rolActual'         => $rolActual,
            'roles'             => $roles,
            'permisosAgrupados' => $permisosAgrupados,
            'permisosAsignados' => $permisosAsignados,
            'mensaje'           => $mensaje,
            'error'             => $error
        ], 'main');
    }

    public function guardarPermisos()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idRol    = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;
        $permisos = isset($_POST['permisos']) && is_array($_POST['permisos']) ? $_POST['permisos'] : [];

        if ($idRol <= 0) {
            $_SESSION['error'] = 'Rol no válido para asignar permisos.';
            $this->redirect('/seguridad/roles');
        }

        $permisoModel = new Permiso();
        $guardado = $permisoModel->syncRolPermisos($idRol, $permisos);

        if ($guardado) {
            $_SESSION['mensaje'] = 'Permisos del rol actualizados correctamente.';
        } else {
            $_SESSION['error'] = 'Ocurrió un error al guardar las asignaciones de permisos.';
        }

        $this->redirect("/seguridad/permisos?rol={$idRol}");
    }
}
