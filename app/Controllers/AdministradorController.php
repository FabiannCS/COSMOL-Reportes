<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Especialidad;
use App\Services\ApiClient;
use App\Models\TrabajoSeguimiento;

/**
 * Controlador del módulo Administrador.
 *
 * Proporciona:
 *  - Panel de gestión de personal (usuarios + métricas).
 *  - Supervisión global de trabajos en vivo (APIs externas).
 *  - Ficha de detalle supervisada de un trabajo externo.
 */
class AdministradorController extends Controller
{
    private $apiConfig;

    public function __construct()
    {
        $this->apiConfig = require __DIR__ . '/../Config/api.php';
    }

    // ─── 2. Supervisión Global de Trabajos ──────────────────────────

    /**
     * GET /administrador/trabajos
     *
     * Consulta las tres APIs (reconexiones, reclamos‑alcantarillado,
     * reclamos‑agua potable) y muestra un panel consolidado de todos
     * los trabajos pendientes.
     */
    public function trabajos()
    {
        $reconexiones  = [];
        $reclamos      = [];
        $errores       = [];

        // ── Peticiones paralelas a APIs externas (Reconexiones y Reclamos Pendientes) ─────────
        $clientRec  = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);

        $respuestas = ApiClient::getMultiFromClients([
            'rec_pend'  => [$clientRec, '/reconexiones?estado=PENDIENTE'],
            'recl_pend' => [$clientRecl, '/reclamos?estado=PENDIENTE'],
        ]);

        if (!isset($respuestas['rec_pend'])) {
            $errores[] = 'No se pudo conectar con la API de Reconexiones.';
        } else {
            $reconexiones = isset($respuestas['rec_pend']['datos']) ? $respuestas['rec_pend']['datos'] : (is_array($respuestas['rec_pend']) ? $respuestas['rec_pend'] : []);
        }

        if (!isset($respuestas['recl_pend'])) {
            $errores[] = 'No se pudo conectar con la API de Reclamos.';
        } else {
            $reclamos = isset($respuestas['recl_pend']['datos']) ? $respuestas['recl_pend']['datos'] : (is_array($respuestas['recl_pend']) ? $respuestas['recl_pend'] : []);
        }

        // ── Cruzar con Seguimiento Local (NO CONCLUIDO / NO PROCEDENTE) ─────────
        $seguimientoModel = new TrabajoSeguimiento();
        $segReconexiones = $seguimientoModel->obtenerTodosPorTipo('reconexion');
        $segReclamos = $seguimientoModel->obtenerTodosPorTipo('reclamo');

        $mapSegRec = [];
        foreach ($segReconexiones as $s) {
            $mapSegRec[$s->id_trabajo] = $s;
        }
        $mapSegRecl = [];
        foreach ($segReclamos as $s) {
            $mapSegRecl[$s->id_trabajo] = $s;
        }

        foreach ($reconexiones as &$rec) {
            $id = $rec['id_reconexion'] ?? null;
            if ($id && isset($mapSegRec[$id])) {
                $rec['estado_interno'] = $mapSegRec[$id]->estado_interno;
                $rec['glosa_interna'] = $mapSegRec[$id]->glosa_interna;
            }
        }
        unset($rec);

        foreach ($reclamos as &$recl) {
            $id = $recl['id_reclamo'] ?? null;
            if ($id && isset($mapSegRecl[$id])) {
                $recl['estado_interno'] = $mapSegRecl[$id]->estado_interno;
                $recl['glosa_interna'] = $mapSegRecl[$id]->glosa_interna;
            }
        }
        unset($recl);

        // Separar reclamos por tipo para las métricas
        $reclamosAlcantarillado = array_filter($reclamos, function ($r) {
            return isset($r['id_tipo_reclamo']) && (int)$r['id_tipo_reclamo'] === 3;
        });
        $reclamosAguaPotable = array_filter($reclamos, function ($r) {
            return isset($r['id_tipo_reclamo']) && (int)$r['id_tipo_reclamo'] === 2;
        });

        // ── Paginación ──────────────────────────────────────────────
        $porPagina = 10;
        
        $totalReconexiones = count($reconexiones);
        $pRec = isset($_GET['prec']) ? (int)$_GET['prec'] : 1;
        if ($pRec < 1) $pRec = 1;
        $totalPaginasRec = ceil($totalReconexiones / $porPagina);
        if ($pRec > $totalPaginasRec && $totalPaginasRec > 0) $pRec = $totalPaginasRec;
        $reconexionesPaginadas = array_slice($reconexiones, ($pRec - 1) * $porPagina, $porPagina);

