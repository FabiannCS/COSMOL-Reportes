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
     * Obtiene todos los tipos de consulta disponibles.
     *
     * @return array Lista de tipos de consulta
     */
    public function getTiposConsulta()
    {
        $sql = "SELECT id_tipo, nombre FROM tipo_consulta ORDER BY nombre ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el conteo total de consultas agrupadas por tipo de consulta,
     * considerando filtros de fecha y búsqueda.
     *
     * @param array $filtros Filtros aplicables (fecha_inicio, fecha_fin, buscar)
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
                LEFT JOIN consulta c ON t.id_tipo = c.id_tipo";

        $params = [];
        $conditions = [];

        if (!empty($filtros['fecha_inicio'])) {
            $conditions[] = "c.fecha_consulta >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $conditions[] = "c.fecha_consulta <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['buscar'])) {
            $conditions[] = "(c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY t.id_tipo, t.nombre, t.descripcion ORDER BY t.id_tipo ASC";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene consultas paginadas y filtradas.
     *
     * @param array $filtros Filtros opcionales (fecha_inicio, fecha_fin, id_tipo, buscar)
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento para paginación
     * @return array Resultados de consultas
     */
    public function getConsultasPaginadas($filtros, $limit, $offset)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE 1=1";

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
     * Obtiene el total de consultas para la paginación según los filtros.
     *
     * @param array $filtros Filtros aplicables
     * @return int Total de registros
     */
    public function getTotalConsultas($filtros)
    {
        $sql = "SELECT COUNT(*)
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE 1=1";

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
     * Obtiene todas las consultas para exportar a CSV, aplicando filtros.
     *
     * @param array $filtros Filtros aplicables
     * @return array Sábana de datos
     */
    public function getAllConsultasExport($filtros)
    {
        $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username
                FROM consulta c
                LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
                LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
                WHERE 1=1";

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
