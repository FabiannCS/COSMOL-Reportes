<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Models\Especialidad;
use App\Services\ApiClient;

class OperadorController extends Controller
{
    private $apiConfig;

    public function __construct()
    {
        $this->apiConfig = require __DIR__ . '/../Config/api.php';
    }

    private function getEspecialidadOperador()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? $_SESSION['usuario']['id_usuario'] : null;
        if (!$idUsuario) {
            return null;
        }

        $especialidadModel = new Especialidad();
        return $especialidadModel->getByUsuario($idUsuario);
    }

    public function trabajos()
    {
        $especialidad = $this->getEspecialidadOperador();

        if (!$especialidad) {
            $_SESSION['error'] = 'No tienes una especialidad asignada. Contacta al administrador.';
            $this->redirect('/');
        }

        $datos = null;
        $error = null;
        $vista = '';

        // Según la especialidad, consumimos la API correspondiente
        switch ($especialidad['nombre']) {
            case 'Reconexión':
                $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                $respuesta = $client->get('/reconexiones?estado=PENDIENTE');
                $datos = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : null);
                $vista = 'operador/reconexiones';
                break;
                
            case 'Maestro de alcantarillado':
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $respuesta = $client->get('/reclamos?estado=PENDIENTE'); 
                $todosLosReclamos = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                // Filtrar solo los de Alcantarillado (id_tipo_reclamo == 3)
                $datos = array_filter($todosLosReclamos, function($item) {
                    return isset($item['id_tipo_reclamo']) && (int)$item['id_tipo_reclamo'] === 3;
                });
                
                $vista = 'operador/reclamos';
                break;
                
            case 'Agua Potable':
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $respuesta = $client->get('/reclamos?estado=PENDIENTE');
                $todosLosReclamos = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                // Filtrar solo los de Agua Potable (id_tipo_reclamo == 2)
                $datos = array_filter($todosLosReclamos, function($item) {
                    return isset($item['id_tipo_reclamo']) && (int)$item['id_tipo_reclamo'] === 2;
                });
                
                $vista = 'operador/reclamos';
                break;
                
            default:
                $error = 'Especialidad no reconocida o no soportada.';
                $vista = 'operador/sin_especialidad';
                break;
        }

        if ($datos === null && empty($error)) {
            $error = 'Hubo un error al comunicarse con el servidor de trabajos. Intente más tarde.';
        }

        $this->view($vista, [
            'title' => 'Mis Trabajos — ' . $especialidad['nombre'],
            'especialidad' => $especialidad['nombre'],
            'trabajos' => $datos,
            'error' => $error
        ], 'main');
    }

    public function detalle()
    {
        $especialidad = $this->getEspecialidadOperador();

        if (!$especialidad) {
            $this->redirect('/operador/trabajos');
        }

        $idTrabajo = isset($_GET['id']) ? trim($_GET['id']) : null;
        if (!$idTrabajo) {
            $_SESSION['error'] = 'No se especificó el ID del trabajo.';
            $this->redirect('/operador/trabajos');
        }

        $datos = null;
        $error = null;
        $vista = '';

        switch ($especialidad['nombre']) {
            case 'Reconexión':
                $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                $respuesta = $client->get('/reconexiones?estado=PENDIENTE');
                $lista = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                $datos = null;
                foreach ($lista as $item) {
                    if (isset($item['id_reconexion']) && $item['id_reconexion'] == $idTrabajo) {
                        $datos = $item;
                        break;
                    }
                }
                $vista = 'operador/reconexion_detalle';
                break;
                
            case 'Maestro de alcantarillado':
            case 'Agua Potable':
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $respuesta = $client->get('/reclamos?estado=PENDIENTE');
                $lista = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                $datos = null;
                foreach ($lista as $item) {
                    if (isset($item['id_reclamo']) && $item['id_reclamo'] == $idTrabajo) {
                        $datos = $item;
                        break;
                    }
                }
                $vista = 'operador/reclamo_detalle';
                break;
        }

        if ($datos === null) {
            $_SESSION['error'] = 'No se pudo obtener el detalle del trabajo.';
            $this->redirect('/operador/trabajos');
        }

        $apiFotoBaseUrl = isset($this->apiConfig['fotos']['base_url']) ? $this->apiConfig['fotos']['base_url'] : 'http://api.cosmol.com.bo';

        $this->view($vista, [
            'title'          => 'Concluir Trabajo — ' . $especialidad['nombre'],
            'especialidad'   => $especialidad['nombre'],
            'trabajo'        => $datos,
            'apiFotoBaseUrl' => $apiFotoBaseUrl,
            'error'          => $error
        ], 'main');
    }

    public function concluir()
    {
        $especialidad = $this->getEspecialidadOperador();

        if (!$especialidad) {
            $this->redirect('/operador/trabajos');
        }

        $idTrabajo = isset($_POST['id_trabajo']) ? trim($_POST['id_trabajo']) : null;
        
        if (!$idTrabajo) {
            $_SESSION['error'] = 'Solicitud inválida.';
            $this->redirect('/operador/trabajos');
        }

        $resultado = null;

        switch ($especialidad['nombre']) {
            case 'Reconexión':
                $glosa = isset($_POST['glosa']) ? trim($_POST['glosa']) : '';
                $lecturacion = isset($_POST['lecturacion']) ? trim($_POST['lecturacion']) : '';
                
                $dataPayload = [
                    'usuario_reconexion' => isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1,
                    'lectura_reconexion' => is_numeric($lecturacion) ? (int)$lecturacion : $lecturacion,
                    'glosa'              => $glosa
                ];
                
                $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                $resultado = $client->put('/reconexiones/' . $idTrabajo, $dataPayload);
                break;
                
            case 'Maestro de alcantarillado':
            case 'Agua Potable':
                $observacionConclusion = isset($_POST['observacion_conclusion']) ? trim($_POST['observacion_conclusion']) : '';
                $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

                $dataPayload = [
                    'id_trabajo' => $idTrabajo,
                    'observacion' => $observacionConclusion,
                    'estado' => $estado,
                    'fecha_conclusion' => date('Y-m-d H:i:s'),
                    'id_operador' => $_SESSION['usuario']['id_usuario']
                ];
                
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $resultado = $client->post('/reclamos/concluir', $dataPayload);
                break;
        }

        if ($resultado === null || (is_array($resultado) && isset($resultado['estado']) && $resultado['estado'] === 'error')) {
            $msgError = (is_array($resultado) && !empty($resultado['mensaje']))
                ? $resultado['mensaje']
                : 'Hubo un error al concluir el trabajo en el servidor externo. Intente nuevamente.';
            $_SESSION['error'] = $msgError;
            $this->redirect("/operador/detalle?id={$idTrabajo}");
        }

        $_SESSION['mensaje'] = 'Trabajo concluido correctamente.';
        $this->redirect('/operador/trabajos');
    }
}
