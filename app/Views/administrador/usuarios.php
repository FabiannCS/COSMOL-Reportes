<?php
/**
 * Vistas de Gestión de Usuarios
 * 
 * @var array $usuarios Lista de usuarios registrados.
 * @var array $roles Lista de roles disponibles para el select.
 * @var array $especialidades Lista de especialidades para operadores.
 * @var string|null $mensaje Mensaje de éxito flash.
 * @var string|null $error Mensaje de error flash.
 * @var string $totalUsuarios Total de usuarios registrados.
 * @var string $activos Total de usuarios activos.
 * @var string $inactivos Total de usuarios inactivos.
 * @var array $porEspecialidad Total de usuarios por especialidad.
 * @var int $paginaActual Página actual de la tabla.
 * @var int $totalPaginas Total de páginas disponibles.
 */
$sesionUserId = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Gestión de Usuarios</h1>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                <i class="bi bi-person-plus-fill"></i>
                <span>Nuevo Usuario</span>
            </button>
        </div>
    </div>

    <!-- Mensajes Flash de Retroalimentación -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de Métricas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 me-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fs-5">Total Usuarios</div>
                        <div class="h4 mb-0 fw-bold"><?= (int)$totalUsuarios ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 me-3">
                        <i class="bi bi-person-check-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fs-5">Activos</div>
                        <div class="h4 mb-0 fw-bold"><?= (int)$activos ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 me-3">
                        <i class="bi bi-person-x-fill text-danger fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fs-5">Inactivos</div>
                        <div class="h4 mb-0 fw-bold text-danger"><?= (int)$inactivos ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 me-3">
                        <i class="bi bi-diagram-3-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fs-5">Especialidades</div>
                        <div class="h4 mb-0 fw-bold"><?= count($porEspecialidad) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta Principal con Tabla de Usuarios -->
    <div class="card border-0 shadow-sm rounded-3" id="tabla-usuarios">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0 text-dark">
                Usuarios Registrados
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 70px;">ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Fecha de Registro</th>
                            <th class="text-end pe-4" style="min-width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No hay usuarios registrados en el sistema.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <?php
                                $idUsuario = (int)$u['id_usuario'];
                                $username  = $u['username'];
                                $nombreRol = isset($u['nombre_rol']) ? $u['nombre_rol'] : 'Sin Rol';
                                $idRol     = isset($u['id_rol']) ? (int)$u['id_rol'] : 0;
                                $estado    = (int)$u['estado'];
                                $fecha     = !empty($u['fecha_creacion']) ? date('d/m/Y H:i', strtotime($u['fecha_creacion'])) : '—';
                                $esPropio  = ($idUsuario === $sesionUserId);

                                // Badges por Rol
                                $badgeRolClass = 'bg-secondary';
                                if ($nombreRol === 'Administrador') {
                                    $badgeRolClass = 'bg-primary';
                                } elseif ($nombreRol === 'Supervisor') {
                                    $badgeRolClass = 'bg-info text-dark';
                                } elseif ($nombreRol === 'Operador') {
                                    $badgeRolClass = 'bg-secondary';
                                }
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold"><?= $idUsuario ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px;">
                                                <i class="bi bi-person-fill text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></div>
                                                <?php if ($esPropio): ?>
                                                    <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.80rem;">Tú</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeRolClass ?>">
                                            <?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($nombreRol === 'Operador'): ?>
                                            <span class="text-dark fw-bold" style="font-size: 0.90rem;">
                                                <?= htmlspecialchars(isset($u['nombre_especialidad']) && $u['nombre_especialidad'] ? $u['nombre_especialidad'] : 'Sin asignar', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($estado === 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.85rem;">
                                                <i class="bi bi-check-circle me-1"></i>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                <i class="bi bi-x-circle me-1"></i>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small fw-bold"><?= $fecha ?></td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <!-- Botón Editar -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditarUsuario"
                                                    data-id="<?= $idUsuario ?>"
                                                    data-username="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-id-rol="<?= $idRol ?>"
                                                    data-id-especialidad="<?= htmlspecialchars(isset($u['id_especialidad']) ? $u['id_especialidad'] : '', ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Editar Usuario">
                                                Editar
                                            </button>

                                            <!-- Botón Cambiar Estado (Toggle) -->
                                            <?php if ($esPropio): ?>
                                                <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="No puedes desactivar tu propia cuenta activa">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            <?php else: ?>
                                                <form action="/administrador/usuarios/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de <?= ($estado === 1) ? 'desactivar' : 'activar' ?> al usuario \'<?= htmlspecialchars(addslashes($username), ENT_QUOTES, 'UTF-8') ?>\'?');">
                                                    <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                                                    <input type="hidden" name="nuevo_estado" value="<?= ($estado === 1) ? 0 : 1 ?>">
                                                    <?php if ($estado === 1): ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Desactivar Cuenta">
                                                            <i class="bi bi-toggle-on"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activar Cuenta">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                <div class="d-flex justify-content-center mt-4 pb-3">
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination mb-0">
                            <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?p=<?= $paginaActual - 1 ?>#tabla-usuarios" <?= ($paginaActual <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                                    <a class="page-link" href="?p=<?= $i ?>#tabla-usuarios"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?p=<?= $paginaActual + 1 ?>#tabla-usuarios" <?= ($paginaActual >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Crear Nuevo Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalCrearUsuarioLabel">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/administrador/usuarios/crear" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="crear_username" class="form-label fw-semibold text-dark">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="crear_username" name="username" required autocomplete="off" placeholder="ej. jgomez">
                    </div>
                    <div class="mb-3">
                        <label for="crear_id_rol" class="form-label fw-semibold text-dark">Rol Asignado <span class="text-danger">*</span></label>
                        <select class="form-select" id="crear_id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id_rol'] ?>" data-nombre-rol="<?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo_crear_especialidad">
                        <label for="crear_id_especialidad" class="form-label fw-semibold text-dark">Especialidad (Operador)</label>
                        <select class="form-select" id="crear_id_especialidad" name="id_especialidad">
                            <option value="">Seleccione una especialidad...</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= (int)$esp['id_especialidad'] ?>">
                                    <?= htmlspecialchars($esp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Requerido si el usuario tiene rol de Operador.</div>
                    </div>
                    <div class="mb-3">
                        <label for="crear_password" class="form-label fw-semibold text-dark">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="crear_password" name="password" minlength="6" required autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                        <div class="form-text small">La contraseña se guardará encriptada con algoritmo BCRYPT.</div>
                    </div>
                    <div class="mb-0">
                        <label for="crear_estado" class="form-label fw-semibold text-dark">Estado Inicial</label>
                        <select class="form-select" id="crear_estado" name="estado">
                            <option value="1" selected>Activo (con acceso inmediato)</option>
                            <option value="0">Inactivo (bloqueado)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarUsuarioLabel">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/administrador/usuarios/editar" method="POST">
                <input type="hidden" name="id_usuario" id="edit_usuario_id" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_usuario_username" class="form-label fw-semibold text-dark">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_usuario_username" name="username" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="edit_usuario_id_rol" class="form-label fw-semibold text-dark">Rol Asignado <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_usuario_id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id_rol'] ?>" data-nombre-rol="<?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="grupo_edit_especialidad">
                        <label for="edit_usuario_id_especialidad" class="form-label fw-semibold text-dark">Especialidad (Operador)</label>
                        <select class="form-select" id="edit_usuario_id_especialidad" name="id_especialidad">
                            <option value="">Seleccione una especialidad...</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= (int)$esp['id_especialidad'] ?>">
                                    <?= htmlspecialchars($esp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Determina qué API de trabajos consultará el operador.</div>
                    </div>
                    <div class="mb-0">
                        <label for="edit_usuario_password" class="form-label fw-semibold text-dark">Nueva Contraseña (Opcional)</label>
                        <input type="password" class="form-control" id="edit_usuario_password" name="password" minlength="6" autocomplete="new-password" placeholder="••••••••">
                        <div class="form-text small text-muted">Dejar en blanco para mantener la contraseña actual del usuario.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEditar = document.getElementById('modalEditarUsuario');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;

            var idUsuario      = button.getAttribute('data-id');
            var username       = button.getAttribute('data-username');
            var idRol          = button.getAttribute('data-id-rol');
            var idEspecialidad = button.getAttribute('data-id-especialidad');

            document.getElementById('edit_usuario_id').value = idUsuario || '';
            document.getElementById('edit_usuario_username').value = username || '';
            document.getElementById('edit_usuario_id_rol').value = idRol || '';
            document.getElementById('edit_usuario_id_especialidad').value = idEspecialidad || '';
            document.getElementById('edit_usuario_password').value = '';
        });
    }
});
</script>
