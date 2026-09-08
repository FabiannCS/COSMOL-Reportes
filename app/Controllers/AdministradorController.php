<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Especialidad;
use App\Services\ApiClient;

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

        // ── Peticiones paralelas a APIs externas (Reconexiones y Reclamos) ─────────
        $clientRec  = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);

        $respuestas = ApiClient::getMultiFromClients([
            'reconexiones' => [$clientRec, '/reconexiones?estado=PENDIENTE'],
            'reclamos'     => [$clientRecl, '/reclamos?estado=PENDIENTE'],
        ]);

        $respRec  = isset($respuestas['reconexiones']) ? $respuestas['reconexiones'] : null;
        $respRecl = isset($respuestas['reclamos']) ? $respuestas['reclamos'] : null;

        if ($respRec === null) {
            $errores[] = 'No se pudo conectar con la API de Reconexiones.';
        } else {
            $reconexiones = isset($respRec['datos']) ? $respRec['datos'] : (is_array($respRec) ? $respRec : []);
        }

        if ($respRecl === null) {
            $errores[] = 'No se pudo conectar con la API de Reclamos.';
        } else {
            $todosReclamos = isset($respRecl['datos']) ? $respRecl['datos'] : (is_array($respRecl) ? $respRecl : []);
            $reclamos      = is_array($todosReclamos) ? $todosReclamos : [];
        }

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

        if ($tipo === 'reconexion') {
            $glosa = isset($_POST['glosa']) ? trim($_POST['glosa']) : '';
            $lecturacion = isset($_POST['lecturacion']) ? trim($_POST['lecturacion']) : '';
            
            $dataPayload = [
                'usuario_reconexion' => isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1,
                'lectura_reconexion' => is_numeric($lecturacion) ? (int)$lecturacion : $lecturacion,
                'glosa'              => $glosa
            ];
            
            $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
            $resultado = $client->put('/reconexiones/' . $idTrabajo, $dataPayload);

        } elseif ($tipo === 'reclamo') {
            $observacionConclusion = isset($_POST['observacion_conclusion']) ? trim($_POST['observacion_conclusion']) : '';
            $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'CONCLUIDO';

            // La API externa concatena automáticamente la glosa previa con " | CONCLUSIÓN: "
            $glosaFinal = (!empty($estado) ? "[{$estado}] " : "") . $observacionConclusion;

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

        if ($resultado === null || (is_array($resultado) && isset($resultado['estado']) && $resultado['estado'] === 'error')) {
            $msgError = (is_array($resultado) && !empty($resultado['mensaje']))
                ? $resultado['mensaje']
                : 'Hubo un error al concluir el trabajo en el servidor externo. Intente nuevamente.';
            $_SESSION['error'] = $msgError;
        } else {
            $_SESSION['mensaje'] = 'Trabajo concluido correctamente.';
        }
        
        $this->redirect('/administrador/trabajos');
    }

    /**
     * GET /administrador/historial
     *
     * Muestra el historial de trabajos que han sido concluidos
     * por el Administrador/Supervisor actual.
     */
    public function historial()
    {

        $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;

        $reconexionesConcluidas = [];
        $reclamosConcluidos     = [];
        $errores                = [];

        // ── Peticiones paralelas a APIs externas (Reconexiones y Reclamos Concluidos) ─────────
        $clientRec  = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);

        $respuestas = ApiClient::getMultiFromClients([
            'reconexiones' => [$clientRec, '/reconexiones?estado=CONCLUIDA'],
            'reclamos'     => [$clientRecl, '/reclamos?estado=CONCLUIDO'],
        ]);

        $respRec  = isset($respuestas['reconexiones']) ? $respuestas['reconexiones'] : null;
        $respRecl = isset($respuestas['reclamos']) ? $respuestas['reclamos'] : null;

        if ($respRec === null) {
            $errores[] = 'No se pudo conectar con la API de Reconexiones.';
        } else {
            $todasRec = isset($respRec['datos']) ? $respRec['datos'] : (is_array($respRec) ? $respRec : []);
            
            $reconexionesConcluidas = array_filter($todasRec, function($item) use ($idUsuario) {
                return (isset($item['usuario_reconexion']) && $item['usuario_reconexion'] == $idUsuario);
            });
        }

        if ($respRecl === null) {
            $errores[] = 'No se pudo conectar con la API de Reclamos.';
        } else {
            $todosRecl = isset($respRecl['datos']) ? $respRecl['datos'] : (is_array($respRecl) ? $respRecl : []);
            
            $reclamosConcluidos = array_filter($todosRecl, function($item) use ($idUsuario) {
                return (
                    (isset($item['usuario_conclucion']) && $item['usuario_conclucion'] == $idUsuario) ||
                    (isset($item['usuario_reclamo']) && $item['usuario_reclamo'] == $idUsuario)
                );
            });
        }

        $this->view('administrador/historial', [
            'title'        => 'Historial de Trabajos Concluidos',
            'reconexiones' => $reconexionesConcluidas,
            'reclamos'     => $reclamosConcluidos,
            'errores'      => $errores,
        ], 'main');
    }
}
