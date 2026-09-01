<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Observacion extends Model
{
    protected $table = 'observacion_trabajo';
    protected $primaryKey = 'id_observacion';

    /**
     * Obtiene todas las observaciones de un trabajo específico, incluyendo el autor.
     *
     * @param int $idTrabajo
     * @return array
     */
    public function getByTrabajo($idTrabajo)
    {
        $sql = "SELECT o.*, u.username as autor 
                FROM {$this->table} o
                LEFT JOIN usuario u ON o.id_usuario = u.id_usuario
                WHERE o.id_trabajo = :id_trabajo
                ORDER BY o.fecha_creacion ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_trabajo' => $idTrabajo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva observación para un trabajo.
     *
     * @param array $data ['id_trabajo', 'id_usuario', 'observacion']
     * @return bool
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (id_trabajo, id_usuario, observacion) 
                VALUES (:id_trabajo, :id_usuario, :observacion)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_trabajo' => $data['id_trabajo'],
            ':id_usuario' => $data['id_usuario'],
            ':observacion' => $data['observacion']
        ]);
    }
}
