<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Trabajo extends Model
{
    protected $table = 'trabajo';
    protected $primaryKey = 'id_trabajo';

    /**
     * Devuelve los trabajos asignados a un operador, opcionalmente filtrados por estado.
     *
     * @param int $idOperador
     * @param array $estados Array de IDs de estados (ej. [1, 2] para Pendiente y En Proceso)
     * @return array
     */
    public function getByOperador($idOperador, $estados = [])
    {
        $sql = "SELECT t.*, u.username as operador 
                FROM {$this->table} t
                LEFT JOIN usuario u ON t.id_operador = u.id_usuario
                WHERE t.id_operador = ?";
        
        $params = [$idOperador];

        if (!empty($estados) && is_array($estados)) {
            $inQuery = implode(',', array_fill(0, count($estados), '?'));
            $sql .= " AND t.estado IN ($inQuery)";
            $params = array_merge($params, $estados);
        }
        
        $sql .= " ORDER BY t.fecha_asignacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de un trabajo a Concluido (3) y registra la fecha de conclusión.
     *
     * @param int $idTrabajo
     * @return bool
     */
    public function marcarConcluido($idTrabajo)
    {
        $sql = "UPDATE {$this->table} 
                SET estado = 3, fecha_conclusion = CURRENT_TIMESTAMP 
                WHERE id_trabajo = :id_trabajo";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id_trabajo' => $idTrabajo]);
    }

    /**
     * Obtiene los detalles completos de un trabajo específico.
     *
     * @param int $idTrabajo
     * @return array|false
     */
    public function findById($idTrabajo)
    {
        $sql = "SELECT t.*, u.username as operador 
                FROM {$this->table} t
                LEFT JOIN usuario u ON t.id_operador = u.id_usuario
                WHERE t.id_trabajo = :id_trabajo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_trabajo' => $idTrabajo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
