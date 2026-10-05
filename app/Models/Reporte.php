<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Reporte extends Model
{
    public function __construct()
    {
        try {
            // Migración defensiva no destructiva para columnas nuevas
            $this->db()->exec("ALTER TABLE consulta ADD COLUMN IF NOT EXISTS telefono VARCHAR(30);");
            $this->db()->exec("ALTER TABLE consulta ADD COLUMN IF NOT EXISTS tipo_ubicacion VARCHAR(20);");
        } catch (\Exception $e) {
            // Silencioso ante contingencias de permisos
        }
    }

    /**
     * Obtiene los tipos de consulta correspondientes al Chatbot (1 al 8).
     *
     * @return array
     */
    public function getTiposConsulta()
    {
        $sql = "SELECT id_tipo, nombre FROM tipo_consulta WHERE id_tipo <= 8 ORDER BY id_tipo ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los tipos de consulta correspondientes a la App Móvil (1, 2, 3, 9, 10, 11, 12).
     *
     * @return array
     */
    public function getTiposConsultaApp()
    {
        $sql = "SELECT id_tipo, nombre FROM tipo_consulta WHERE id_tipo IN (1, 2, 3, 9, 10, 11, 12) ORDER BY id_tipo ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el conteo total de consultas del Chatbot agrupadas por tipo,
     * excluyendo registros de la App Móvil.
     *
     * @param array $filtros
     * @return array
     */
    public function getTotalesPorTipoConsulta($filtros = [])
    {
        $sql = "SELECT 
                    t.id_tipo,
                    t.nombre,
                    t.descripcion,
                    COUNT(c.id_consulta) as total
                FROM tipo_consulta t
                LEFT JOIN (
                    SELECT c.id_consulta, c.id_tipo
                    FROM consulta c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    WHERE (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL)
                      AND (u.username != 'app_movil' OR u.username IS NULL)
        ";

        $params = [];
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ) c ON t.id_tipo = c.id_tipo
                WHERE t.id_tipo <= 8
                GROUP BY t.id_tipo, t.nombre, t.descripcion
                ORDER BY t.id_tipo ASC";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene consultas paginadas y filtradas del Chatbot (excluye App Móvil).
     */
    public function getConsultasPaginadas($filtros, $limit, $offset)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL)
                  AND (u.username != 'app_movil' OR u.username IS NULL)";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC, c.id_consulta DESC";
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db()->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de consultas del Chatbot para paginación (excluye App Móvil).
     */
    public function getTotalConsultas($filtros)
    {
        $sql = "SELECT COUNT(*)
                FROM consulta c
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL)
                  AND (u.username != 'app_movil' OR u.username IS NULL)";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene consultas del Chatbot para exportar a CSV.
     */
    public function getAllConsultasExport($filtros)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion != 'APP_MOVIL' OR c.tipo_ubicacion IS NULL)
                  AND (u.username != 'app_movil' OR u.username IS NULL)";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC, c.id_consulta DESC";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================================
    // MÉTODOS ESPECIALIZADOS PARA EL MÓDULO APP MÓVIL
    // =========================================================================

    /**
     * Obtiene el conteo agrupado por tipo para las tarjetas KPI de la App Móvil.
     * Tipos: 1 (Acceso), 3 (Consumo), 9 (PDF), 10 (Multipago), 11 (Pago al Paso), 12 (QR).
     */
    public function getTotalesPorTipoApp($filtros = [])
    {
        $sql = "SELECT 
                    t.id_tipo,
                    t.nombre,
                    t.descripcion,
                    COUNT(c.id_consulta) as total
                FROM tipo_consulta t
                LEFT JOIN (
                    SELECT c.id_consulta, c.id_tipo
                    FROM consulta c
                    LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                    WHERE (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil')
        ";

        $params = [];
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ) c ON t.id_tipo = c.id_tipo
                WHERE t.id_tipo IN (1, 2, 3, 9, 10, 11, 12)
                GROUP BY t.id_tipo, t.nombre, t.descripcion
                ORDER BY t.id_tipo ASC";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene consultas paginadas y filtradas exclusivamente de la App Móvil.
     */
    public function getConsultasAppPaginadas($filtros, $limit, $offset)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, t.id_tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil')";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC, c.id_consulta DESC";
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db()->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de registros de la App Móvil para la paginación.
     */
    public function getTotalConsultasApp($filtros)
    {
        $sql = "SELECT COUNT(*)
                FROM consulta c
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil')";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Obtiene todas las consultas de la App Móvil para exportar a CSV.
     */
    public function getAllConsultasAppExport($filtros)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, t.id_tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE (c.tipo_ubicacion = 'APP_MOVIL' OR u.username = 'app_movil')";

        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_tipo'])) {
            $sql .= " AND c.id_tipo = :id_tipo";
            $params[':id_tipo'] = $filtros['id_tipo'];
        }

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $sql .= " ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC, c.id_consulta DESC";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de números telefónicos únicos que han consultado al chatbot.
     *
     * @return int
     */
    public function getTotalNumerosUnicos()
    {
        try {
            $sql = "SELECT COUNT(DISTINCT telefono) FROM consulta WHERE telefono IS NOT NULL AND telefono <> ''";
            $stmt = $this->db()->query($sql);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene los números más activos para control de tráfico y detección de excesos.
     *
     * @param int $limit
     * @return array
     */
    public function getNumerosMasActivos($limit = 5)
    {
        try {
            $sql = "SELECT telefono, COUNT(*) as total_consultas, MAX(nombres) as ultimo_nombre, MAX(fecha_consulta) as ultima_fecha
                    FROM consulta
                    WHERE telefono IS NOT NULL AND telefono <> ''
                    GROUP BY telefono
                    ORDER BY total_consultas DESC
                    LIMIT :limit";
            $stmt = $this->db()->prepare($sql);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
