<?php
/**
 * Vista de Gestión de Roles
 * 
 * @var array $roles Lista de roles registrados.
 * @var string|null $mensaje Mensaje de éxito flash.
 * @var string|null $error Mensaje de error flash.
 */

$roles = isset($roles) ? $roles : [];
$totalRoles = count($roles);

$totalUsuariosSistema = 0;
$rolesSistema = 0;
$rolesPersonalizados = 0;

foreach ($roles as $r) {
    $uCount = isset($r['total_usuarios']) ? (int)$r['total_usuarios'] : 0;
    $totalUsuariosSistema += $uCount;
    if (in_array((int)$r['id_rol'], [1, 2, 3], true)) {
        $rolesSistema++;
    } else {
        $rolesPersonalizados++;
    }
}
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="text-uppercase text-secondary fw-semibold small mb-1" style="font-size: 0.72rem; letter-spacing: 0.06em;">Seguridad y Control de Accesos</div>
            <h1 class="h3 fw-bold mb-1 text-dark">Gestión de Roles</h1>
            <p class="text-secondary small mb-0">Definición de perfiles de usuario y niveles de autorización para los módulos del sistema.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/seguridad/permisos" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-sm">
                <i class="bi bi-shield-check"></i>
                <span>Permisos</span>
            </a>
            <?php if (hasPermission('roles.crear')): ?>
                <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nuevo Rol</span>
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-sm d-inline-flex align-items-center gap-1.5 px-3 py-2 disabled" disabled title="No posee permiso para crear roles">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nuevo Rol</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mensajes Flash de Retroalimentación -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm border" role="alert">
            <i class="bi bi-check2-circle fs-5 flex-shrink-0 text-success"></i>
            <div class="small flex-grow-1 text-dark"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm border" role="alert">
            <i class="bi bi-exclamation-circle fs-5 flex-shrink-0 text-danger"></i>
            <div class="small flex-grow-1 text-dark"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjeta Principal con Tabla de Roles -->
    <div class="card border shadow-sm rounded-3 mb-4" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark fs-6">Listado de Perfiles</h5>
            </div>
            <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
                <input type="text" id="filtroTablaRoles" class="form-control form-control-sm" placeholder="Buscar rol..." style="max-width: 220px;">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaRoles">
                    <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-4 text-secondary text-uppercase fw-semibold" style="width: 75px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 0.72rem; letter-spacing: 0.05em;">Nombre del Rol</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Descripción</th>
                            <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Usuarios</th>
                            <th class="text-end pe-4 text-secondary text-uppercase fw-semibold" style="width: 190px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-secondary">
                                    <p class="mb-1 fw-semibold text-dark">No hay roles registrados en el sistema</p>
                                    <p class="small text-muted mb-3">Comience registrando un nuevo perfil para habilitar permisos.</p>
                                    <?php if (hasPermission('roles.crear')): ?>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearRol">
                                            <i class="bi bi-plus-lg me-1"></i>Nuevo Rol
                                        </button>
                                    <?php endif; ?>
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
                                <tr class="fila-rol">
                                    <td class="ps-4 text-muted font-monospace small">
                                        <?= str_pad((string)$idRol, 2, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-semibold text-dark rol-nombre"><?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ($esProtegido): ?>
                                                <span class="badge bg-light text-secondary border border-secondary-subtle fw-normal py-0.5 px-2" style="font-size: 0.7rem;" title="Rol base protegido del sistema">
                                                    Sistema
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-secondary small rol-desc" style="max-width: 360px; line-height: 1.45;">
                                        <?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-dark fw-medium small font-monospace"><?= $totalUsuarios ?></span>
                                        <span class="text-muted small"><?= ($totalUsuarios === 1) ? 'usuario' : 'usuarios' ?></span>
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <div class="d-inline-flex gap-1.5 align-items-center justify-content-end">
                                            <?php if (hasPermission('roles.permisos')): ?>
                                                <a href="/seguridad/permisos?rol=<?= $idRol ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" title="Configurar Matriz de Permisos">
                                                    <i class="bi bi-shield-check"></i>
                                                    <span>Permisos</span>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 disabled" disabled title="No posee permiso para gestionar permisos">
                                                    <i class="bi bi-shield-check"></i>
                                                    <span>Permisos</span>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (hasPermission('roles.editar')): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditarRol"
                                                        data-id="<?= $idRol ?>"
                                                        data-nombre="<?= htmlspecialchars($nombreRol, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-descripcion="<?= htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8') ?>"
                                                        data-protegido="<?= $esProtegido ? '1' : '0' ?>"
                                                        title="Editar Rol">
                                                    <i class="bi bi-pencil"></i>
                                                    <span>Editar</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 disabled" disabled title="No posee permiso para editar roles">
                                                    <i class="bi bi-pencil"></i>
                                                    <span>Editar</span>
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
        <div class="modal-content border shadow rounded-3" style="border-color: #e2e8f0;">
            <div class="modal-header bg-light border-bottom py-3 px-4" style="border-color: #e2e8f0;">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalCrearRolLabel">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/seguridad/roles/crear" method="POST">
                <?= csrfField(); ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="crear_nombre_rol" class="form-label fw-semibold text-secondary small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="crear_nombre_rol" name="nombre_rol" required autocomplete="off" placeholder="Ej. Inspector de Calidad">
                        <div class="form-text text-muted small">Nombre único para identificar este perfil en el sistema.</div>
                    </div>
                    <div class="mb-0">
                        <label for="crear_descripcion_rol" class="form-label fw-semibold text-secondary small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="crear_descripcion_rol" name="descripcion" rows="3" required placeholder="Describa el alcance de funciones y responsabilidades del perfil..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4" style="border-color: #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <span>Crear Rol</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Rol -->
<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border shadow rounded-3" style="border-color: #e2e8f0;">
            <div class="modal-header bg-light border-bottom py-3 px-4" style="border-color: #e2e8f0;">
                <h5 class="modal-title fw-bold text-dark fs-6" id="modalEditarRolLabel">Editar Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="/seguridad/roles/editar" method="POST">
                <?= csrfField(); ?>
                <input type="hidden" name="id_rol" id="edit_rol_id" value="">
                <div class="modal-body p-4">
                    <div id="edit_rol_protegido_alerta" class="alert alert-warning py-2 px-3 small d-flex align-items-center gap-2 mb-3 d-none">
                        <i class="bi bi-info-circle flex-shrink-0"></i>
                        <span>Los roles base del núcleo del sistema no pueden ser renombrados.</span>
                    </div>
                    <div class="mb-3">
                        <label for="edit_rol_nombre" class="form-label fw-semibold text-secondary small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Nombre del Rol <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_rol_nombre" name="nombre_rol" required autocomplete="off">
                    </div>
                    <div class="mb-0">
                        <label for="edit_rol_descripcion" class="form-label fw-semibold text-secondary small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_rol_descripcion" name="descripcion" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4" style="border-color: #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Manejo de Modal Editar Rol
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

    // Filtrado en vivo de roles por texto
    var inputFiltro = document.getElementById('filtroTablaRoles');
    if (inputFiltro) {
        inputFiltro.addEventListener('input', function () {
            var termino = this.value.toLowerCase().trim();
            var filas = document.querySelectorAll('#tablaRoles tbody tr.fila-rol');
            filas.forEach(function (fila) {
                var nombre = (fila.querySelector('.rol-nombre') ? fila.querySelector('.rol-nombre').textContent : '').toLowerCase();
                var desc = (fila.querySelector('.rol-desc') ? fila.querySelector('.rol-desc').textContent : '').toLowerCase();
                if (termino === '' || nombre.indexOf(termino) !== -1 || desc.indexOf(termino) !== -1) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>
