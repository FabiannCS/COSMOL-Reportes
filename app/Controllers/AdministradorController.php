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

        $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
        $id   = isset($_GET['id'])   ? trim($_GET['id'])   : '';

        if ($tipo === '' || $id === '') {
            $_SESSION['error'] = 'Parámetros de trabajo incompletos.';
            $this->redirect('/administrador/trabajos');
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
            $this->redirect('/administrador/trabajos');
        }

        if ($datos !== null) {
            $datos['estado_calculado'] = ApiClient::determinarEstadoTrabajo($datos);
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

        $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : null;
        $idTrabajo = isset($_POST['id_trabajo']) ? trim($_POST['id_trabajo']) : null;
        
        if (!$idTrabajo || !$tipo) {
            $_SESSION['error'] = 'Solicitud inválida.';
            $this->redirect('/administrador/trabajos');
        }

        $resultado = null;
        $seguimientoModel = new TrabajoSeguimiento();

        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'CONCLUIDO';
        $glosa = isset($_POST['glosa']) ? trim($_POST['glosa']) : ''; // Para reconexiones
        $observacionConclusion = isset($_POST['observacion_conclusion']) ? trim($_POST['observacion_conclusion']) : ''; // Para reclamos
        
        $glosaFinal = (!empty($estado) ? "[{$estado}] " : "") . ($tipo === 'reconexion' ? $glosa : $observacionConclusion);

        if ($estado === 'NO CONCLUIDO' || $estado === 'NO PROCEDENTE') {
            // Guardar localmente
            $exito = $seguimientoModel->registrarSeguimiento($idTrabajo, $tipo, $estado, $glosaFinal);
            if (!$exito) {
                $_SESSION['error'] = 'Error al registrar el seguimiento local.';
                $this->redirect("/administrador/trabajos/detalle?tipo={$tipo}&id={$idTrabajo}");
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
                $this->redirect('/administrador/trabajos');
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
        
        $this->redirect('/administrador/trabajos');
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
                $item['estado_calculado'] = $seguimiento->estado_interno;
                $reconexionesNoConcluidas[] = $item;
            } elseif ($estCalc === 'NO CONCLUIDO' || $estCalc === 'NO PROCEDENTE') {
                $item['tipo_trabajo']     = 'reconexion';
                $item['estado_calculado'] = $estCalc;
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
                $item['estado_calculado'] = $seguimiento->estado_interno;
                $reclamosNoConcluidos[] = $item;
            } elseif ($estCalc === 'NO CONCLUIDO' || $estCalc === 'NO PROCEDENTE') {
                $item['tipo_trabajo']     = 'reclamo';
                $item['estado_calculado'] = $estCalc;
                $reclamosNoConcluidos[] = $item;
            }
        }

        $todosTrabajos = array_merge($reconexionesNoConcluidas, $reclamosNoConcluidos);

        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        if ($buscar !== '') {
            $todosTrabajos = array_filter($todosTrabajos, function($t) use ($buscar) {
                $cod = $t['cod_socio'] ?? '';
                $nom = $t['nombre_socio'] ?? '';
                $est = $t['estado_calculado'] ?? ($t['estado'] ?? '');
                return stripos($cod, $buscar) !== false || stripos($nom, $buscar) !== false || stripos($est, $buscar) !== false;
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
                $item['operador_nombre'] = isset($mapaUsuarios[$idOp]) ? $mapaUsuarios[$idOp] : ($idOp > 0 ? "Usuario #{$idOp}" : 'Sistema');
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
                
                $item['tipo_trabajo'] = 'reclamo';
                $item['estado_calculado'] = ApiClient::determinarEstadoTrabajo($item);
                $item['operador_nombre'] = isset($mapaUsuarios[$idOp]) ? $mapaUsuarios[$idOp] : ($idOp > 0 ? "Usuario #{$idOp}" : 'Sistema');
                $reclamosConcluidos[] = $item;
            }
        }

        $todosTrabajos = array_merge($reconexionesConcluidas, $reclamosConcluidos);

        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

        if ($buscar !== '') {
            $todosTrabajos = array_filter($todosTrabajos, function($t) use ($buscar) {
                $cod = $t['cod_socio'] ?? '';
                $nom = $t['nombre_socio'] ?? '';
                $est = $t['estado_calculado'] ?? ($t['estado'] ?? '');
                $ope = $t['operador_nombre'] ?? '';
                return stripos($cod, $buscar) !== false || stripos($nom, $buscar) !== false || stripos($est, $buscar) !== false || stripos($ope, $buscar) !== false;
            });
        }

        // Ordenar por fecha_actualizacion o fecha_registro descendente
        usort($todosTrabajos, function ($a, $b) {
            $fechaA = $a['fecha_actualizacion'] ?? $a['fecha_registro'] ?? '';
            $fechaB = $b['fecha_actualizacion'] ?? $b['fecha_registro'] ?? '';
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
            'errores'      => $errores,
        ], 'main');
    }
}
