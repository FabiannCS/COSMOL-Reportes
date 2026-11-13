<?php
/**
 * Componente: Tabla de Usuarios para Pantallas Grandes (Desktop)
 * Archivo: app/Views/administrador/partials/usuarios_tabla_desktop.php
 * 
 * Vista tabular de alta densidad (visible en pantallas medianas y grandes >= 768px).
 * 
 * @var array $usuarios
 * @var int $sesionUserId
 */
?>
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 70px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 0.72rem; letter-spacing: 0.05em;">Usuario</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Rol</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 0.72rem; letter-spacing: 0.05em;">Especialidad</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 110px; font-size: 0.72rem; letter-spacing: 0.05em;">Estado</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Registro</th>
                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <?php
                $idUsuario = (int)$u['id_usuario'];
                $username  = $u['username'];
                $nombreRol = isset($u['nombre_rol']) ? $u['nombre_rol'] : 'Sin Rol';
                $idRolU    = isset($u['id_rol']) ? (int)$u['id_rol'] : 0;
                $estado    = (int)$u['estado'];
                $fecha     = !empty($u['fecha_creacion']) ? date('d/m/Y H:i', strtotime($u['fecha_creacion'])) : '—';
                $esPropio  = ($idUsuario === $sesionUserId);

                // Badges por Rol sobrios
                $badgeRolClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                if ($nombreRol === 'Administrador') {
                    $badgeRolClass = 'bg-primary-subtle text-primary border-primary-subtle';
                } elseif ($nombreRol === 'Supervisor') {
                    $badgeRolClass = 'bg-info-subtle text-info-emphasis border-info-subtle';
                } elseif ($nombreRol === 'Operador') {
                    $badgeRolClass = 'bg-dark-subtle text-dark border-dark-subtle';
                }
                ?>
                <tr>
                    <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                        <?= str_pad((string)$idUsuario, 2, '0', STR_PAD_LEFT) ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($esPropio): ?>
                                <span class="badge bg-light text-secondary border border-secondary-subtle fw-normal" style="font-size: 0.7rem;">Tú</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge border fw-normal py-1 px-2 <?= $badgeRolClass ?>">
                            <?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($nombreRol === 'Operador'): ?>
                            <span class="text-dark small fw-medium">
                                <?= htmlspecialchars(isset($u['nombre_especialidad']) && $u['nombre_especialidad'] ? $u['nombre_especialidad'] : 'Sin asignar', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($estado === 1): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal py-1 px-2">
                                Activo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-normal py-1 px-2">
                                Inactivo
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-muted font-monospace small">
                        <?= $fecha ?>
                    </td>
                    <td class="text-end pe-3 pe-sm-4 text-nowrap">
                        <div class="d-inline-flex gap-1.5 align-items-center justify-content-end">
                            <!-- Botón Trabajos Concluidos (Historial del Operador) -->
                            <?php if ($nombreRol === 'Operador' || hasPermission('trabajos.ver')): ?>
                                <a href="/administrador/historial?buscar=<?= urlencode($username) ?>" 
                                   class="btn btn-sm btn-outline-secondary px-2.5 py-1" 
                                   title="Ver trabajos concluidos por <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
                                    Trabajos
                                </a>
                            <?php endif; ?>

                            <!-- Botón Editar -->
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

                            <!-- Botón Cambiar Estado (Activar / Desactivar) -->
                            <?php if ($esPropio): ?>
                                <button type="button" class="btn btn-sm btn-light border text-muted px-2 py-1" disabled title="No puedes desactivar tu propia cuenta activa">
                                    Activo
                                </button>
                            <?php elseif (hasPermission('usuarios.estado')): ?>
                                <form action="/administrador/usuarios/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Confirma que desea <?= ($estado === 1) ? 'desactivar' : 'activar' ?> al usuario \'<?= htmlspecialchars(addslashes($username), ENT_QUOTES, 'UTF-8') ?>\'?');">
                                    <?= csrfField(); ?>
                                    <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                                    <input type="hidden" name="nuevo_estado" value="<?= ($estado === 1) ? 0 : 1 ?>">
                                    <?php if ($estado === 1): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Desactivar Cuenta">
                                            Desactivar
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success px-2 py-1" title="Activar Cuenta">
                                            Activar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
