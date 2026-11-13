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

        // --- 0. Capturar Parámetros de Filtrado ---
        $periodo     = isset($_GET['periodo']) ? trim($_GET['periodo']) : 'todos';
        $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fechaFin    = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;

        if ($periodo === 'hoy') {
            $fechaInicio = date('Y-m-d');
            $fechaFin    = date('Y-m-d');
        } elseif ($periodo === '7dias') {
            $fechaInicio = date('Y-m-d', strtotime('-6 days'));
            $fechaFin    = date('Y-m-d');
        } elseif ($periodo === '30dias') {
            $fechaInicio = date('Y-m-d', strtotime('-29 days'));
            $fechaFin    = date('Y-m-d');
        } elseif ($periodo === 'mes') {
            $fechaInicio = date('Y-m-01');
            $fechaFin    = date('Y-m-t');
        } elseif ($periodo === 'personalizado') {
            if (empty($fechaInicio) && empty($fechaFin)) {
                $periodo = 'todos';
            }
        } else {
            $periodo     = 'todos';
            $fechaInicio = null;
            $fechaFin    = null;
        }

        $filtros = [
            'periodo'      => $periodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin'    => $fechaFin,
        ];

        // --- 1. Total Consultas y Evolución según Filtros ---
        $db = \App\Core\Database::getInstance();
        $totalConsultas    = 0;
        $totalConsultasApp = 0;
        $diasSemana        = [];
        $topTiposConsultas = [];

        // Generar puntos temporales para el gráfico de evolución
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $dtStart  = new \DateTime($fechaInicio);
            $dtEnd    = new \DateTime($fechaFin);
            $diffDays = (int)$dtStart->diff($dtEnd)->format('%a');
            
            if ($diffDays <= 45) {
                $interval   = new \DateInterval('P1D');
                $datePeriod = new \DatePeriod($dtStart, $interval, (clone $dtEnd)->modify('+1 day'));
                foreach ($datePeriod as $dt) {
                    $ymd = $dt->format('Y-m-d');
                    $diasSemana[$ymd] = [
                        'fecha'     => $ymd,
                        'label'     => $dt->format('d/m'),
                        'whatsapp'  => 0,
                        'app_movil' => 0,
                        'total'     => 0
                    ];
                }
            } else {
                for ($i = 6; $i >= 0; $i--) {
                    $fechaYmd = date('Y-m-d', strtotime("$fechaFin -$i days"));
                    $diasSemana[$fechaYmd] = [
                        'fecha'     => $fechaYmd,
                        'label'     => date('d/m', strtotime($fechaYmd)),
                        'whatsapp'  => 0,
                        'app_movil' => 0,
                        'total'     => 0
                    ];
                }
            }
        } else {
            // Periodo 'todos': Mostrar ventana de los últimos 7 días con actividad registrada
            $stmtMax = $db->query("SELECT COALESCE(MAX(fecha_consulta), CURRENT_DATE) FROM consulta");
            $maxFecha = $stmtMax->fetchColumn() ?: date('Y-m-d');
            for ($i = 6; $i >= 0; $i--) {
                $fechaYmd = date('Y-m-d', strtotime("$maxFecha -$i days"));
                $diasSemana[$fechaYmd] = [
                    'fecha'     => $fechaYmd,
                    'label'     => date('d/m', strtotime($fechaYmd)),
                    'whatsapp'  => 0,
                    'app_movil' => 0,
                    'total'     => 0
                ];
            }
        }

        try {
            $paramsFechas = [];
            $whereFechas  = "";
            if (!empty($fechaInicio)) {
                $whereFechas .= " AND c.fecha_consulta >= :fecha_inicio";
                $paramsFechas[':fecha_inicio'] = $fechaInicio;
            }
            if (!empty($fechaFin)) {
                $whereFechas .= " AND c.fecha_consulta <= :fecha_fin";
                $paramsFechas[':fecha_fin'] = $fechaFin;
            }

            // Conteo exclusivo de consultas del Chatbot WhatsApp
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM consulta c
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL)
                  AND (u.username != 'app_movil' OR u.username IS NULL)
                  {$whereFechas}
            ");
            $stmt->execute($paramsFechas);
            $totalConsultas = (int)$stmt->fetchColumn();

            // Conteo exclusivo de consultas de la App Móvil (excluye Reclamos)
            $stmtApp = $db->prepare("
                SELECT COUNT(*) FROM consulta c
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil')
                  AND (c.id_tipo != 4 OR c.id_tipo IS NULL)
                  {$whereFechas}
            ");
            $stmtApp->execute($paramsFechas);
            $totalConsultasApp = (int)$stmtApp->fetchColumn();

            // Desglose de consultas diarias por canal para el gráfico
            $fechasKeys = array_keys($diasSemana);
            if (!empty($fechasKeys)) {
                $minDia = min($fechasKeys);
                $maxDia = max($fechasKeys);
                $stmtDias = $db->prepare("
                    SELECT 
                        c.fecha_consulta,
                        SUM(CASE WHEN (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil') AND (c.id_tipo != 4 OR c.id_tipo IS NULL) THEN 1 ELSE 0 END) as app_movil,
                        SUM(CASE WHEN (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL) AND (u.username != 'app_movil' OR u.username IS NULL) THEN 1 ELSE 0 END) as whatsapp
                    FROM consulta c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    WHERE c.fecha_consulta >= :min_dia AND c.fecha_consulta <= :max_dia
                    GROUP BY c.fecha_consulta
                    ORDER BY c.fecha_consulta ASC
                ");
                $stmtDias->execute([':min_dia' => $minDia, ':max_dia' => $maxDia]);
                $resultadosDias = $stmtDias->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($resultadosDias as $row) {
                    $f = $row['fecha_consulta'];
                    if (isset($diasSemana[$f])) {
                        $diasSemana[$f]['whatsapp']  = (int)$row['whatsapp'];
                        $diasSemana[$f]['app_movil'] = (int)$row['app_movil'];
                        $diasSemana[$f]['total']     = (int)$row['whatsapp'] + (int)$row['app_movil'];
                    }
                }
            }

            // Top Tipos de Consultas más Frecuentes
            $stmtTop = $db->prepare("
                SELECT tc.nombre, COUNT(c.id_consulta) as total
                FROM tipo_consulta tc
                JOIN consulta c ON tc.id_tipo = c.id_tipo
                WHERE 1=1 {$whereFechas}
                GROUP BY tc.id_tipo, tc.nombre
                ORDER BY total DESC
                LIMIT 6
            ");
            $stmtTop->execute($paramsFechas);
            $topTiposConsultas = $stmtTop->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            $totalConsultas    = 0;
            $totalConsultasApp = 0;
            $topTiposConsultas = [];
        }

        // --- 2. Cantidad de Operadores y Distribución ---
        $totalOperadores = 0;
        $operadoresPorEsp = [];
        $mapOpPorEsp = [
            'Reconexión'                => 0,
            'Maestro de alcantarillado' => 0,
            'Agua Potable'              => 0
        ];

        try {
            $stmtOp = $db->query("SELECT COUNT(*) FROM usuario u JOIN rol r ON u.id_rol = r.id_rol WHERE r.nombre_rol = 'Operador'");
            $totalOperadores = (int)$stmtOp->fetchColumn();

            $stmtEsp = $db->query("
                SELECT e.nombre as especialidad, COUNT(u.id_usuario) as total 
                FROM especialidad e 
                LEFT JOIN usuario u ON e.id_especialidad = u.id_especialidad 
                    AND u.id_rol = (SELECT id_rol FROM rol WHERE nombre_rol = 'Operador' LIMIT 1) 
                GROUP BY e.id_especialidad, e.nombre 
                ORDER BY e.id_especialidad
            ");
            $operadoresPorEsp = $stmtEsp->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($operadoresPorEsp as $item) {
                if (isset($mapOpPorEsp[$item['especialidad']])) {
                    $mapOpPorEsp[$item['especialidad']] = (int)$item['total'];
                }
            }
        } catch (\Exception $e) {
            $totalOperadores = 0;
            $operadoresPorEsp = [];
        }

        // --- 3. Trabajos de las APIs (Pendientes y Concluidos) en Paralelo ---
        $totalPendientes        = 0;
        $recPendCount           = 0;
        $reclPendCount          = 0;
        $reclAlcantarilladoPend = 0;
        $reclAguaPotablePend    = 0;

        $totalConcluidos        = 0;
        $recConCount            = 0;
        $reclConCount           = 0;
        $reclAlcantarilladoCon  = 0;
        $reclAguaPotableCon     = 0;
        
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
        $datosRecPend = isset($resRecPend['datos']) ? $resRecPend['datos'] : (is_array($resRecPend) ? $resRecPend : []);
        $recPendCount = count($datosRecPend);

        // Reclamos Pendientes con desglose por tipo
        $resReclPend = isset($respuestasMulti['recl_pend']) ? $respuestasMulti['recl_pend'] : null;
        $datosReclPend = isset($resReclPend['datos']) ? $resReclPend['datos'] : (is_array($resReclPend) ? $resReclPend : []);
        $reclPendCount = count($datosReclPend);
        foreach ($datosReclPend as $r) {
            $t = isset($r['id_tipo_reclamo']) ? (int)$r['id_tipo_reclamo'] : 0;
            if ($t === 3) {
                $reclAlcantarilladoPend++;
            } elseif ($t === 2) {
                $reclAguaPotablePend++;
            }
        }
        $totalPendientes = $recPendCount + $reclPendCount;

        // Reconexiones Concluidas
        $resRecCon = isset($respuestasMulti['rec_con']) ? $respuestasMulti['rec_con'] : null;
        $datosRecCon = isset($resRecCon['datos']) ? $resRecCon['datos'] : (is_array($resRecCon) ? $resRecCon : []);
        $recConCount = count($datosRecCon);

        // Reclamos Concluidos con desglose por tipo
        $resReclCon = isset($respuestasMulti['recl_con']) ? $respuestasMulti['recl_con'] : null;
        $datosReclCon = isset($resReclCon['datos']) ? $resReclCon['datos'] : (is_array($resReclCon) ? $resReclCon : []);
        $reclConCount = count($datosReclCon);
        foreach ($datosReclCon as $r) {
            $t = isset($r['id_tipo_reclamo']) ? (int)$r['id_tipo_reclamo'] : 0;
            if ($t === 3) {
                $reclAlcantarilladoCon++;
            } elseif ($t === 2) {
                $reclAguaPotableCon++;
            }
        }
        $totalConcluidos = $recConCount + $reclConCount;

        // Tasa de resolución global (%)
        $totalOperaciones = $totalPendientes + $totalConcluidos;
        $tasaResolucion = $totalOperaciones > 0 ? round(($totalConcluidos / $totalOperaciones) * 100, 1) : 0;

        // Filtrado por fecha para trabajos si se especificó un rango
        if (!empty($fechaInicio) || !empty($fechaFin)) {
            $filtrarFecha = function ($item, $campo) use ($fechaInicio, $fechaFin) {
                if (!isset($item[$campo]) || empty($item[$campo])) {
                    return true;
                }
                $f = substr(trim($item[$campo]), 0, 10);
                if (!empty($fechaInicio) && $f < $fechaInicio) return false;
                if (!empty($fechaFin) && $f > $fechaFin) return false;
                return true;
            };

            $datosRecCon = array_values(array_filter($datosRecCon, function ($r) use ($filtrarFecha) {
                return $filtrarFecha($r, 'fecha_reconexion');
            }));
            $datosReclCon = array_values(array_filter($datosReclCon, function ($r) use ($filtrarFecha) {
                return $filtrarFecha($r, 'fecha_trabajo');
            }));
            $datosRecPend = array_values(array_filter($datosRecPend, function ($r) use ($filtrarFecha) {
                return $filtrarFecha($r, 'fecha_registro');
            }));
            $datosReclPend = array_values(array_filter($datosReclPend, function ($r) use ($filtrarFecha) {
                return $filtrarFecha($r, 'fecha_registro');
            }));

            // Recalcular conteos según el filtro temporal
            $recPendCount = count($datosRecPend);
            $reclPendCount = count($datosReclPend);
            $reclAlcantarilladoPend = 0;
            $reclAguaPotablePend = 0;
            foreach ($datosReclPend as $r) {
                $t = isset($r['id_tipo_reclamo']) ? (int)$r['id_tipo_reclamo'] : 0;
                if ($t === 3) $reclAlcantarilladoPend++;
                elseif ($t === 2) $reclAguaPotablePend++;
            }
            $totalPendientes = $recPendCount + $reclPendCount;

            $recConCount = count($datosRecCon);
            $reclConCount = count($datosReclCon);
            $reclAlcantarilladoCon = 0;
            $reclAguaPotableCon = 0;
            foreach ($datosReclCon as $r) {
                $t = isset($r['id_tipo_reclamo']) ? (int)$r['id_tipo_reclamo'] : 0;
                if ($t === 3) $reclAlcantarilladoCon++;
                elseif ($t === 2) $reclAguaPotableCon++;
            }
            $totalConcluidos = $recConCount + $reclConCount;

            $totalOperaciones = $totalPendientes + $totalConcluidos;
            $tasaResolucion = $totalOperaciones > 0 ? round(($totalConcluidos / $totalOperaciones) * 100, 1) : 0;
        }

        // Trabajos en Seguimiento Local (NO CONCLUIDO)
        $totalNoConcluidos = 0;
        try {
            $stmtNC = $db->query("SELECT COUNT(*) FROM trabajo_seguimiento WHERE estado_interno = 'NO CONCLUIDO'");
            $totalNoConcluidos = (int)$stmtNC->fetchColumn();
        } catch (\Exception $e) {
            $totalNoConcluidos = 0;
        }

        // Métricas de Teléfonos y Control de Tráfico del Bot
        $reporteModel       = new \App\Models\Reporte();
        $totalNumerosUnicos = $reporteModel->getTotalNumerosUnicos();
        $numerosMasActivos  = $reporteModel->getNumerosMasActivos(5);

        // --- 4. Cálculo de Tiempos Promedio de Atención y Resolución ---
        // Se calcula el tiempo promedio de resolución por trabajo individual (un solo reclamo / una reconexión).
        // Para evitar distorsión por tickets estáticos de prueba (backlog inactivo de desarrollo que permaneció semanas
        // o meses antes de cerrarse masivamente), se utiliza una ventana operativa realista acorde al SLA del servicio:
        // - Reconexiones: SLA operativo oficial de 24h (ventana de ciclo operativo de hasta 48h).
        // - Reclamos: Ventana de hasta 96h (4 días) para intervenciones físicas de red (Agua Potable y Alcantarillado).
        $mapaUsuarios = [];
        try {
            $stmtUsers = $db->query("SELECT id_usuario, username FROM usuario");
            while ($uRow = $stmtUsers->fetch(\PDO::FETCH_ASSOC)) {
                $mapaUsuarios[(int)$uRow['id_usuario']] = $uRow['username'];
            }
        } catch (\Exception $e) {
            $mapaUsuarios = [];
        }

        $formatearHorasLegible = function ($horasDecimal) {
            if ($horasDecimal <= 0) return '0h 0m';
            $minutosTotal = (int)round($horasDecimal * 60);
            $dias = (int)floor($minutosTotal / (24 * 60));
            $restoMinutos = $minutosTotal % (24 * 60);
            $horas = (int)floor($restoMinutos / 60);
            $minutos = $restoMinutos % 60;
            
            if ($dias > 0) {
                return "{$dias}d {$horas}h" . ($minutos > 0 ? " {$minutos}m" : "");
            }
            return "{$horas}h {$minutos}m";
        };

        $MAX_HORAS_REC  = 48.0;
        $MAX_HORAS_RECL = 96.0;

        // Recopilación de diferencias brutas para Reconexiones
        $rawRec = [];
        foreach ($datosRecCon as $item) {
            $fReg = isset($item['fecha_registro']) ? $item['fecha_registro'] : '';
            $fFin = !empty($item['fecha_reconexion']) ? $item['fecha_reconexion'] : (!empty($item['fecha_actualizacion']) ? $item['fecha_actualizacion'] : '');
            if (!empty($fReg) && !empty($fFin)) {
                $t1 = strtotime($fReg);
                $t2 = strtotime($fFin);
                if ($t1 && $t2 && $t2 >= $t1) {
                    $diffHoras = round(($t2 - $t1) / 3600, 2);
                    if ($diffHoras > 0) {
                        $rawRec[] = [
                            'diff' => $diffHoras,
                            'item' => $item
                        ];
                    }
                }
            }
        }

        // Recopilación de diferencias brutas para Reclamos
        $rawRecl = [];
        foreach ($datosReclCon as $item) {
            $fReg = isset($item['fecha_registro']) ? $item['fecha_registro'] : '';
            $fFin = !empty($item['fecha_trabajo']) ? $item['fecha_trabajo'] : (!empty($item['fecha_actualizacion']) ? $item['fecha_actualizacion'] : '');
            if (!empty($fReg) && !empty($fFin)) {
                $t1 = strtotime($fReg);
                $t2 = strtotime($fFin);
                if ($t1 && $t2 && $t2 >= $t1) {
                    $diffHoras = round(($t2 - $t1) / 3600, 2);
                    if ($diffHoras > 0) {
                        $rawRecl[] = [
                            'diff' => $diffHoras,
                            'item' => $item
                        ];
                    }
                }
            }
        }

        $tiemposRec      = [];
        $tiemposRecl     = [];
        $tiemposReclAgua = [];
        $tiemposReclAlc  = [];
        $tiemposTecnicos = [];

        // Filtrado operativo de Reconexiones (excluyendo outliers masivos de backlog)
        $recFiltrados = array_filter($rawRec, function ($r) use ($MAX_HORAS_REC) {
            return $r['diff'] <= $MAX_HORAS_REC;
        });
        // Fallback defensivo: si no hay casos <= 48h pero sí hubo concluidos, tomar los menores a 168h (1 semana)
        if (empty($recFiltrados) && !empty($rawRec)) {
            $recFiltrados = array_filter($rawRec, function ($r) {
                return $r['diff'] <= 168.0;
            });
        }

        foreach ($recFiltrados as $r) {
            $diffHoras = $r['diff'];
            $item      = $r['item'];
            $tiemposRec[] = $diffHoras;

            $uId = isset($item['usuario_reconexion']) ? (int)$item['usuario_reconexion'] : 0;
            $nombreOp = isset($mapaUsuarios[$uId]) ? $mapaUsuarios[$uId] : ($uId > 0 ? "Operador #{$uId}" : "Sin asignar");
            if (!isset($tiemposTecnicos[$nombreOp])) {
                $tiemposTecnicos[$nombreOp] = [
                    'nombre'       => $nombreOp,
                    'tiempos'      => [],
                    'reconexiones' => 0,
                    'reclamos'     => 0
                ];
            }
            $tiemposTecnicos[$nombreOp]['tiempos'][] = $diffHoras;
            $tiemposTecnicos[$nombreOp]['reconexiones']++;
        }

        // Filtrado operativo de Reclamos (separando por tipo)
        $reclFiltrados = array_filter($rawRecl, function ($r) use ($MAX_HORAS_RECL) {
            return $r['diff'] <= $MAX_HORAS_RECL;
        });
        // Fallback defensivo: si no hay casos <= 96h pero sí hubo concluidos, tomar los menores a 168h (1 semana)
        if (empty($reclFiltrados) && !empty($rawRecl)) {
            $reclFiltrados = array_filter($rawRecl, function ($r) {
                return $r['diff'] <= 168.0;
            });
        }

        foreach ($reclFiltrados as $r) {
            $diffHoras = $r['diff'];
            $item      = $r['item'];
            $tiemposRecl[] = $diffHoras;

            $idTipo = isset($item['id_tipo_reclamo']) ? (int)$item['id_tipo_reclamo'] : 0;
            if ($idTipo === 2) {
                $tiemposReclAgua[] = $diffHoras;
            } elseif ($idTipo === 3) {
                $tiemposReclAlc[] = $diffHoras;
            }

            $uId = !empty($item['usuario_conclucion']) ? (int)$item['usuario_conclucion'] : (!empty($item['usuario_reclamo']) ? (int)$item['usuario_reclamo'] : 0);
            $nombreOp = isset($mapaUsuarios[$uId]) ? $mapaUsuarios[$uId] : ($uId > 0 ? "Operador #{$uId}" : "Sin asignar");
            if (!isset($tiemposTecnicos[$nombreOp])) {
                $tiemposTecnicos[$nombreOp] = [
                    'nombre'       => $nombreOp,
                    'tiempos'      => [],
                    'reconexiones' => 0,
                    'reclamos'     => 0
                ];
            }
            $tiemposTecnicos[$nombreOp]['tiempos'][] = $diffHoras;
            $tiemposTecnicos[$nombreOp]['reclamos']++;
        }

        // Promedios consolidados por servicio (tiempo de resolución de un solo trabajo)
        $promRec      = count($tiemposRec) > 0 ? round(array_sum($tiemposRec) / count($tiemposRec), 1) : 0;
        $promRecl     = count($tiemposRecl) > 0 ? round(array_sum($tiemposRecl) / count($tiemposRecl), 1) : 0;
        $promReclAgua = count($tiemposReclAgua) > 0 ? round(array_sum($tiemposReclAgua) / count($tiemposReclAgua), 1) : 0;
        $promReclAlc  = count($tiemposReclAlc) > 0 ? round(array_sum($tiemposReclAlc) / count($tiemposReclAlc), 1) : 0;

        $tiemposTodos = array_merge($tiemposRec, $tiemposRecl);
        $promGlobal   = count($tiemposTodos) > 0 ? round(array_sum($tiemposTodos) / count($tiemposTodos), 1) : 0;

        // Consolidación por técnico
        $tecnicosFinal = [];
        foreach ($tiemposTecnicos as $nombre => $datos) {
            $cnt = count($datos['tiempos']);
            $prom = $cnt > 0 ? round(array_sum($datos['tiempos']) / $cnt, 1) : 0;
            $tecnicosFinal[] = [
                'nombre'        => $nombre,
                'promedioHoras' => $prom,
                'promedioTexto' => $formatearHorasLegible($prom),
                'totalCasos'    => $cnt,
                'reconexiones'  => $datos['reconexiones'],
                'reclamos'      => $datos['reclamos'],
            ];
        }

        // Ordenar técnicos por promedio ascendente
        usort($tecnicosFinal, function ($a, $b) {
            if ($a['promedioHoras'] == $b['promedioHoras']) return 0;
            return ($a['promedioHoras'] < $b['promedioHoras']) ? -1 : 1;
        });

        $metricasTiempos = [
            'promedioHorasRec'       => $promRec,
            'promedioHorasRecl'      => $promRecl,
            'promedioHorasReclAgua'  => $promReclAgua,
            'promedioHorasReclAlc'   => $promReclAlc,
            'promedioHorasGlobal'    => $promGlobal,
            'promedioRecTexto'       => $formatearHorasLegible($promRec),
            'promedioReclTexto'      => $formatearHorasLegible($promRecl),
            'promedioReclAguaTexto'  => $formatearHorasLegible($promReclAgua),
            'promedioReclAlcTexto'   => $formatearHorasLegible($promReclAlc),
            'promedioGlobalTexto'    => $formatearHorasLegible($promGlobal),
            'totalCasosRec'          => count($tiemposRec),
            'totalCasosRecl'         => count($tiemposRecl),
            'totalCasosReclAgua'     => count($tiemposReclAgua),
            'totalCasosReclAlc'      => count($tiemposReclAlc),
            'totalCasosGlobal'       => count($tiemposTodos),
            'tecnicos'               => $tecnicosFinal
        ];

        $this->view('dashboard/dashboard', [
            'title'                     => 'Panel de Control y Métricas — COSMOL Reportes',
            'usuario'                   => $usuario,
            'filtros'                   => $filtros,
            'totalConsultas'            => $totalConsultas,
            'totalConsultasApp'         => $totalConsultasApp,
            'totalNumerosUnicos'        => $totalNumerosUnicos,
            'numerosMasActivos'         => $numerosMasActivos,
            'totalOperadores'           => $totalOperadores,
            'operadoresPorEspecialidad' => $operadoresPorEsp,
            'mapOpPorEsp'               => $mapOpPorEsp,
            'totalPendientes'           => $totalPendientes,
            'recPendCount'              => $recPendCount,
            'reclPendCount'             => $reclPendCount,
            'reclAlcantarilladoPend'    => $reclAlcantarilladoPend,
            'reclAguaPotablePend'       => $reclAguaPotablePend,
            'totalConcluidos'           => $totalConcluidos,
            'recConCount'               => $recConCount,
            'reclConCount'              => $reclConCount,
            'reclAlcantarilladoCon'     => $reclAlcantarilladoCon,
            'reclAguaPotableCon'        => $reclAguaPotableCon,
            'tasaResolucion'            => $tasaResolucion,
            'totalNoConcluidos'         => $totalNoConcluidos,
            'diasSemana'                => array_values($diasSemana),
            'topTiposConsultas'         => $topTiposConsultas,
            'metricasTiempos'           => $metricasTiempos,
            'apiStatus'                 => [
                'reconexiones' => $resRecPend !== null,
                'reclamos'     => $resReclPend !== null,
                'db'           => true
            ]
        ], 'main');
    }
}
