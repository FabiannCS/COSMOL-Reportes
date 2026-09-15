<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class TrabajoSeguimiento extends Model {
    protected $table = 'trabajo_seguimiento';
    protected $primaryKey = 'id';

    /**
     * Registra o actualiza el estado de seguimiento de un trabajo.
     * 
     * @param int $idTrabajo ID del reclamo o reconexión
     * @param string $tipoTrabajo 'reclamo' o 'reconexion'
     * @param string $estadoInterno 'NO CONCLUIDO' o 'NO PROCEDENTE'
     * @param string $glosaInterna Opcional
     * @return bool
     */
    public function registrarSeguimiento($idTrabajo, $tipoTrabajo, $estadoInterno, $glosaInterna = '') {
        $sql = "INSERT INTO {$this->table} (id_trabajo, tipo_trabajo, estado_interno, glosa_interna) 
                VALUES (:id_trabajo, :tipo_trabajo, :estado_interno, :glosa_interna)
                ON CONFLICT (id_trabajo, tipo_trabajo) 
                DO UPDATE SET 
                    estado_interno = EXCLUDED.estado_interno,
                    glosa_interna = EXCLUDED.glosa_interna,
                    fecha_registro = CURRENT_TIMESTAMP";

        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            ':id_trabajo' => $idTrabajo,
            ':tipo_trabajo' => $tipoTrabajo,
            ':estado_interno' => $estadoInterno,
            ':glosa_interna' => $glosaInterna
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
        $sql = "SELECT * FROM {$this->table} WHERE id_trabajo = :id_trabajo AND tipo_trabajo = :tipo_trabajo";
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
        $sql = "SELECT * FROM {$this->table} WHERE tipo_trabajo = :tipo_trabajo";
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
        $sql = "SELECT * FROM {$this->table}";
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
