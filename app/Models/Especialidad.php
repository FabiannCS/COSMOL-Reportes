<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

class Especialidad extends Model
{

    public function getByUsuario($idUsuario)
    {
        $stmt = $this->db()->prepare('
            SELECT e.id_especialidad, e.nombre, e.fecha_creacion
            FROM especialidad e
            INNER JOIN usuario u ON e.id_especialidad = u.id_especialidad
            WHERE u.id_usuario = :id_usuario
        ');
        $stmt->execute(['id_usuario' => $idUsuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function getAll()
    {
        $stmt = $this->db()->prepare('
            SELECT id_especialidad, nombre, fecha_creacion
            FROM especialidad
            ORDER BY id_especialidad ASC
        ');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignar($idEspecialidad, $idUsuario)
    {
        try {
            $stmt = $this->db()->prepare('
                UPDATE usuario
                SET id_especialidad = :id_especialidad
                WHERE id_usuario = :id_usuario
            ');
            return $stmt->execute([
                'id_especialidad' => $idEspecialidad,
                'id_usuario' => $idUsuario
            ]);
        } catch (Exception $e) {
            error_log("Error asignando especialidad: " . $e->getMessage());
            return false;
        }
    }
}
