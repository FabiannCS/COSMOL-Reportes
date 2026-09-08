<?php
/**
 * Vistas de Gestión de Permisos por Rol
 * 
 * @var array $roles Lista de roles para el selector.
 * @var array $rolActual Datos del rol seleccionado actualmente.
 * @var array $permisosAsignados Array simple con IDs de permisos asignados al rol.
 * @var array $permisosAgrupados Array multidimensional de permisos agrupados por módulo.
 * @var string|null $mensaje Mensaje de éxito flash.
 * @var string|null $error Mensaje de error flash.
 */
$idRolActual = isset($rolActual['id_rol']) ? (int)$rolActual['id_rol'] : 0;
$nombreRolActual = isset($rolActual['nombre_rol']) ? $rolActual['nombre_rol'] : 'Rol';
$descripcionRolActual = isset($rolActual['descripcion']) ? $rolActual['descripcion'] : '';
$esRolProtegido = in_array($idRolActual, [1, 2, 3], true);
$totalPermisosAsignados = is_array($permisosAsignados) ? count($permisosAsignados) : 0;
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold mb-0 text-dark">Matriz de Permisos</h1>
                <?php if ($esRolProtegido): ?>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                        <i class="bi bi-lock-fill me-1"></i>Rol del Sistema
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">Configure los privilegios y capacidades de acceso para cada rol en la plataforma.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/seguridad/roles" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i>
                <span>Volver a Roles</span>
            </a>
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

    <!-- Pestañas de Selección de Rol -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase mb-2">Seleccionar Rol a Configurar:</div>
                    <ul class="nav nav-pills gap-2">
                        <?php foreach ($roles as $r): ?>
                            <?php 
                            $esActivo = ((int)$r['id_rol'] === $idRolActual);
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $esActivo ? 'active' : 'bg-light text-dark border' ?> d-flex align-items-center gap-2 px-3 py-2" 
                                   href="/seguridad/permisos?rol=<?= (int)$r['id_rol'] ?>">
                                    <i class="bi bi-shield-shaded"></i>
                                    <span class="fw-semibold"><?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Guardado de Permisos -->
    <form action="/seguridad/roles/permisos" method="POST" id="formPermisos">
        <input type="hidden" name="id_rol" value="<?= $idRolActual ?>">

        <!-- Banner Informativo del Rol Seleccionado y Barra de Acciones -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 border-start border-primary border-4">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock text-primary"></i>
                        Permisos para: <?= htmlspecialchars($nombreRolActual, ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($descripcionRolActual, ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" id="contadorPermisosBadge">
                            <i class="bi bi-check2-circle me-1"></i><span id="contadorPermisos"><?= $totalPermisosAsignados ?></span> permisos asignados
                        </span>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if (hasPermission('roles.permisos')): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMarcarTodosGlobal">
                            <i class="bi bi-check-all me-1"></i>Marcar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDesmarcarTodosGlobal">
                            <i class="bi bi-dash-square me-1"></i>Desmarcar Todos
                        </button>
                        <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-save"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary disabled" disabled>
                            <i class="bi bi-check-all me-1"></i>Marcar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary disabled" disabled>
                            <i class="bi bi-dash-square me-1"></i>Desmarcar Todos
                        </button>
                        <button type="button" class="btn btn-secondary px-4 d-inline-flex align-items-center gap-2 shadow-sm disabled" disabled title="No posee permiso para modificar la matriz de permisos">
                            <i class="bi bi-save"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Módulos de Permisos -->
        <?php if (empty($permisosAgrupados)): ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-shield-slash fs-1 d-block mb-2"></i>
                    No existen permisos registrados en el catálogo del sistema.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($permisosAgrupados as $modulo => $permisos): ?>
                <?php
                $moduloKey = md5($modulo);
                $moduloIcon = 'bi-folder-fill';
                $moduloColor = 'text-primary';
                $moduloBg = 'bg-primary';

                if ($modulo === 'Seguridad') {
                    $moduloIcon = 'bi-shield-check';
                    $moduloColor = 'text-primary';
                    $moduloBg = 'bg-primary';
                } elseif ($modulo === 'Operaciones') {
                    $moduloIcon = 'bi-tools';
                    $moduloColor = 'text-warning';
                    $moduloBg = 'bg-warning text-dark';
                } elseif ($modulo === 'Administración') {
                    $moduloIcon = 'bi-gear-fill';
                    $moduloColor = 'text-secondary';
                    $moduloBg = 'bg-secondary';
                } elseif ($modulo === 'Reportes') {
                    $moduloIcon = 'bi-file-earmark-bar-graph-fill';
                    $moduloColor = 'text-info';
                    $moduloBg = 'bg-info text-dark';
                }
                ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?= $moduloIcon ?> <?= $moduloColor ?> fs-5"></i>
                            <h6 class="card-title fw-bold mb-0 text-dark">Módulo <?= htmlspecialchars($modulo, ENT_QUOTES, 'UTF-8') ?></h6>
                            <span class="badge bg-light text-dark border ms-1"><?= count($permisos) ?> acciones</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary btn-marcar-modulo" data-target="<?= $moduloKey ?>">
                                Marcar módulo
                            </button>
                            <span class="text-muted">|</span>
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-muted btn-desmarcar-modulo" data-target="<?= $moduloKey ?>">
                                Desmarcar módulo
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <?php foreach ($permisos as $p): ?>
                                <?php
                                $idPermiso = (int)$p['id_permiso'];
                                $clave     = $p['clave_permiso'];
                                $nombre    = $p['nombre_permiso'];
                                $desc      = isset($p['descripcion']) ? $p['descripcion'] : '';
                                $estaMarcado = in_array($idPermiso, $permisosAsignados, true);
                                ?>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100 transition-all permission-card">
                                        <div class="form-check form-switch d-flex align-items-start gap-2 ps-0 mb-0">
                                            <input class="form-check-input ms-0 mt-1 flex-shrink-0 permission-checkbox module-check-<?= $moduloKey ?>" 
                                                   type="checkbox" 
                                                   name="permisos[]" 
                                                   value="<?= $idPermiso ?>" 
                                                   id="permiso_<?= $idPermiso ?>"
                                                   style="cursor: pointer;"
                                                   <?= $estaMarcado ? 'checked' : '' ?>
                                                   <?= hasPermission('roles.permisos') ? '' : 'disabled' ?>>
                                            <label class="form-check-label flex-grow-1" for="permiso_<?= $idPermiso ?>" style="cursor: pointer;">
                                                <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></div>
                                                <code class="small text-primary bg-white px-1 py-1 rounded border border-primary border-opacity-25"><?= htmlspecialchars($clave, ENT_QUOTES, 'UTF-8') ?></code>
                                                <?php if (!empty($desc)): ?>
                                                    <div class="text-muted small mt-1"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></div>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Barra Inferior Fija de Guardado -->
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>Los cambios en los permisos tendrán efecto en el próximo inicio de sesión o actualización del usuario.
                </span>
                <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-save"></i>
                    <span>Guardar Permisos</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkboxes = document.querySelectorAll('.permission-checkbox');
    var contadorElem = document.getElementById('contadorPermisos');

    function actualizarContador() {
        if (!contadorElem) return;
        var totalMarcados = document.querySelectorAll('.permission-checkbox:checked').length;
        contadorElem.textContent = totalMarcados;
    }

    checkboxes.forEach(function (chk) {
        chk.addEventListener('change', actualizarContador);
    });

    // Botones de Marcar/Desmarcar por Módulo
    document.querySelectorAll('.btn-marcar-modulo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = this.getAttribute('data-target');
            document.querySelectorAll('.module-check-' + target).forEach(function (chk) {
                chk.checked = true;
            });
            actualizarContador();
        });
    });

    document.querySelectorAll('.btn-desmarcar-modulo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = this.getAttribute('data-target');
            document.querySelectorAll('.module-check-' + target).forEach(function (chk) {
                chk.checked = false;
            });
            actualizarContador();
        });
    });

    // Botones Globales
    var btnMarcarGlobal = document.getElementById('btnMarcarTodosGlobal');
    var btnDesmarcarGlobal = document.getElementById('btnDesmarcarTodosGlobal');

    if (btnMarcarGlobal) {
        btnMarcarGlobal.addEventListener('click', function () {
            checkboxes.forEach(function (chk) {
                chk.checked = true;
            });
            actualizarContador();
        });
    }

    if (btnDesmarcarGlobal) {
        btnDesmarcarGlobal.addEventListener('click', function () {
            checkboxes.forEach(function (chk) {
                chk.checked = false;
            });
            actualizarContador();
        });
    }
});
</script>
