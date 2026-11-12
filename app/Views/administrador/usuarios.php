<?php
/**
 * Vista de Gestión de Personal y Usuarios (Administrador / Supervisor)
 * Archivo: app/Views/administrador/usuarios.php
 * 
 * Orquestador modular que centraliza la administración de cuentas de usuario,
 * asignación de roles y especialidades técnicas.
 * Sigue la arquitectura de componentes parciales en app/Views/administrador/partials/.
 * 
 * @var array $usuarios Lista de usuarios registrados (paginados).
 * @var array $roles Lista de roles disponibles para el select.
 * @var array $especialidades Lista de especialidades para operadores.
 * @var string|null $mensaje Mensaje de éxito flash.
 * @var string|null $error Mensaje de error flash.
 * @var int $totalUsuarios Total de usuarios registrados que coinciden con el filtro.
 * @var int $activos Total de usuarios activos.
 * @var int $inactivos Total de usuarios inactivos.
 * @var int $paginaActual Página actual de la tabla.
 * @var int $totalPaginas Total de páginas disponibles.
 * @var string|null $buscar Término de búsqueda de usuario.
 * @var int|null $idRol ID del rol filtrado.
 * @var int|null $idEspecialidad ID de la especialidad filtrada.
 * @var int|null $estadoFiltro Estado filtrado (1 o 0).
 */

$sesionUserId   = isset($_SESSION['usuario']['id_usuario']) ? (int)$_SESSION['usuario']['id_usuario'] : 0;
$usuarios       = isset($usuarios) ? $usuarios : [];
$roles          = isset($roles) ? $roles : [];
$especialidades = isset($especialidades) ? $especialidades : [];
$totalUsuarios  = isset($totalUsuarios) ? (int)$totalUsuarios : 0;
$activos        = isset($activos) ? (int)$activos : 0;
$inactivos      = isset($inactivos) ? (int)$inactivos : 0;
$paginaActual   = isset($paginaActual) ? (int)$paginaActual : 1;
$totalPaginas   = isset($totalPaginas) ? (int)$totalPaginas : 1;

$buscar         = isset($buscar) ? trim($buscar) : '';
$idRol          = isset($idRol) && $idRol !== '' ? (int)$idRol : null;
$idEspecialidad = isset($idEspecialidad) && $idEspecialidad !== '' ? (int)$idEspecialidad : null;
$estadoFiltro   = isset($estadoFiltro) && $estadoFiltro !== '' ? (int)$estadoFiltro : null;

// Comprobar si hay algún filtro activo
$hayFiltrosActivos = (!empty($buscar) || $idRol !== null || $idEspecialidad !== null || $estadoFiltro !== null);

// Construir query string para paginación persistente
$queryParams = [];
if (!empty($buscar)) $queryParams['buscar'] = $buscar;
if ($idRol !== null) $queryParams['id_rol'] = $idRol;
if ($idEspecialidad !== null) $queryParams['id_especialidad'] = $idEspecialidad;
if ($estadoFiltro !== null) $queryParams['estado'] = $estadoFiltro;
$queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Gestión de Usuarios</h1>
            <p class="text-secondary small mb-0">Administración de cuentas de acceso, asignación de roles y especialidades técnicas.</p>
        </div>
        <div>
            <?php if (hasPermission('usuarios.crear')): ?>
                <button type="button" class="btn btn-primary btn-sm py-1.5 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                    Nuevo Usuario
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-secondary btn-sm py-1.5 px-3 shadow-sm disabled" disabled title="No posee permiso para registrar usuarios">
                    Nuevo Usuario
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mensajes Flash de Retroalimentación -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- 1. Tarjeta de Filtros Avanzados -->
    <?php require __DIR__ . '/partials/usuarios/filtros.php'; ?>

    <!-- 2. Tarjeta Principal con Lista de Usuarios -->
    <div class="card shadow-sm border rounded-3 mb-4" id="tabla-usuarios" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark fs-6">Personal Registrado</h5>
            </div>
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5">
                Total: <?= $totalUsuarios ?> | Activos: <?= $activos ?> | Inactivos: <?= $inactivos ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($usuarios)): ?>
                <div class="text-center py-5 text-secondary">
                    <?php if ($hayFiltrosActivos): ?>
                        <p class="mb-1 fw-semibold text-dark">No se encontraron usuarios con los filtros seleccionados</p>
                        <p class="small text-muted mb-3">Intente ajustando el rol, la especialidad o restableciendo los filtros.</p>
                        <a href="/administrador/usuarios" class="btn btn-sm btn-outline-secondary px-3">Ver todos los usuarios</a>
                    <?php else: ?>
                        <p class="mb-1 fw-semibold text-dark">No hay usuarios registrados en el sistema</p>
                        <p class="small text-muted mb-3">Comience registrando un nuevo usuario para otorgarle acceso.</p>
                        <?php if (hasPermission('usuarios.crear')): ?>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                Crear Primer Usuario
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Vista Desktop: Tabla de Alta Densidad -->
                <?php require __DIR__ . '/partials/usuarios/tabla_desktop.php'; ?>

                <!-- Vista Móvil: Fichas Adaptadas -->
                <?php require __DIR__ . '/partials/usuarios/tarjetas_mobile.php'; ?>
            <?php endif; ?>
        </div>

        <!-- 3. Paginación Numérica -->
        <?php require __DIR__ . '/partials/usuarios/paginacion.php'; ?>
    </div>
</div>

<!-- 4. Modales de Gestión de Usuarios -->
<?php require __DIR__ . '/partials/usuarios/modal_crear.php'; ?>
<?php require __DIR__ . '/partials/usuarios/modal_editar.php'; ?>

<!-- 5. Scripts de Dinamismo y Modales -->
<?php require __DIR__ . '/partials/usuarios/scripts.php'; ?>
