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

    /**
     * Verifica sesión activa con rol Administrador.
     * El middleware ya garantiza esto, pero se revalida internamente
     * como medida de defensa en profundidad.
     */
    private function verificarAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rol = isset($_SESSION['usuario']['nombre_rol'])
            ? $_SESSION['usuario']['nombre_rol']
            : null;

        if ($rol !== 'Administrador') {
            $_SESSION['error'] = 'Acceso denegado.';
            $this->redirect('/dashboard');
        }
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
        $this->verificarAdmin();

        $reconexiones  = [];
        $reclamos      = [];
        $errores       = [];

        // ── Reconexiones pendientes ─────────────────────────────────
        $clientRec = new ApiClient($this->apiConfig['reconexiones']['base_url']);
        $respRec   = $clientRec->get('/reconexiones?estado=PENDIENTE');

        if ($respRec === null) {
            $errores[] = 'No se pudo conectar con la API de Reconexiones.';
        } else {
            $reconexiones = isset($respRec['datos']) ? $respRec['datos'] : (is_array($respRec) ? $respRec : []);
        }

        // ── Reclamos pendientes (todos, luego separamos) ────────────
        $clientRecl = new ApiClient($this->apiConfig['reclamos']['base_url']);
        $respRecl   = $clientRecl->get('/reclamos?estado=PENDIENTE');

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
        $this->verificarAdmin();

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
                $respuesta = $client->get('/reconexiones?estado=PENDIENTE');
                $lista     = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);

                foreach ($lista as $item) {
                    if (isset($item['id_reconexion']) && $item['id_reconexion'] == $id) {
                        $datos = $item;
                        break;
                    }
                }
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
}