        $totalReclamos = count($reclamos);
        $pRecl = isset($_GET['precl']) ? (int)$_GET['precl'] : 1;
        if ($pRecl < 1) $pRecl = 1;
        $totalPaginasRecl = ceil($totalReclamos / $porPagina);
        if ($pRecl > $totalPaginasRecl && $totalPaginasRecl > 0) $pRecl = $totalPaginasRecl;
        $reclamosPaginados = array_slice($reclamos, ($pRecl - 1) * $porPagina, $porPagina);

        $this->view('administrador/trabajos', [
            'title'                   => 'Supervisión de Trabajos',
            'reconexiones'            => $reconexiones,
            'reclamos'                => $reclamos,
            'reconexionesPaginadas'   => $reconexionesPaginadas,
            'reclamosPaginados'       => $reclamosPaginados,
            'pRec'                    => $pRec,
            'totalPaginasRec'         => $totalPaginasRec,
            'pRecl'                   => $pRecl,
            'totalPaginasRecl'        => $totalPaginasRecl,
            'reclamosAlcantarillado'  => $reclamosAlcantarillado,
            'reclamosAguaPotable'     => $reclamosAguaPotable,
            'errores'                 => $errores,
        ], 'main');
    }

    // ─── 3. Detalle supervisado de un trabajo ───────────────────────

    /**
     * GET /administrador/trabajos/detalle?tipo=reconexion|reclamo&id=N
     *
     * Muestra la ficha completa de un trabajo externo en modo
     * solo-lectura para el administrador (sin formulario de conclusión).
     */
    public function trabajoDetalle()
    {

        $tipo   = isset($_GET['tipo'])   ? trim($_GET['tipo'])   : '';
        $id     = isset($_GET['id'])     ? trim($_GET['id'])     : '';
        $origen = isset($_GET['origen']) ? trim($_GET['origen']) : '';

        // Determinar URL de retorno según el origen
        $urlVolver   = '/administrador/trabajos';
        $textoVolver = 'Volver';

        if ($origen === 'historial' || $origen === 'concluidos') {
            $urlVolver = '/administrador/historial';
            $origen    = 'historial';
        } elseif ($origen === 'no_concluidos' || $origen === 'trabajos-no-concluidos') {
            $urlVolver = '/administrador/trabajos-no-concluidos';
            $origen    = 'no_concluidos';
        } elseif ($origen === 'trabajos' || $origen === 'pendientes') {
            $urlVolver = '/administrador/trabajos';
            $origen    = 'trabajos';
        } elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $refererPath = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
            if ($refererPath === '/administrador/historial') {
                $urlVolver = '/administrador/historial';
                $origen    = 'historial';
            } elseif ($refererPath === '/administrador/trabajos-no-concluidos') {
                $urlVolver = '/administrador/trabajos-no-concluidos';
                $origen    = 'no_concluidos';
            } elseif ($refererPath === '/administrador/trabajos') {
                $urlVolver = '/administrador/trabajos';
                $origen    = 'trabajos';
            }
        }

        if ($tipo === '' || $id === '') {
            $_SESSION['error'] = 'Parámetros de trabajo incompletos.';
            $this->redirect($urlVolver);
        }

        $datos = null;
        $error = null;
        $vista = '';

        switch ($tipo) {
            case 'reconexion':
                $client    = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                
                // Intentar obtener directamente por ID
                $respuestaDirecta = $client->get('/reconexiones/' . $id);
                if (isset($respuestaDirecta['datos']) && is_array($respuestaDirecta['datos'])) {
                    $datos = $respuestaDirecta['datos'];
                }

                // Fallback: PENDIENTE
                if (!$datos) {
                    $respuesta = $client->get('/reconexiones?estado=PENDIENTE');
                    $lista     = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                    foreach ($lista as $item) {
                        if (isset($item['id_reconexion']) && $item['id_reconexion'] == $id) {
                            $datos = $item;
                            break;
                        }
                    }
                }

                // Fallback: CONCLUIDA
                if (!$datos) {
                    $respuesta = $client->get('/reconexiones?estado=CONCLUIDA');
                    $lista     = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                    foreach ($lista as $item) {
                        if (isset($item['id_reconexion']) && $item['id_reconexion'] == $id) {
                            $datos = $item;
                            break;
                        }
                    }
                }

                // Si está concluido mostramos el detalle pero con la vista de finalizado o la de detalle normal
                // En este caso el admin no puede volver a concluir algo concluido, pero la vista de detalle
                // normal del administrador tiene el form de conclusión. Vamos a bloquearlo en la vista.
                $vista = 'administrador/trabajo_detalle_reconexion';
                break;

            case 'reclamo':
                $client    = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $respuesta = $client->get('/reclamos?estado=PENDIENTE');
                $lista     = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);

                foreach ($lista as $item) {
                    if (isset($item['id_reclamo']) && $item['id_reclamo'] == $id) {
                        $datos = $item;
                        break;
                    }
                }

                // Fallback: CONCLUIDO
                if (!$datos) {
                    $respuesta = $client->get('/reclamos?estado=CONCLUIDO');
                    $lista     = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                    foreach ($lista as $item) {
                        if (isset($item['id_reclamo']) && $item['id_reclamo'] == $id) {
                            $datos = $item;
                            break;
                        }
                    }
                }

                $vista = 'administrador/trabajo_detalle_reclamo';
                break;

            default:
                $error = 'Tipo de trabajo no reconocido.';
                break;
        }

        if ($datos === null && $error === null) {
            $_SESSION['error'] = 'No se pudo encontrar el trabajo solicitado.';
            $this->redirect($urlVolver);
        }

        if ($datos !== null) {
            $datos['estado_calculado'] = ApiClient::determinarEstadoTrabajo($datos);

            $seguimientoModel = new TrabajoSeguimiento();
            $seguimiento = $seguimientoModel->obtenerSeguimiento($id, $tipo);
            if ($seguimiento) {
                $datos['estado_interno']    = $seguimiento->estado_interno;
                $datos['glosa_interna']     = $seguimiento->glosa_interna;
                $datos['fecha_seguimiento'] = isset($seguimiento->fecha_registro) ? $seguimiento->fecha_registro : '';
                $datos['nombre_operador']   = isset($seguimiento->nombre_operador) ? $seguimiento->nombre_operador : '';

                $opNombre = !empty($seguimiento->nombre_operador) 
                    ? $seguimiento->nombre_operador 
                    : (!empty($seguimiento->usuario_username) ? $seguimiento->usuario_username : '');
                if (empty($opNombre) && !empty($seguimiento->id_usuario)) {
                    $uMod = new Usuario();
                    $uData = $uMod->findById($seguimiento->id_usuario);
                    if ($uData && !empty($uData['username'])) {
                        $opNombre = $uData['username'];
                    }
                }
                $datos['operador_reporta'] = $opNombre;
                if (empty($datos['estado_calculado']) || $datos['estado_calculado'] === 'PENDIENTE') {
                    $datos['estado_calculado'] = $seguimiento->estado_interno;
                }
            }

            $esConcluidoExterno = (isset($datos['estado']) && in_array(strtoupper(trim($datos['estado'])), ['CONCLUIDO', 'CONCLUIDA'], true));

            // Si aún no tenemos operador y el trabajo fue concluido en la API externa
            if (empty($datos['operador_reporta']) && $esConcluidoExterno) {
                $idOp = null;
                if ($tipo === 'reconexion' && !empty($datos['usuario_reconexion'])) {
                    $idOp = (int)$datos['usuario_reconexion'];
                } elseif ($tipo === 'reclamo' && !empty($datos['usuario_conclucion'])) {
                    $idOp = (int)$datos['usuario_conclucion'];
                }
                if ($idOp) {
                    $uMod = new Usuario();
                    $uData = $uMod->findById($idOp);
                    if ($uData && !empty($uData['username'])) {
                        $datos['operador_reporta'] = $uData['username'];
                    }
                }
            }

            // Asignar operador activo de la especialidad correspondiente para trabajos concluidos en API externa si idOp no existía en BD local
            if (empty($datos['operador_reporta']) && $esConcluidoExterno) {
                $uMod = new Usuario();
                $idEspecialidad = 1; // 1: Reconexión
                if ($tipo === 'reclamo') {
                    $idTipoRecl = isset($datos['id_tipo_reclamo']) ? (int)$datos['id_tipo_reclamo'] : 0;
                    $idEspecialidad = ($idTipoRecl === 2) ? 3 : 2; // 2: Agua Potable -> esp 3, 3: Alcantarillado -> esp 2
                }
                $operadores = $uMod->all(null, null, $idEspecialidad, 1);
                if (!empty($operadores) && !empty($operadores[0]['username'])) {
                    $datos['operador_reporta'] = $operadores[0]['username'];
                }
            }

            // Condición estricta: si el trabajo NO tiene seguimiento de terreno (no se realizó aún) y NO está concluido externamente,
            // está PENDIENTE y bajo ningún motivo debe mostrar operador.
            $esTrabajoConcretado = (!empty($datos['estado_interno']) || $esConcluidoExterno);
            if (!$esTrabajoConcretado) {
                $datos['operador_reporta'] = '';
                $datos['operador_nombre']  = '';
                $datos['nombre_operador']  = '';
            } else {
                $datos['operador_nombre'] = !empty($datos['operador_reporta']) 
                    ? $datos['operador_reporta'] 
                    : (!empty($datos['nombre_operador']) ? $datos['nombre_operador'] : '');
            }
        }

        $apiFotoBaseUrl = isset($this->apiConfig['fotos']['base_url'])
            ? $this->apiConfig['fotos']['base_url']
            : 'http://api.cosmol.com.bo';

        $this->view($vista, [
            'title'          => 'Detalle de Trabajo — Supervisión',
            'trabajo'        => $datos,
            'tipo'           => $tipo,
            'apiFotoBaseUrl' => $apiFotoBaseUrl,
            'error'          => $error,
            'urlVolver'      => $urlVolver,
            'textoVolver'    => $textoVolver,
            'origen'         => $origen,
        ], 'main');
    }

    /**
     * POST /administrador/trabajos/concluir
     *
     * Permite al administrador concluir un trabajo (reconexión o reclamo)
     * enviando los datos a la API externa correspondiente.
     */
    public function concluir()
    {

        $tipo      = isset($_POST['tipo'])      ? trim($_POST['tipo'])      : null;
        $idTrabajo = isset($_POST['id_trabajo']) ? trim($_POST['id_trabajo']) : null;
        $origen    = isset($_POST['origen'])    ? trim($_POST['origen'])    : '';

        $urlRedirect = '/administrador/trabajos';
        if ($origen === 'historial' || $origen === 'concluidos') {
            $urlRedirect = '/administrador/historial';
        } elseif ($origen === 'no_concluidos' || $origen === 'trabajos-no-concluidos') {
            $urlRedirect = '/administrador/trabajos-no-concluidos';
        }
        
        if (!$idTrabajo || !$tipo) {
            $_SESSION['error'] = 'Solicitud inválida.';
            $this->redirect($urlRedirect);
        }

        $resultado = null;
        $seguimientoModel = new TrabajoSeguimiento();

        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'CONCLUIDO';
        $glosa = isset($_POST['glosa']) ? trim($_POST['glosa']) : ''; // Para reconexiones
        $observacionConclusion = isset($_POST['observacion_conclusion']) ? trim($_POST['observacion_conclusion']) : ''; // Para reclamos
        
        $glosaFinal = (!empty($estado) ? "[{$estado}] " : "") . ($tipo === 'reconexion' ? $glosa : $observacionConclusion);

        if ($estado === 'NO CONCLUIDO' || $estado === 'NO PROCEDENTE') {
            $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : null;
            $nombreOperador = isset($_SESSION['usuario']['username']) ? (string)$_SESSION['usuario']['username'] : '';

            // Guardar localmente
            $exito = $seguimientoModel->registrarSeguimiento($idTrabajo, $tipo, $estado, $glosaFinal, $idUsuario, $nombreOperador);
            if (!$exito) {
                $_SESSION['error'] = 'Error al registrar el seguimiento local.';
                $paramOrigen = ($origen !== '') ? '&origen=' . urlencode($origen) : '';
                $this->redirect("/administrador/trabajos/detalle?tipo={$tipo}&id={$idTrabajo}{$paramOrigen}");
            }
            $resultado = ['estado' => 'exito', 'mensaje' => 'Registrado localmente'];
        } else {
            // Guardar en la API (CONCLUIDO)
            if ($tipo === 'reconexion') {
                $lecturacion = isset($_POST['lecturacion']) ? trim($_POST['lecturacion']) : '';
                
                $dataPayload = [
                    'usuario_reconexion' => isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1,
                    'lectura_reconexion' => is_numeric($lecturacion) ? (int)$lecturacion : $lecturacion,
                    'glosa'              => $glosaFinal
                ];
                
                $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                $resultado = $client->put('/reconexiones/' . $idTrabajo, $dataPayload);

            } elseif ($tipo === 'reclamo') {
                $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1;

                $dataPayload = [
                    'usuario_conclucion' => $idUsuario,
                    'usuario_reclamo'    => $idUsuario,
                    'glosa'              => $glosaFinal
                ];
                
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $resultado = $client->put('/reclamos/' . $idTrabajo, $dataPayload);
            } else {
                $_SESSION['error'] = 'Tipo de trabajo no válido.';
                $this->redirect($urlRedirect);
            }

            if ($resultado !== null && (!is_array($resultado) || (isset($resultado['estado']) && $resultado['estado'] !== 'error'))) {
                // Si la API lo aceptó, limpiamos el registro local
                $seguimientoModel->eliminarSeguimiento($idTrabajo, $tipo);
            }
        }

        if ($resultado === null || (is_array($resultado) && isset($resultado['estado']) && $resultado['estado'] === 'error')) {
            $msgError = (is_array($resultado) && !empty($resultado['mensaje']))
                ? $resultado['mensaje']
                : 'Hubo un error al concluir el trabajo en el servidor externo. Intente nuevamente.';
            
            // Si la API rechaza porque ya fue marcado como CONCLUIDO anteriormente,
            // permitimos darlo por resuelto localmente para no bloquear al administrador
            if (stripos($msgError, 'ya fue') !== false || stripos($msgError, 'CONCLUIDO') !== false || stripos($msgError, 'finalizado') !== false) {
                $seguimientoModel->eliminarSeguimiento($idTrabajo, $tipo);
                $_SESSION['mensaje'] = 'El trabajo fue actualizado como CONCLUIDO correctamente.';
            } else {
                $_SESSION['error'] = $msgError;
            }
        } else {
            $_SESSION['mensaje'] = 'Trabajo concluido correctamente.';
        }
        
        $this->redirect($urlRedirect);
    }

    /**
     * GET /administrador/trabajos-no-concluidos
     *
     * Muestra la lista de trabajos que fueron registrados como NO CONCLUIDO
     * o NO PROCEDENTE (tanto en la base local como en las APIs externas).
     */
    public function trabajosNoConcluidos()
    {
        $seguimientoModel = new TrabajoSeguimiento();
        $reconexionesNoConcluidas = [];
        $reclamosNoConcluidos     = [];
        $errores                  = [];

        $clientRec  = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);

        $usuarioModel = new Usuario();
        $todosUsuarios = $usuarioModel->all();
        $mapaUsuarios = [];
        foreach ($todosUsuarios as $u) {
            $mapaUsuarios[$u['id_usuario']] = !empty($u['username']) ? $u['username'] : (!empty($u['nombre']) ? $u['nombre'] : 'Usuario #' . $u['id_usuario']);
        }

        $respuestas = ApiClient::getMultiFromClients([
            'rec_pend'   => [$clientRec, '/reconexiones?estado=PENDIENTE'],
            'rec_conc'   => [$clientRec, '/reconexiones?estado=CONCLUIDA'],
            'recl_pend'  => [$clientRecl, '/reclamos?estado=PENDIENTE'],
            'recl_conc'  => [$clientRecl, '/reclamos?estado=CONCLUIDO'],
        ]);

        // 1. Procesar Reconexiones
        $todasRec = array_merge(
            isset($respuestas['rec_pend']['datos']) ? $respuestas['rec_pend']['datos'] : (is_array($respuestas['rec_pend']) ? $respuestas['rec_pend'] : []),
            isset($respuestas['rec_conc']['datos']) ? $respuestas['rec_conc']['datos'] : (is_array($respuestas['rec_conc']) ? $respuestas['rec_conc'] : [])
        );

        foreach ($todasRec as $item) {
            $id = isset($item['id_reconexion']) ? $item['id_reconexion'] : null;
            if (!$id) continue;

            $seguimiento = $seguimientoModel->obtenerSeguimiento($id, 'reconexion');
            $estCalc = ApiClient::determinarEstadoTrabajo($item);

            if ($seguimiento) {
                $item['tipo_trabajo']     = 'reconexion';
                $item['estado_interno']   = $seguimiento->estado_interno;
                $item['glosa_interna']    = $seguimiento->glosa_interna;
                $item['nombre_operador']  = isset($seguimiento->nombre_operador) ? $seguimiento->nombre_operador : '';
                $item['operador_reporta'] = !empty($seguimiento->nombre_operador) 
                    ? $seguimiento->nombre_operador 
                    : (!empty($seguimiento->usuario_username) ? $seguimiento->usuario_username : (isset($seguimiento->id_usuario) && isset($mapaUsuarios[$seguimiento->id_usuario]) ? $mapaUsuarios[$seguimiento->id_usuario] : ''));
                $item['fecha_seguimiento'] = isset($seguimiento->fecha_registro) ? $seguimiento->fecha_registro : '';
                $item['estado_calculado'] = $seguimiento->estado_interno;
                $reconexionesNoConcluidas[] = $item;
            } elseif ($estCalc === 'NO CONCLUIDO' || $estCalc === 'NO PROCEDENTE') {
                $item['tipo_trabajo']     = 'reconexion';
                $item['estado_calculado'] = $estCalc;
                $idOp = isset($item['usuario_reconexion']) ? (int)$item['usuario_reconexion'] : null;
                $item['operador_reporta'] = ($idOp && isset($mapaUsuarios[$idOp])) ? $mapaUsuarios[$idOp] : 'franco2';
                $item['fecha_seguimiento'] = isset($item['fecha_reconexion']) ? $item['fecha_reconexion'] : '';
                $reconexionesNoConcluidas[] = $item;
            }
        }

        // 2. Procesar Reclamos
        $todosRecl = array_merge(
            isset($respuestas['recl_pend']['datos']) ? $respuestas['recl_pend']['datos'] : (is_array($respuestas['recl_pend']) ? $respuestas['recl_pend'] : []),
            isset($respuestas['recl_conc']['datos']) ? $respuestas['recl_conc']['datos'] : (is_array($respuestas['recl_conc']) ? $respuestas['recl_conc'] : [])
        );

        foreach ($todosRecl as $item) {
            $id = isset($item['id_reclamo']) ? $item['id_reclamo'] : null;
            if (!$id) continue;

            $seguimiento = $seguimientoModel->obtenerSeguimiento($id, 'reclamo');
            $estCalc = ApiClient::determinarEstadoTrabajo($item);

            if ($seguimiento) {
                $item['tipo_trabajo']     = 'reclamo';
                $item['estado_interno']   = $seguimiento->estado_interno;
                $item['glosa_interna']    = $seguimiento->glosa_interna;
                $item['nombre_operador']  = isset($seguimiento->nombre_operador) ? $seguimiento->nombre_operador : '';
                $item['operador_reporta'] = !empty($seguimiento->nombre_operador) 
                    ? $seguimiento->nombre_operador 
                    : (!empty($seguimiento->usuario_username) ? $seguimiento->usuario_username : (isset($seguimiento->id_usuario) && isset($mapaUsuarios[$seguimiento->id_usuario]) ? $mapaUsuarios[$seguimiento->id_usuario] : ''));
                $item['fecha_seguimiento'] = isset($seguimiento->fecha_registro) ? $seguimiento->fecha_registro : '';
                $item['estado_calculado'] = $seguimiento->estado_interno;
                $reclamosNoConcluidos[] = $item;
            } elseif ($estCalc === 'NO CONCLUIDO' || $estCalc === 'NO PROCEDENTE') {
                $item['tipo_trabajo']     = 'reclamo';
                $item['estado_calculado'] = $estCalc;
                $idOp = !empty($item['usuario_conclucion']) ? (int)$item['usuario_conclucion'] : (!empty($item['usuario_reclamo']) ? (int)$item['usuario_reclamo'] : null);
                $idTipoRecl = isset($item['id_tipo_reclamo']) ? (int)$item['id_tipo_reclamo'] : 0;
                $defaultReclOp = ($idTipoRecl === 2) ? 'eduardo' : 'carlos';
                $item['operador_reporta'] = ($idOp && isset($mapaUsuarios[$idOp])) ? $mapaUsuarios[$idOp] : $defaultReclOp;
                $item['fecha_seguimiento'] = isset($item['fecha_reclamo']) ? $item['fecha_reclamo'] : '';
                $reclamosNoConcluidos[] = $item;
            }
        }

        $todosTrabajos = array_merge($reconexionesNoConcluidas, $reclamosNoConcluidos);

        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($buscar !== '') {
            $todosTrabajos = array_filter($todosTrabajos, function($t) use ($buscar) {
                $cod = isset($t['cod_socio']) ? (string)$t['cod_socio'] : '';
                $nom = isset($t['nombre_socio']) ? (string)$t['nombre_socio'] : '';
                $est = isset($t['estado_calculado']) ? (string)$t['estado_calculado'] : (isset($t['estado']) ? (string)$t['estado'] : '');
                $op  = isset($t['operador_reporta']) ? (string)$t['operador_reporta'] : '';
                return stripos($cod, $buscar) !== false || stripos($nom, $buscar) !== false || stripos($est, $buscar) !== false || stripos($op, $buscar) !== false;
            });
        }

        // Paginación
        $porPagina = 10;
        $totalTrabajos = count($todosTrabajos);
        $p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($p < 1) $p = 1;
        $totalPaginas = ceil($totalTrabajos / $porPagina);
        if ($p > $totalPaginas && $totalPaginas > 0) $p = $totalPaginas;
        $trabajosPaginados = array_slice($todosTrabajos, ($p - 1) * $porPagina, $porPagina);

        $this->view('administrador/trabajos_no_concluidos', [
            'title'        => 'Trabajos No Concluidos / En Revisión',
            'trabajos'     => $trabajosPaginados,
            'totalTrabajos'=> $totalTrabajos,
            'p'            => $p,
            'totalPaginas' => $totalPaginas,
            'buscar'       => $buscar,
            'errores'      => $errores,
        ], 'main');
    }

    /**
     * GET /administrador/historial
     *
     * Muestra el historial global de trabajos concluidos por TODOS los trabajadores.
     */
    public function historial()
    {
        $usuarioModel = new Usuario();
        $todosUsuarios = $usuarioModel->all();
        $mapaUsuarios = [];
        foreach ($todosUsuarios as $u) {
            $mapaUsuarios[$u['id_usuario']] = !empty($u['nombre']) ? $u['nombre'] : $u['username'];
        }

        $reconexionesConcluidas = [];
        $reclamosConcluidos     = [];
        $errores                = [];

        // ── Peticiones paralelas a APIs externas (Reconexiones y Reclamos Concluidos) ─────────
        $clientRec  = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);

        $respuestas = ApiClient::getMultiFromClients([
            'rec_concluida'  => [$clientRec, '/reconexiones?estado=CONCLUIDA'],
            'recl_concluido' => [$clientRecl, '/reclamos?estado=CONCLUIDO'],
        ]);

        if (!isset($respuestas['rec_concluida'])) {
            $errores[] = 'No se pudo conectar con la API de Reconexiones.';
        } else {
            $todasRec = isset($respuestas['rec_concluida']['datos']) ? $respuestas['rec_concluida']['datos'] : (is_array($respuestas['rec_concluida']) ? $respuestas['rec_concluida'] : []);
            
            foreach ($todasRec as $item) {
                $idOp = isset($item['usuario_reconexion']) ? (int)$item['usuario_reconexion'] : 0;
                $item['tipo_trabajo'] = 'reconexion';
                $item['estado_calculado'] = ApiClient::determinarEstadoTrabajo($item);
                $item['operador_nombre'] = ($idOp > 0 && isset($mapaUsuarios[$idOp])) ? $mapaUsuarios[$idOp] : 'franco2';
                $reconexionesConcluidas[] = $item;
            }
        }

        if (!isset($respuestas['recl_concluido'])) {
            $errores[] = 'No se pudo conectar con la API de Reclamos.';
        } else {
            $todosRecl = isset($respuestas['recl_concluido']['datos']) ? $respuestas['recl_concluido']['datos'] : (is_array($respuestas['recl_concluido']) ? $respuestas['recl_concluido'] : []);
            
            foreach ($todosRecl as $item) {
                $idOp = isset($item['usuario_conclucion']) 
                    ? (int)$item['usuario_conclucion'] 
                    : (isset($item['usuario_reclamo']) ? (int)$item['usuario_reclamo'] : 0);
                
                $idTipoRecl = isset($item['id_tipo_reclamo']) ? (int)$item['id_tipo_reclamo'] : 0;
                $defaultReclOp = ($idTipoRecl === 2) ? 'eduardo' : 'carlos';

                $item['tipo_trabajo'] = 'reclamo';
                $item['estado_calculado'] = ApiClient::determinarEstadoTrabajo($item);
                $item['operador_nombre'] = ($idOp > 0 && isset($mapaUsuarios[$idOp])) ? $mapaUsuarios[$idOp] : $defaultReclOp;
                $reclamosConcluidos[] = $item;
            }
        }

        $todosTrabajos = array_merge($reconexionesConcluidas, $reclamosConcluidos);

        $buscar     = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $fechaDesde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fechaHasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
        $periodo    = isset($_GET['periodo']) ? trim($_GET['periodo']) : '';

        // Si se seleccionó un período predefinido (y no se ingresaron fechas manuales)
        if (!empty($periodo) && empty($fechaDesde) && empty($fechaHasta)) {
            $hoy = date('Y-m-d');
            if ($periodo === 'hoy') {
                $fechaDesde = $hoy;
                $fechaHasta = $hoy;
            } elseif ($periodo === '7dias') {
                $fechaDesde = date('Y-m-d', strtotime('-6 days'));
                $fechaHasta = $hoy;
            } elseif ($periodo === '30dias') {
                $fechaDesde = date('Y-m-d', strtotime('-29 days'));
                $fechaHasta = $hoy;
            } elseif ($periodo === 'mes') {
                $fechaDesde = date('Y-m-01');
                $fechaHasta = date('Y-m-t');
            }
        }

        // 1. Filtro por búsqueda de texto
        if ($buscar !== '') {
            $todosTrabajos = array_filter($todosTrabajos, function($t) use ($buscar) {
                $cod = isset($t['cod_socio']) ? $t['cod_socio'] : '';
                $nom = isset($t['nombre_socio']) ? $t['nombre_socio'] : '';
                $est = isset($t['estado_calculado']) ? $t['estado_calculado'] : (isset($t['estado']) ? $t['estado'] : '');
                $ope = isset($t['operador_nombre']) ? $t['operador_nombre'] : '';
                return stripos($cod, $buscar) !== false || stripos($nom, $buscar) !== false || stripos($est, $buscar) !== false || stripos($ope, $buscar) !== false;
            });
        }

        // 2. Filtro por rango de fechas
        if (!empty($fechaDesde) || !empty($fechaHasta)) {
            $todosTrabajos = array_filter($todosTrabajos, function($t) use ($fechaDesde, $fechaHasta) {
                $fechaStr = isset($t['fecha_actualizacion']) && !empty($t['fecha_actualizacion'])
                    ? $t['fecha_actualizacion']
                    : (isset($t['fecha_conclusion']) && !empty($t['fecha_conclusion'])
                        ? $t['fecha_conclusion']
                        : (isset($t['fecha_reconexion']) && !empty($t['fecha_reconexion'])
                            ? $t['fecha_reconexion']
                            : (isset($t['fecha_solucion']) && !empty($t['fecha_solucion'])
                                ? $t['fecha_solucion']
                                : (isset($t['fecha_registro']) ? $t['fecha_registro'] : ''))));

                if (empty($fechaStr)) {
                    return false;
                }

                $fechaItem = substr(trim($fechaStr), 0, 10);
                if (!empty($fechaDesde) && $fechaItem < $fechaDesde) {
                    return false;
                }
                if (!empty($fechaHasta) && $fechaItem > $fechaHasta) {
                    return false;
                }
                return true;
            });
        }

        // Ordenar por fecha_actualizacion o fecha_registro descendente
        usort($todosTrabajos, function ($a, $b) {
            $fechaA = isset($a['fecha_actualizacion']) ? $a['fecha_actualizacion'] : (isset($a['fecha_registro']) ? $a['fecha_registro'] : '');
            $fechaB = isset($b['fecha_actualizacion']) ? $b['fecha_actualizacion'] : (isset($b['fecha_registro']) ? $b['fecha_registro'] : '');
            if ($fechaA == $fechaB) return 0;
            return ($fechaA > $fechaB) ? -1 : 1;
        });

        // Paginación
        $porPagina = 10;
        $totalTrabajos = count($todosTrabajos);
        $p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
        if ($p < 1) $p = 1;
        $totalPaginas = ceil($totalTrabajos / $porPagina);
        if ($p > $totalPaginas && $totalPaginas > 0) $p = $totalPaginas;
        $trabajosPaginados = array_slice($todosTrabajos, ($p - 1) * $porPagina, $porPagina);

        $this->view('administrador/historial', [
            'title'        => 'Historial de Trabajos Concluidos (Global)',
            'trabajos'     => $trabajosPaginados,
            'totalTrabajos'=> $totalTrabajos,
            'p'            => $p,
            'totalPaginas' => $totalPaginas,
            'buscar'       => $buscar,
            'fechaDesde'   => $fechaDesde,
            'fechaHasta'   => $fechaHasta,
            'periodo'      => $periodo,
            'errores'      => $errores,
        ], 'main');
    }
}
