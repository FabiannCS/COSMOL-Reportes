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
            <h1 class="h2 fw-bold mb-1 text-dark">Gestión de Usuarios</h1>
            <p class="text-muted small mb-0">Administración de cuentas de acceso, asignación de roles y especialidades.</p>
        </div>
        <div>
            <?php if (hasPermission('usuarios.crear')): ?>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Nuevo Usuario</span>
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2 shadow-sm disabled" disabled title="No posee permiso para registrar usuarios">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Nuevo Usuario</span>
                </button>
            <?php endif; ?>
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
    <div class="row g-3 g-xl-4 mb-4">
        <?php
            $statTitle = 'Total Usuarios';
            $statValue = (int)$totalUsuarios;
            $statColor = 'primary';
            $statIcon  = 'bi-people-fill';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Activos';
            $statValue = (int)$activos;
            $statColor = 'success';
            $statIcon  = 'bi-person-check-fill';
            $statValueColor = 'text-success';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Inactivos';
            $statValue = (int)$inactivos;
            $statColor = 'danger';
            $statIcon  = 'bi-person-x-fill';
            $statValueColor = 'text-danger';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Especialidades';
            $statValue = count($porEspecialidad);
            $statColor = 'info';
            $statIcon  = 'bi-diagram-3-fill';
            $statValueColor = 'text-dark';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
    </div>

    <!-- Tarjeta Principal con Tabla de Usuarios -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="tabla-usuarios">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="card-title fw-bold mb-0 text-dark">
                Usuarios Registrados
            </h5>
            <form action="" method="GET" class="d-flex w-auto m-0">
                <input type="text" name="buscar" class="form-control form-control-sm me-2" placeholder="Buscar por usuario..." value="<?= htmlspecialchars($buscar ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
                <?php if (!empty($buscar)): ?>
                    <a href="/administrador/usuarios" class="btn btn-sm btn-outline-secondary ms-1" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-nowrap" style="width: 70px;">ID</th>
                            <th class="text-nowrap">Usuario</th>
                            <th class="text-nowrap">Rol</th>
                            <th class="text-nowrap">Especialidad</th>
                            <th class="text-nowrap">Estado</th>
                            <th class="text-nowrap">Fecha de Registro</th>
                            <th class="text-end pe-4 text-nowrap" style="min-width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-opacity-50"></i>
                                    <h5>No hay usuarios registrados en el sistema.</h5>
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
                                            <div>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if ($esPropio): ?>
                                                    <span class="badge bg-warning bg-opacity-25 text-dark ms-1" style="font-size: 0.75rem;">Tú</span>
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
                                                Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small fw-semibold text-nowrap"><?= $fecha ?></td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <!-- Botón Editar -->
                                            <?php if (hasPermission('usuarios.editar')): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary rounded-pill px-3"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarUsuario"
                                                        data-id="<?= $idUsuario ?>"
                                                        data-username="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-id-rol="<?= $idRol ?>"
                                                        data-id-especialidad="<?= htmlspecialchars(isset($u['id_especialidad']) ? $u['id_especialidad'] : '', ENT_QUOTES, 'UTF-8') ?>"
                                                        title="Editar Usuario">
                                                    <i class="bi bi-pencil-square me-1"></i><span>Editar</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" disabled title="No posee permiso para editar usuarios">
                                                    <i class="bi bi-pencil-square me-1"></i><span>Editar</span>
                                                </button>
                                            <?php endif; ?>

                                            <!-- Botón Cambiar Estado (Toggle) -->
                                            <?php if ($esPropio): ?>
                                                <button type="button" class="btn btn-sm btn-light border text-muted rounded-pill px-2" disabled title="No puedes desactivar tu propia cuenta activa">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            <?php elseif (hasPermission('usuarios.estado')): ?>
                                                <form action="/administrador/usuarios/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de <?= ($estado === 1) ? 'desactivar' : 'activar' ?> al usuario \'<?= htmlspecialchars(addslashes($username), ENT_QUOTES, 'UTF-8') ?>\'?');">
                                                    <?= csrfField(); ?>
                                                    <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                                                    <input type="hidden" name="nuevo_estado" value="<?= ($estado === 1) ? 0 : 1 ?>">
                                                    <?php if ($estado === 1): ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2" title="Desactivar Cuenta">
                                                            <i class="bi bi-toggle-on"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2" title="Activar Cuenta">
                                                            <i class="bi bi-toggle-off"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 disabled" disabled title="No posee permiso para cambiar el estado de usuarios">
                                                    <i class="bi bi-toggle-<?= ($estado === 1) ? 'on' : 'off' ?>"></i>
                                                </button>
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
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 p-3 border-top bg-light">
                    <div class="small text-muted">
                        Página <strong><?= $paginaActual ?></strong> de <strong><?= $totalPaginas ?></strong> (Total: <?= $totalUsuarios ?>)
                    </div>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                            <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?p=<?= $paginaActual - 1 ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>#tabla-usuarios" <?= ($paginaActual <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                                    <a class="page-link" href="?p=<?= $i ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>#tabla-usuarios"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?p=<?= $paginaActual + 1 ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>#tabla-usuarios" <?= ($paginaActual >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
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
                <?= csrfField(); ?>
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
                <?= csrfField(); ?>
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
    // Alternar visibilidad y requerimiento del campo especialidad
    function alternarEspecialidad(selectRol, grupoEsp, selectEsp) {
        if (!selectRol || !grupoEsp) return;
        var selectedOption = selectRol.options[selectRol.selectedIndex];
        var nombreRol = selectedOption ? selectedOption.getAttribute('data-nombre-rol') : '';
        if (nombreRol === 'Operador') {
            grupoEsp.style.display = 'block';
            if (selectEsp) selectEsp.required = true;
        } else {
            grupoEsp.style.display = 'none';
            if (selectEsp) {
                selectEsp.required = false;
                selectEsp.value = '';
            }
        }
    }

    // Modal Crear Usuario
    var selectRolCrear = document.getElementById('crear_id_rol');
    var grupoEspCrear = document.getElementById('grupo_crear_especialidad');
    var selectEspCrear = document.getElementById('crear_id_especialidad');
    if (selectRolCrear) {
        selectRolCrear.addEventListener('change', function () {
            alternarEspecialidad(selectRolCrear, grupoEspCrear, selectEspCrear);
        });
        alternarEspecialidad(selectRolCrear, grupoEspCrear, selectEspCrear);
    }

    // Modal Editar Usuario
    var modalEditar = document.getElementById('modalEditarUsuario');
    var selectRolEdit = document.getElementById('edit_usuario_id_rol');
    var grupoEspEdit = document.getElementById('grupo_edit_especialidad');
    var selectEspEdit = document.getElementById('edit_usuario_id_especialidad');

    if (selectRolEdit) {
        selectRolEdit.addEventListener('change', function () {
            alternarEspecialidad(selectRolEdit, grupoEspEdit, selectEspEdit);
        });
    }

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

            alternarEspecialidad(selectRolEdit, grupoEspEdit, selectEspEdit);
        });
    }
});
</script>
