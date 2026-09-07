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

        // --- 1. Total Consultas ---
        $db = \App\Core\Database::getInstance();
        $totalConsultas = 0;
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM consulta");
            $totalConsultas = (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            $totalConsultas = 0; // Por si la tabla consulta aún no existe
        }

        // --- 2. Cantidad de Operadores ---
        $totalOperadores = 0;
        $operadoresPorEsp = [];
        try {
            $stmtOp = $db->query("SELECT COUNT(*) FROM usuario u JOIN rol r ON u.id_rol = r.id_rol WHERE r.nombre_rol = 'Operador'");
            $totalOperadores = (int)$stmtOp->fetchColumn();

            $stmtEsp = $db->query("SELECT e.nombre as especialidad, COUNT(u.id_usuario) as total FROM especialidad e LEFT JOIN usuario u ON e.id_especialidad = u.id_especialidad AND u.id_rol = (SELECT id_rol FROM rol WHERE nombre_rol = 'Operador' LIMIT 1) GROUP BY e.id_especialidad, e.nombre ORDER BY e.id_especialidad");
            $operadoresPorEsp = $stmtEsp->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $totalOperadores = 0;
            $operadoresPorEsp = [];
        }

        // --- 3. Trabajos de las APIs (Pendientes y Concluidos) ---
        $totalPendientes = 0;
        $recPendCount    = 0;
        $reclPendCount   = 0;
        $totalConcluidos = 0;
        $recConCount     = 0;
        $reclConCount    = 0;
        
        $apiConfig = require __DIR__ . '/../Config/api.php';
        $clientRec = new \App\Services\ApiClient($apiConfig['reconexiones']['base_url']);
        $clientRecl = new \App\Services\ApiClient($apiConfig['reclamos']['base_url']);

        // Reconexiones Pendientes
        $resRecPend = $clientRec->get('/reconexiones?estado=PENDIENTE');
        if ($resRecPend && isset($resRecPend['datos'])) {
            $recPendCount = count($resRecPend['datos']);
        } elseif (is_array($resRecPend)) {
            $recPendCount = count($resRecPend);
        }

        // Reclamos Pendientes
        $resReclPend = $clientRecl->get('/reclamos?estado=PENDIENTE');
        if ($resReclPend && isset($resReclPend['datos'])) {
            $reclPendCount = count($resReclPend['datos']);
        } elseif (is_array($resReclPend)) {
            $reclPendCount = count($resReclPend);
        }
        $totalPendientes = $recPendCount + $reclPendCount;

        // Reconexiones Concluidas
        $resRecCon = $clientRec->get('/reconexiones?estado=CONCLUIDO');
        if ($resRecCon && isset($resRecCon['datos'])) {
            $recConCount = count($resRecCon['datos']);
        } elseif (is_array($resRecCon)) {
            $recConCount = count($resRecCon);
        }

        // Reclamos Concluidos
        $resReclCon = $clientRecl->get('/reclamos?estado=CONCLUIDO');
        if ($resReclCon && isset($resReclCon['datos'])) {
            $reclConCount = count($resReclCon['datos']);
        } elseif (is_array($resReclCon)) {
            $reclConCount = count($resReclCon);
        }
        $totalConcluidos = $recConCount + $reclConCount;

        $this->view('dashboard/index', [
            'title'                     => 'Dashboard — COSMOL Reportes',
            'usuario'                   => $usuario,
            'totalConsultas'            => $totalConsultas,
            'totalOperadores'           => $totalOperadores,
            'operadoresPorEspecialidad' => $operadoresPorEsp,
            'totalPendientes'           => $totalPendientes,
            'recPendCount'              => $recPendCount,
            'reclPendCount'             => $reclPendCount,
            'totalConcluidos'           => $totalConcluidos,
            'recConCount'               => $recConCount,
            'reclConCount'              => $reclConCount
        ], 'main');
    }
}
