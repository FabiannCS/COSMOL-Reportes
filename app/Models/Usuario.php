<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    /**
     * Retorna todos los usuarios registrados con su respectivo rol
     *
     * @return array
     */
    public function all()
    {
        $stmt = $this->db()->query(
            "SELECT u.id_usuario, u.username, u.estado, u.fecha_creacion, u.fecha_actualizacion, 
                    u.id_rol, r.nombre_rol, u.id_especialidad, e.nombre as nombre_especialidad 
             FROM usuario u 
             LEFT JOIN rol r ON u.id_rol = r.id_rol 
             LEFT JOIN especialidad e ON u.id_especialidad = e.id_especialidad 
             ORDER BY u.fecha_creacion DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Busca un usuario activo por su username para inicio de sesión
     *
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username)
    {
        $stmt = $this->db()->prepare(
            "SELECT u.id_usuario, u.username, u.password_hash, u.estado, u.id_rol, r.nombre_rol, 
                    u.id_especialidad, e.nombre as nombre_especialidad 
             FROM usuario u 
             LEFT JOIN rol r ON u.id_rol = r.id_rol 
             LEFT JOIN especialidad e ON u.id_especialidad = e.id_especialidad 
             WHERE u.username = :username AND u.estado = 1"
        );
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Busca los datos de un usuario por su ID
     *
     * @param int $idUsuario
     * @return array|false
     */
    public function findById($idUsuario)
    {
        $stmt = $this->db()->prepare(
            "SELECT u.id_usuario, u.username, u.estado, u.fecha_creacion, u.fecha_actualizacion, 
                    u.id_rol, r.nombre_rol, u.id_especialidad, e.nombre as nombre_especialidad 
             FROM usuario u 
             LEFT JOIN rol r ON u.id_rol = r.id_rol 
             LEFT JOIN especialidad e ON u.id_especialidad = e.id_especialidad 
             WHERE u.id_usuario = :id_usuario"
        );
        $stmt->execute(['id_usuario' => (int)$idUsuario]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $estado = isset($data['estado']) ? (int)$data['estado'] : 1;
        $idEspecialidad = !empty($data['id_especialidad']) ? (int)$data['id_especialidad'] : null;

        $stmt = $this->db()->prepare(
            "INSERT INTO usuario (username, password_hash, id_rol, id_especialidad, estado) 
             VALUES (:username, :password_hash, :id_rol, :id_especialidad, :estado)"
        );
        return $stmt->execute([
            'username'        => trim($data['username']),
            'password_hash'   => $passwordHash,
            'id_rol'          => (int)$data['id_rol'],
            'id_especialidad' => $idEspecialidad,
            'estado'          => $estado
        ]);
    }

    /**
     * Actualiza la información de un usuario
     *
     * @param int $id
     * @param array $data ['username', 'id_rol', 'id_especialidad' (opcional), 'password' (opcional)]
     * @return bool
     */
    public function update($id, array $data)
    {
        $idEspecialidad = !empty($data['id_especialidad']) ? (int)$data['id_especialidad'] : null;

        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $this->db()->prepare(
                "UPDATE usuario 
                 SET username = :username, id_rol = :id_rol, id_especialidad = :id_especialidad, 
                     password_hash = :password_hash, fecha_actualizacion = CURRENT_TIMESTAMP 
                 WHERE id_usuario = :id"
            );
            return $stmt->execute([
                'username'        => trim($data['username']),
                'id_rol'          => (int)$data['id_rol'],
                'id_especialidad' => $idEspecialidad,
                'password_hash'   => $passwordHash,
                'id'              => (int)$id
            ]);
        }

        $stmt = $this->db()->prepare(
            "UPDATE usuario 
             SET username = :username, id_rol = :id_rol, id_especialidad = :id_especialidad, 
                 fecha_actualizacion = CURRENT_TIMESTAMP 
             WHERE id_usuario = :id"
        );
        return $stmt->execute([
            'username'        => trim($data['username']),
            'id_rol'          => (int)$data['id_rol'],
            'id_especialidad' => $idEspecialidad,
            'id'              => (int)$id
        ]);
    }

    /**
     * Activa o desactiva la cuenta de un usuario
     *
     * @param int $id
     * @param int $nuevoEstado (1 = Activo, 0 = Inactivo)
     * @return bool
     */
    public function toggleEstado($id, $nuevoEstado)
    {
        $stmt = $this->db()->prepare(
            "UPDATE usuario 
             SET estado = :estado, fecha_actualizacion = CURRENT_TIMESTAMP 
             WHERE id_usuario = :id"
        );
        return $stmt->execute([
            'estado' => (int)$nuevoEstado,
            'id'     => (int)$id
        ]);
    }

    /**
     * Valida si un nombre de usuario ya existe
     *
     * @param string $username
     * @param int|null $excludeId
     * @return bool
     */
    public function existsUsername($username, $excludeId = null)
    {
        if ($excludeId !== null) {
            $stmt = $this->db()->prepare(
                "SELECT 1 FROM usuario WHERE username = :username AND id_usuario != :exclude_id LIMIT 1"
            );
            $stmt->execute([
                'username'   => trim($username),
                'exclude_id' => (int)$excludeId
            ]);
            return $stmt->fetchColumn() !== false;
        }

        $stmt = $this->db()->prepare(
            "SELECT 1 FROM usuario WHERE username = :username LIMIT 1"
        );
        $stmt->execute(['username' => trim($username)]);
        return $stmt->fetchColumn() !== false;
    }
}
