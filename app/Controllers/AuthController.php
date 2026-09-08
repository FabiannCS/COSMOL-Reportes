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
            $permisoModel = new \App\Models\Permiso();
            $permisos = $permisoModel->getClavesByRol($user['id_rol']);

            // Guardar datos del usuario en sesión (sin el hash de contraseña)
            $_SESSION['usuario'] = [
                'id_usuario' => $user['id_usuario'],
                'username'   => $user['username'],
                'id_rol'     => $user['id_rol'],
                'nombre_rol' => $user['nombre_rol'],
                'permisos'   => $permisos
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

        // --- 1. Total Consultas y Evolución (Últimos 7 días) ---
        $db = \App\Core\Database::getInstance();
        $totalConsultas = 0;
        $consultas7Dias = [];
        
        // Inicializar últimos 7 días con 0
        for ($i = 6; $i >= 0; $i--) {
            $consultas7Dias[date('Y-m-d', strtotime("-$i days"))] = 0;
        }

        try {
            $stmt = $db->query("SELECT COUNT(*) FROM consulta");
            $totalConsultas = (int)$stmt->fetchColumn();

            $stmtDias = $db->query("
                SELECT fecha_consulta, COUNT(*) as total 
                FROM consulta 
                WHERE fecha_consulta >= CURRENT_DATE - INTERVAL '6 days' 
                GROUP BY fecha_consulta 
                ORDER BY fecha_consulta ASC
            ");
            
            $resultadosDias = $stmtDias->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($resultadosDias as $row) {
                if (isset($consultas7Dias[$row['fecha_consulta']])) {
                    $consultas7Dias[$row['fecha_consulta']] = (int)$row['total'];
                }
            }
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

        // --- 3. Trabajos de las APIs (Pendientes y Concluidos) en Paralelo ---
        $totalPendientes = 0;
        $recPendCount    = 0;
        $reclPendCount   = 0;
        $totalConcluidos = 0;
        $recConCount     = 0;
        $reclConCount    = 0;
        
        $apiConfig = require __DIR__ . '/../Config/api.php';
        $clientRec = new \App\Services\ApiClient($apiConfig['reconexiones']['base_url']);
        $clientRecl = new \App\Services\ApiClient($apiConfig['reclamos']['base_url']);

        // Ejecución concurrente de las 4 peticiones a las APIs externas
        $respuestasMulti = \App\Services\ApiClient::getMultiFromClients([
            'rec_pend'  => [$clientRec, '/reconexiones?estado=PENDIENTE'],
            'recl_pend' => [$clientRecl, '/reclamos?estado=PENDIENTE'],
            'rec_con'   => [$clientRec, '/reconexiones?estado=CONCLUIDA'],
            'recl_con'  => [$clientRecl, '/reclamos?estado=CONCLUIDO'],
        ]);

        // Reconexiones Pendientes
        $resRecPend = isset($respuestasMulti['rec_pend']) ? $respuestasMulti['rec_pend'] : null;
        if ($resRecPend && isset($resRecPend['datos'])) {
            $recPendCount = count($resRecPend['datos']);
        } elseif (is_array($resRecPend)) {
            $recPendCount = count($resRecPend);
        }

        // Reclamos Pendientes
        $resReclPend = isset($respuestasMulti['recl_pend']) ? $respuestasMulti['recl_pend'] : null;
        if ($resReclPend && isset($resReclPend['datos'])) {
            $reclPendCount = count($resReclPend['datos']);
        } elseif (is_array($resReclPend)) {
            $reclPendCount = count($resReclPend);
        }
        $totalPendientes = $recPendCount + $reclPendCount;

        // Reconexiones Concluidas
        $resRecCon = isset($respuestasMulti['rec_con']) ? $respuestasMulti['rec_con'] : null;
        if ($resRecCon && isset($resRecCon['datos'])) {
            $recConCount = count($resRecCon['datos']);
        } elseif (is_array($resRecCon)) {
            $recConCount = count($resRecCon);
        }

        // Reclamos Concluidos
        $resReclCon = isset($respuestasMulti['recl_con']) ? $respuestasMulti['recl_con'] : null;
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
            'reclConCount'              => $reclConCount,
            'consultas7Dias'            => $consultas7Dias
        ], 'main');
    }
}
