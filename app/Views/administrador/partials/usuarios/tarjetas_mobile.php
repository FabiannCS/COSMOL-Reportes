<?php
/**
 * Componente: Fichas de Usuarios para Pantallas Móviles (< 768px)
 * Archivo: app/Views/administrador/partials/usuarios_tarjetas_mobile.php
 * 
 * Tarjetas táctiles adaptadas para teléfonos y pantallas pequeñas.
 * 
 * @var array $usuarios
 * @var int $sesionUserId
 */
?>
<div class="d-block d-md-none p-3" id="listaMobileUsuarios">
    <?php foreach ($usuarios as $u): ?>
        <?php
        $idUsuario = (int)$u['id_usuario'];
        $username  = $u['username'];
        $nombreRol = isset($u['nombre_rol']) ? $u['nombre_rol'] : 'Sin Rol';
        $idRolU    = isset($u['id_rol']) ? (int)$u['id_rol'] : 0;
        $estado    = (int)$u['estado'];
        $fecha     = !empty($u['fecha_creacion']) ? date('d/m/Y H:i', strtotime($u['fecha_creacion'])) : '—';
        $esPropio  = ($idUsuario === $sesionUserId);

        $badgeRolClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
        if ($nombreRol === 'Administrador') {
            $badgeRolClass = 'bg-primary-subtle text-primary border-primary-subtle';
        } elseif ($nombreRol === 'Supervisor') {
            $badgeRolClass = 'bg-info-subtle text-info-emphasis border-info-subtle';
        } elseif ($nombreRol === 'Operador') {
            $badgeRolClass = 'bg-dark-subtle text-dark border-dark-subtle';
        }
        ?>
        <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="font-monospace fw-bold text-muted small">#<?= str_pad((string)$idUsuario, 2, '0', STR_PAD_LEFT) ?></span>
                    <span class="badge border fw-normal py-0.5 px-2 <?= $badgeRolClass ?>" style="font-size: 0.72rem;">
                        <?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <?php if ($estado === 1): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">Activo</span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-normal">Inactivo</span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-1.5">
                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                <?php if ($esPropio): ?>
                    <span class="badge bg-light text-secondary border border-secondary-subtle fw-normal" style="font-size: 0.7rem;">Tú</span>
                <?php endif; ?>
            </div>

            <?php if ($nombreRol === 'Operador'): ?>
                <div class="small text-secondary mt-1">
                    <span class="text-muted">Especialidad:</span>
                    <span class="fw-medium text-dark"><?= htmlspecialchars(isset($u['nombre_especialidad']) && $u['nombre_especialidad'] ? $u['nombre_especialidad'] : 'Sin asignar', ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <div class="small text-muted mt-2 pt-2 border-top font-monospace" style="border-color: #f1f5f9; font-size: 0.78rem;">
                Registrado: <?= $fecha ?>
            </div>

            <div class="d-flex flex-wrap justify-content-end align-items-center gap-1.5 mt-3 pt-2 border-top" style="border-color: #f1f5f9;">
                <?php if ($nombreRol === 'Operador' || hasPermission('trabajos.ver')): ?>
                    <a href="/administrador/historial?buscar=<?= urlencode($username) ?>" 
                       class="btn btn-sm btn-outline-secondary px-2.5 py-1">
                        Trabajos
                    </a>
                <?php endif; ?>

                <?php if (hasPermission('usuarios.editar')): ?>
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary px-2.5 py-1"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalEditarUsuario"
                            data-id="<?= $idUsuario ?>"
                            data-username="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                            data-id-rol="<?= $idRolU ?>"
                            data-id-especialidad="<?= htmlspecialchars(isset($u['id_especialidad']) ? $u['id_especialidad'] : '', ENT_QUOTES, 'UTF-8') ?>"
                            title="Editar Usuario">
                        Editar
                    </button>
                <?php endif; ?>

                <?php if (!$esPropio && hasPermission('usuarios.estado')): ?>
                    <form action="/administrador/usuarios/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Confirma que desea <?= ($estado === 1) ? 'desactivar' : 'activar' ?> al usuario \'<?= htmlspecialchars(addslashes($username), ENT_QUOTES, 'UTF-8') ?>\'?');">
                        <?= csrfField(); ?>
                        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                        <input type="hidden" name="nuevo_estado" value="<?= ($estado === 1) ? 0 : 1 ?>">
                        <?php if ($estado === 1): ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1">
                                Desactivar
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-sm btn-outline-success px-2.5 py-1">
                                Activar
                            </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
