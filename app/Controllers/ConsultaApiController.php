<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

class ConsultaApiController extends Controller
{
    public function registrar()
    {
        // 1. Validar Token de Seguridad
        $tokenEsperado = getenv('REPORTES_API_TOKEN') ?: '';
        $tokenRecibido = $_SERVER['HTTP_X_REPORTES_TOKEN'] ?? '';

        // Header headers generally arrive as HTTP_...
        // For standard setup in apache with php-fpm, headers like X-Reportes-Token become HTTP_X_REPORTES_TOKEN
        
        if ($tokenRecibido !== $tokenEsperado) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Token no autorizado']);
            exit;
        }

        // 2. Leer JSON del Chatbot
        $input = json_decode(file_get_contents('php://input'), true);

        $codigoSocio = isset($input['codigo_socio']) ? (int)$input['codigo_socio'] : 0;
        $nombres     = isset($input['nombres']) ? trim($input['nombres']) : 'Socio';
        $idTipo      = isset($input['id_tipo']) ? (int)$input['id_tipo'] : null;

        // Auto-detectar Reclamo si envían el JSON directamente de la API externa
        if (!$idTipo) {
            if (isset($input['id_tipo_reclamo'])) {
                $idTipo = 4; // Registro de Reclamo
            } elseif (isset($input['descripcion']) && stripos($input['descripcion'], 'recon') !== false) {
                $idTipo = 5; // Solicitud de Reconexión
            }
        }

        $fecha       = isset($input['fecha_consulta']) ? $input['fecha_consulta'] : date('Y-m-d');
        $hora        = isset($input['hora_consulta']) ? $input['hora_consulta'] : date('H:i:s');

        // Ya no capturamos datos operativos de trabajos, solo metadatos de la consulta
        // Quitamos la validación de codigoSocio estricta por si el JSON del Reclamo no lo incluye
        if (!$idTipo) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo determinar el id_tipo o falta en la petición']);
            exit;
        }

        // 3. Insertar en la tabla consulta
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO consulta (codigo_socio, nombres, fecha_consulta, hora_consulta, id_tipo)
                VALUES (:codigo_socio, :nombres, :fecha_consulta, :hora_consulta, :id_tipo)
            ");

            $stmt->execute([
                ':codigo_socio'   => $codigoSocio,
                ':nombres'        => $nombres,
                ':fecha_consulta' => $fecha,
                ':hora_consulta'  => $hora,
                ':id_tipo'        => $idTipo
            ]);

            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Consulta registrada'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error interno al guardar', 'details' => $e->getMessage()]);
        }
        exit;
    }
}
