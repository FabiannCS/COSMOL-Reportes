<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Models\Especialidad;
use App\Services\ApiClient;
use App\Models\TrabajoSeguimiento;

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
            $this->view('operador/sin_especialidad', [
                'title' => 'Sin Especialidad — COSMOL Reportes',
                'error' => 'No tienes una especialidad asignada en el sistema.'
            ], 'main');
            return;
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
        } else if (is_array($datos)) {
            // Filtrar los trabajos que ya fueron marcados localmente como NO CONCLUIDO o NO PROCEDENTE
            $seguimientoModel = new TrabajoSeguimiento();
            $tipoTrabajo = ($especialidad['nombre'] === 'Reconexión') ? 'reconexion' : 'reclamo';
            $seguimientos = $seguimientoModel->obtenerTodosPorTipo($tipoTrabajo);
            
            $idSeguimientos = [];
            foreach ($seguimientos as $seg) {
                $idSeguimientos[] = $seg->id_trabajo;
            }

            $datos = array_filter($datos, function($item) use ($idSeguimientos, $tipoTrabajo) {
                $idItem = ($tipoTrabajo === 'reconexion') ? ($item['id_reconexion'] ?? null) : ($item['id_reclamo'] ?? null);
                if ($idItem && in_array($idItem, $idSeguimientos)) {
                    return false; // Ocultar de la lista de pendientes porque ya se gestionó localmente
                }
                return true;
            });
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
                $datos = null;

                // Intentar obtener directamente por ID (endpoint individual soportado por la API)
                $respuestaDirecta = $client->get('/reconexiones/' . $idTrabajo);
                if (isset($respuestaDirecta['datos']) && is_array($respuestaDirecta['datos'])) {
                    $datos = $respuestaDirecta['datos'];
                }

                // Fallback: buscar en PENDIENTE si el endpoint individual falló
                if (!$datos) {
                    $respuesta = $client->get('/reconexiones?estado=PENDIENTE');
                    $lista = isset($respuesta['datos']) ? $respuesta['datos'] : [];
                    foreach ($lista as $item) {
                        if (isset($item['id_reconexion']) && $item['id_reconexion'] == $idTrabajo) {
                            $datos = $item;
                            break;
                        }
                    }
                }

                // Fallback: buscar en CONCLUIDA (estado correcto en la API de reconexiones)
                if (!$datos) {
                    $respuestaConcluida = $client->get('/reconexiones?estado=CONCLUIDA');
                    $listaConcluida = isset($respuestaConcluida['datos']) ? $respuestaConcluida['datos'] : [];
                    foreach ($listaConcluida as $item) {
                        if (isset($item['id_reconexion']) && $item['id_reconexion'] == $idTrabajo) {
                            $datos = $item;
                            break;
                        }
                    }
                }
                
                $vista = (isset($datos['estado']) && strtoupper(trim($datos['estado'])) !== 'PENDIENTE') 
                            ? 'operador/reconexion_concluido' 
                            : 'operador/reconexion_detalle';
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

                // Si no está en PENDIENTE, buscar en CONCLUIDO (para el historial)
                if (!$datos) {
                    $respuestaConcluido = $client->get('/reclamos?estado=CONCLUIDO');
                    $listaConcluida = isset($respuestaConcluido['datos']) ? $respuestaConcluido['datos'] : (is_array($respuestaConcluido) ? $respuestaConcluido : []);
                    foreach ($listaConcluida as $item) {
                        if (isset($item['id_reclamo']) && $item['id_reclamo'] == $idTrabajo) {
                            $datos = $item;
                            break;
                        }
                    }
                }
                
                $vista = (isset($datos['estado']) && strtoupper(trim($datos['estado'])) !== 'PENDIENTE') 
                            ? 'operador/reclamo_concluido' 
                            : 'operador/reclamo_detalle';
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
        $seguimientoModel = new TrabajoSeguimiento();
        $tipoTrabajo = ($especialidad['nombre'] === 'Reconexión') ? 'reconexion' : 'reclamo';
        
        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : 'CONCLUIDO';
        $glosa = isset($_POST['glosa']) ? trim($_POST['glosa']) : ''; // Para reconexiones
        $observacionConclusion = isset($_POST['observacion_conclusion']) ? trim($_POST['observacion_conclusion']) : ''; // Para reclamos
        
        $glosaFinal = (!empty($estado) ? "[{$estado}] " : "") . ($especialidad['nombre'] === 'Reconexión' ? $glosa : $observacionConclusion);

        if ($estado === 'NO CONCLUIDO' || $estado === 'NO PROCEDENTE') {
            // Guardar localmente
            $exito = $seguimientoModel->registrarSeguimiento($idTrabajo, $tipoTrabajo, $estado, $glosaFinal);
            if (!$exito) {
                $_SESSION['error'] = 'Error al registrar el seguimiento local.';
                $this->redirect("/operador/detalle?id={$idTrabajo}");
            }
            $resultado = ['estado' => 'exito', 'mensaje' => 'Registrado localmente'];
        } else {
            // Guardar en la API (CONCLUIDO)
            switch ($especialidad['nombre']) {
                case 'Reconexión':
                    $lecturacion = isset($_POST['lecturacion']) ? trim($_POST['lecturacion']) : '';
                    
                    $dataPayload = [
                        'usuario_reconexion' => isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1,
                        'lectura_reconexion' => is_numeric($lecturacion) ? (int)$lecturacion : $lecturacion,
                        'glosa'              => $glosaFinal
                    ];
                    
                    $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                    $resultado = $client->put('/reconexiones/' . $idTrabajo, $dataPayload);
                    break;
                    
                case 'Maestro de alcantarillado':
                case 'Agua Potable':
                    $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 1;

                    $dataPayload = [
                        'usuario_conclucion' => $idUsuario,
                        'usuario_reclamo'    => $idUsuario,
                        'glosa'              => $glosaFinal
                    ];
                    
                    $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                    $resultado = $client->put('/reclamos/' . $idTrabajo, $dataPayload);
                    break;
            }

            if ($resultado !== null && (!is_array($resultado) || (isset($resultado['estado']) && $resultado['estado'] !== 'error'))) {
                // Si la API lo aceptó, limpiamos el registro local (por si antes fue NO CONCLUIDO)
                $seguimientoModel->eliminarSeguimiento($idTrabajo, $tipoTrabajo);
            }
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

    public function historial()
    {
        $especialidad = $this->getEspecialidadOperador();

        if (!$especialidad) {
            $this->view('operador/sin_especialidad', [
                'title' => 'Sin Especialidad — COSMOL Reportes',
                'error' => 'No tienes una especialidad asignada en el sistema.'
            ], 'main');
            return;
        }

        $idUsuario = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
        
        $datos = null;
        $error = null;
        $vista = 'operador/historial';

        // Historial
        switch ($especialidad['nombre']) {
            case 'Reconexión':
                $client = new ApiClient($this->apiConfig['reconexiones']['base_url']);
                // La API usa el estado 'CONCLUIDA' (femenino) para reconexiones concluidas
                $respuesta = $client->get('/reconexiones?estado=CONCLUIDA');
                $todos = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                // Filtrar por el operador que ejecutó la reconexión (usuario_reconexion se actualiza correctamente por la API)
                $datos = array_filter($todos, function($item) use ($idUsuario) {
                    $esDelUsuario = isset($item['usuario_reconexion']) && $item['usuario_reconexion'] == $idUsuario;
                    $estado = strtoupper(trim($item['estado'] ?? ''));
                    $esConcluida = $estado === 'CONCLUIDA' || $estado === 'CONCLUIDO';
                    return $esDelUsuario && $esConcluida;
                });
                break;
                
            case 'Maestro de alcantarillado':
            case 'Agua Potable':
                $client = new ApiClient($this->apiConfig['reclamos']['base_url']);
                $respuesta = $client->get('/reclamos?estado=CONCLUIDO'); 
                $todos = isset($respuesta['datos']) ? $respuesta['datos'] : (is_array($respuesta) ? $respuesta : []);
                
                $tipoReclamo = ($especialidad['nombre'] === 'Agua Potable') ? 2 : 3;
                
                $datos = array_filter($todos, function($item) use ($idUsuario, $tipoReclamo) {
                    $esDelTipo = isset($item['id_tipo_reclamo']) && (int)$item['id_tipo_reclamo'] === $tipoReclamo;
                    $noEsPendiente = isset($item['estado']) && $item['estado'] !== 'PENDIENTE';
                    $esDelUsuario = (isset($item['usuario_conclucion']) && $item['usuario_conclucion'] == $idUsuario) || 
                                    (isset($item['usuario_reclamo']) && $item['usuario_reclamo'] == $idUsuario);
                                    
                    return $esDelTipo && $noEsPendiente && $esDelUsuario;
                });
                break;
                
            default:
                $error = 'Especialidad no reconocida o no soportada.';
                break;
        }

        if ($datos === null && empty($error)) {
            $error = 'Hubo un error al comunicarse con el servidor de trabajos. Intente más tarde.';
        }

        $this->view($vista, [
            'title' => 'Historial de Trabajos - ' . $especialidad['nombre'],
            'especialidad' => $especialidad['nombre'],
            'trabajos' => $datos,
            'error' => $error
        ], 'main');
    }
}
