<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TrabajoSeguimiento extends Model {
    protected $table = 'trabajo_seguimiento';
    protected $primaryKey = 'id';

    public function __construct() {
        try {
            // Migración defensiva idempotente para servidores con volúmenes de PostgreSQL preexistentes
            $this->db()->exec("
                CREATE TABLE IF NOT EXISTS trabajo_seguimiento (
                    id SERIAL PRIMARY KEY,
                    id_trabajo INT NOT NULL,
                    tipo_trabajo VARCHAR(50) NOT NULL,
                    estado_interno VARCHAR(50) NOT NULL,
                    glosa_interna TEXT,
                    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE (id_trabajo, tipo_trabajo)
                );
                CREATE INDEX IF NOT EXISTS idx_trabajo_seguimiento_tipo_id ON trabajo_seguimiento(tipo_trabajo, id_trabajo);
                ALTER TABLE trabajo_seguimiento ADD COLUMN IF NOT EXISTS id_usuario INT;
                ALTER TABLE trabajo_seguimiento ADD COLUMN IF NOT EXISTS nombre_operador VARCHAR(150);
            ");
        } catch (\Exception $e) {
            // Silencioso ante contingencias de permisos
        }
    }

    /**
     * Registra o actualiza el estado de seguimiento de un trabajo.
     * 
     * @param int $idTrabajo ID del reclamo o reconexión
     * @param string $tipoTrabajo 'reclamo' o 'reconexion'
     * @param string $estadoInterno 'NO CONCLUIDO' o 'NO PROCEDENTE'
     * @param string $glosaInterna Opcional
     * @param int|null $idUsuario ID del operador que registra
     * @param string $nombreOperador Nombre/username del operador
     * @return bool
     */
    public function registrarSeguimiento($idTrabajo, $tipoTrabajo, $estadoInterno, $glosaInterna = '', $idUsuario = null, $nombreOperador = '') {
        $sql = "INSERT INTO {$this->table} (id_trabajo, tipo_trabajo, estado_interno, glosa_interna, id_usuario, nombre_operador) 
                VALUES (:id_trabajo, :tipo_trabajo, :estado_interno, :glosa_interna, :id_usuario, :nombre_operador)
                ON CONFLICT (id_trabajo, tipo_trabajo) 
                DO UPDATE SET 
                    estado_interno = EXCLUDED.estado_interno,
                    glosa_interna = EXCLUDED.glosa_interna,
                    id_usuario = EXCLUDED.id_usuario,
                    nombre_operador = EXCLUDED.nombre_operador,
                    fecha_registro = CURRENT_TIMESTAMP";

        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            ':id_trabajo' => $idTrabajo,
            ':tipo_trabajo' => $tipoTrabajo,
            ':estado_interno' => $estadoInterno,
            ':glosa_interna' => $glosaInterna,
            ':id_usuario' => $idUsuario,
            ':nombre_operador' => $nombreOperador
        ]);
    }

    /**
     * Obtiene el seguimiento de un trabajo específico.
     * 
     * @param int $idTrabajo
     * @param string $tipoTrabajo
     * @return object|false
     */
    public function obtenerSeguimiento($idTrabajo, $tipoTrabajo) {
        $sql = "SELECT ts.*, u.username AS usuario_username 
                FROM {$this->table} ts
                LEFT JOIN usuario u ON ts.id_usuario = u.id_usuario
                WHERE ts.id_trabajo = :id_trabajo AND ts.tipo_trabajo = :tipo_trabajo";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            ':id_trabajo' => $idTrabajo,
            ':tipo_trabajo' => $tipoTrabajo
        ]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene todos los seguimientos para un tipo de trabajo específico.
     * Útil para adjuntar a listas.
     * 
     * @param string $tipoTrabajo
     * @return array
     */
    public function obtenerTodosPorTipo($tipoTrabajo) {
        $sql = "SELECT ts.*, u.username AS usuario_username 
                FROM {$this->table} ts
                LEFT JOIN usuario u ON ts.id_usuario = u.id_usuario
                WHERE ts.tipo_trabajo = :tipo_trabajo";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([':tipo_trabajo' => $tipoTrabajo]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene todos los seguimientos registrados.
     * 
     * @return array
     */
    public function obtenerTodosSeguimientos() {
        $sql = "SELECT ts.*, u.username AS usuario_username 
                FROM {$this->table} ts
                LEFT JOIN usuario u ON ts.id_usuario = u.id_usuario";
        $stmt = $this->db()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Elimina el seguimiento una vez que el trabajo se concluye de verdad.
     * 
     * @param int $idTrabajo
     * @param string $tipoTrabajo
     * @return bool
     */
    public function eliminarSeguimiento($idTrabajo, $tipoTrabajo) {
        $sql = "DELETE FROM {$this->table} WHERE id_trabajo = :id_trabajo AND tipo_trabajo = :tipo_trabajo";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            ':id_trabajo' => $idTrabajo,
            ':tipo_trabajo' => $tipoTrabajo
        ]);
    }
}
