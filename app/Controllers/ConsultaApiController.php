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

        // Auto-detectar Reclamo o Reconexión si envían el JSON directamente de la API externa
        if (!$idTipo) {
            if (isset($input['id_tipo_reclamo'])) {
                $idTipo = 4; // Registro de Reclamo
            } elseif (isset($input['descripcion']) && stripos($input['descripcion'], 'recon') !== false) {
                $idTipo = 5; // Solicitud de Reconexión
            }
        }

        $fecha         = isset($input['fecha_consulta']) ? $input['fecha_consulta'] : date('Y-m-d');
        $hora          = isset($input['hora_consulta']) ? $input['hora_consulta'] : date('H:i:s');
        $telefono      = isset($input['telefono']) && !empty($input['telefono']) ? trim((string)$input['telefono']) : null;
        $tipoUbicacion = isset($input['tipo_ubicacion']) && !empty($input['tipo_ubicacion']) ? trim((string)$input['tipo_ubicacion']) : null;

        if ($codigoSocio <= 0 || !$idTipo) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Parámetros obligatorios faltantes']);
            exit;
        }

        // 3. Insertar en la tabla consulta
        try {
            $db = Database::getInstance();
            // Migración defensiva no destructiva para columnas nuevas
            $db->exec("ALTER TABLE consulta ADD COLUMN IF NOT EXISTS telefono VARCHAR(30);");
            $db->exec("ALTER TABLE consulta ADD COLUMN IF NOT EXISTS tipo_ubicacion VARCHAR(20);");

            $stmt = $db->prepare("
                INSERT INTO consulta (codigo_socio, nombres, telefono, tipo_ubicacion, fecha_consulta, hora_consulta, id_tipo)
                VALUES (:codigo_socio, :nombres, :telefono, :tipo_ubicacion, :fecha_consulta, :hora_consulta, :id_tipo)
            ");

            $stmt->execute([
                ':codigo_socio'   => $codigoSocio,
                ':nombres'        => $nombres,
                ':telefono'       => $telefono,
                ':tipo_ubicacion' => $tipoUbicacion,
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
