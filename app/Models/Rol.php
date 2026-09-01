<?php

namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    /** @var array Roles protegidos del sistema que no deben ser eliminados ni renombrados */
    private static $rolesProtegidos = [1, 2, 3];

    public function all()
    {
        $stmt = $this->db()->query(
            "SELECT r.id_rol, r.nombre_rol, r.descripcion, r.fecha_creacion, COUNT(u.id_usuario) AS total_usuarios 
             FROM rol r 
             LEFT JOIN usuario u ON r.id_rol = u.id_rol 
             GROUP BY r.id_rol, r.nombre_rol, r.descripcion, r.fecha_creacion 
             ORDER BY r.id_rol ASC"
        );
        return $stmt->fetchAll();
    }

    public function allActive()
    {
        $stmt = $this->db()->query(
            "SELECT id_rol, nombre_rol, descripcion 
             FROM rol 
             ORDER BY id_rol ASC"
        );
        return $stmt->fetchAll();
    }

    public function findById($idRol)
    {
        $stmt = $this->db()->prepare(
            "SELECT id_rol, nombre_rol, descripcion, fecha_creacion 
             FROM rol 
             WHERE id_rol = :id_rol"
        );
        $stmt->execute(['id_rol' => (int)$idRol]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $stmt = $this->db()->prepare(
            "INSERT INTO rol (nombre_rol, descripcion) 
             VALUES (:nombre_rol, :descripcion)"
        );
        return $stmt->execute([
            'nombre_rol'  => trim($data['nombre_rol']),
            'descripcion' => trim($data['descripcion'])
        ]);
    }

    public function update($id, array $data)
    {
        $stmt = $this->db()->prepare(
            "UPDATE rol 
             SET nombre_rol = :nombre_rol, descripcion = :descripcion 
             WHERE id_rol = :id"
        );
        return $stmt->execute([
            'nombre_rol'  => trim($data['nombre_rol']),
            'descripcion' => trim($data['descripcion']),
            'id'          => (int)$id
        ]);
    }

    /**
     * Verifica si un rol es del núcleo protegido del sistema
     *
     * @param int $id
     * @return bool
     */
    public function isProtected($id)
    {
        return in_array((int)$id, self::$rolesProtegidos, true);
    }

    public function existsNombre($nombre, $excludeId = null)
    {
        if ($excludeId !== null) {
            $stmt = $this->db()->prepare(
                "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER(:nombre) AND id_rol != :exclude_id LIMIT 1"
            );
            $stmt->execute([
                'nombre'     => trim($nombre),
                'exclude_id' => (int)$excludeId
            ]);
            return $stmt->fetchColumn() !== false;
        }

        $stmt = $this->db()->prepare(
            "SELECT 1 FROM rol WHERE LOWER(nombre_rol) = LOWER(:nombre) LIMIT 1"
        );
        $stmt->execute(['nombre' => trim($nombre)]);
        return $stmt->fetchColumn() !== false;
    }
}
