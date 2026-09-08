<?php
/**
 * Vistas de Gestión de Roles
 * 
 * @var array $roles Lista de roles registrados.
 * @var string|null $mensaje Mensaje de éxito flash.
 * @var string|null $error Mensaje de error flash.
 */
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1 text-dark">Gestión de Roles</h1>
            <p class="text-muted small mb-0">Definición de perfiles de usuario y niveles de autorización para los módulos.</p>
        </div>
        <div>
            <?php if (hasPermission('roles.crear')): ?>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
                    <i class="bi bi-shield-plus"></i>
                    <span>Nuevo Rol</span>
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2 shadow-sm disabled" disabled title="No posee permiso para crear roles">
                    <i class="bi bi-shield-plus"></i>
                    <span>Nuevo Rol</span>
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

    <!-- Tarjeta Principal con Tabla de Roles -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="bi bi-shield-shaded me-2 text-primary"></i>Roles Configurados
            </h5>
            <span class="badge bg-primary rounded-pill px-3 py-2">
                Total: <?= count($roles) ?> roles
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-nowrap" style="width: 70px;">ID</th>
                            <th class="text-nowrap">Nombre del Rol</th>
                            <th class="text-nowrap">Descripción</th>
                            <th class="text-nowrap">Usuarios Asignados</th>
                            <th class="text-end pe-4 text-nowrap" style="min-width: 200px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 text-opacity-50"></i>
                                    <h5>No hay roles registrados en el sistema.</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roles as $rol): ?>
                                <?php
                                $idRol         = (int)$rol['id_rol'];
                                $nombreRol     = $rol['nombre_rol'];
                                $descripcion   = isset($rol['descripcion']) ? $rol['descripcion'] : '';
                                $totalUsuarios = isset($rol['total_usuarios']) ? (int)$rol['total_usuarios'] : 0;
                                $esProtegido   = in_array($idRol, [1, 2, 3], true);
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold"><?= $idRol ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ($esProtegido): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2" title="Rol protegido del sistema">
                                                    <i class="bi bi-lock-fill me-1"></i>Sistema
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-muted" style="max-width: 300px;">
                                        <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-3 py-1">
                                            <i class="bi bi-people-fill me-1"></i>
                                            <?= $totalUsuarios ?> <?= ($totalUsuarios === 1) ? 'usuario' : 'usuarios' ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <?php if (hasPermission('roles.permisos')): ?>
                                                <a href="/seguridad/permisos?rol=<?= $idRol ?>" class="btn btn-sm btn-primary rounded-pill px-3" title="Gestionar Permisos">
                                                    <i class="bi bi-shield-lock-fill me-1"></i><span>Permisos</span>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" disabled title="No posee permiso para gestionar permisos">
                                                    <i class="bi bi-shield-lock-fill me-1"></i><span>Permisos</span>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (hasPermission('roles.editar')): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarRol"
                                                        data-id="<?= $idRol ?>"
                                                        data-nombre="<?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-descripcion="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-protegido="<?= $esProtegido ? '1' : '0' ?>"
                                                        title="Editar Rol">
                                                    <i class="bi bi-pencil-square me-1"></i><span>Editar</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 disabled" disabled title="No posee permiso para editar roles">
                                                    <i class="bi bi-pencil-square me-1"></i><span>Editar</span>
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
        </div>
    </div>
</div>

<!-- Modal: Crear Nuevo Rol -->
<div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalCrearRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalCrearRolLabel">
                    <i class="bi bi-shield-plus me-2 text-primary"></i>Nuevo Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/seguridad/roles/crear" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="crear_nombre_rol" class="form-label fw-semibold text-dark">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="crear_nombre_rol" name="nombre_rol" required autocomplete="off" placeholder="ej. Inspector de Calidad">
                    </div>
                    <div class="mb-0">
                        <label for="crear_descripcion_rol" class="form-label fw-semibold text-dark">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="crear_descripcion_rol" name="descripcion" rows="3" required placeholder="Detalle el alcance y responsabilidades de este perfil..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Crear Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Rol -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarRolLabel">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Rol
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/seguridad/roles/editar" method="POST">
                <input type="hidden" name="id_rol" id="edit_rol_id" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_rol_nombre" class="form-label fw-semibold text-dark">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_rol_nombre" name="nombre_rol" required autocomplete="off">
                        <div id="edit_rol_protegido_alerta" class="form-text text-warning d-none">
                            <i class="bi bi-info-circle me-1"></i>Los roles del sistema no pueden ser renombrados.
                        </div>
                    </div>
                    <div class="mb-0">
                        <label for="edit_rol_descripcion" class="form-label fw-semibold text-dark">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_rol_descripcion" name="descripcion" rows="3" required></textarea>
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
    var modalEditarRol = document.getElementById('modalEditarRol');
    if (modalEditarRol) {
        modalEditarRol.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;

            var idRol       = button.getAttribute('data-id');
            var nombre      = button.getAttribute('data-nombre');
            var descripcion = button.getAttribute('data-descripcion');
            var protegido   = button.getAttribute('data-protegido') === '1';

            var inputId          = document.getElementById('edit_rol_id');
            var inputNombre      = document.getElementById('edit_rol_nombre');
            var inputDescripcion = document.getElementById('edit_rol_descripcion');
            var alertaProtegido  = document.getElementById('edit_rol_protegido_alerta');

            if (inputId) inputId.value = idRol || '';
            if (inputNombre) {
                inputNombre.value = nombre || '';
                if (protegido) {
                    inputNombre.setAttribute('readonly', 'readonly');
                } else {
                    inputNombre.removeAttribute('readonly');
                }
            }
            if (inputDescripcion) inputDescripcion.value = descripcion || '';
            if (alertaProtegido) {
                if (protegido) {
                    alertaProtegido.classList.remove('d-none');
                } else {
                    alertaProtegido.classList.add('d-none');
                }
            }
        });
    }
});
</script>
