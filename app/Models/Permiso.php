<?php

namespace App\Models;

use App\Core\Model;
use PDO;
use PDOException;

class Permiso extends Model
{

    public function all()
    {
        $stmt = $this->db()->query(
            "SELECT id_permiso, clave_permiso, nombre_permiso, descripcion, modulo, fecha_creacion 
             FROM permiso 
             ORDER BY modulo ASC, id_permiso ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Retorna el catálogo de permisos agrupado por módulo
     *
     * @return array Array asociativo ['Seguridad' => [...], 'Operaciones' => [...]]
     */
    public function allGroupedByModule()
    {
        $permisos = $this->all();
        $agrupados = [];

        foreach ($permisos as $permiso) {
            $modulo = $permiso['modulo'];
            if (!isset($agrupados[$modulo])) {
                $agrupados[$modulo] = [];
            }
            $agrupados[$modulo][] = $permiso;
        }

        return $agrupados;
    }

    public function getIdsByRol($idRol)
    {
        $stmt = $this->db()->prepare(
            "SELECT id_permiso FROM rol_permiso WHERE id_rol = :id_rol"
        );
        $stmt->execute(['id_rol' => (int)$idRol]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Retorna los nombres clave de los permisos asignados a un rol.
     *
     * @param int $idRol
     * @return array Array de strings con las claves de permisos (ej. ['usuarios.ver', 'trabajos.concluir'])
     */
    public function getClavesByRol($idRol)
    {
        $stmt = $this->db()->prepare(
            "SELECT p.clave_permiso 
             FROM permiso p 
             INNER JOIN rol_permiso rp ON p.id_permiso = rp.id_permiso 
             WHERE rp.id_rol = :id_rol"
        );
        $stmt->execute(['id_rol' => (int)$idRol]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function syncRolPermisos($idRol, array $permisosIds)
    {
        $db = $this->db();

        try {
            $db->beginTransaction();

            $stmtDelete = $db->prepare("DELETE FROM rol_permiso WHERE id_rol = :id_rol");
            $stmtDelete->execute(['id_rol' => (int)$idRol]);

            if (!empty($permisosIds)) {
                $stmtInsert = $db->prepare(
                    "INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (:id_rol, :id_permiso)"
                );

                foreach ($permisosIds as $idPermiso) {
                    if (is_numeric($idPermiso) && (int)$idPermiso > 0) {
                        $stmtInsert->execute([
                            'id_rol'     => (int)$idRol,
                            'id_permiso' => (int)$idPermiso
                        ]);
                    }
                }
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error al sincronizar permisos de rol: " . $e->getMessage());
            return false;
        }
    }
}
