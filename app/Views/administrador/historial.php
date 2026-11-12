<?php
/**
 * Vista: Historial de Trabajos Concluidos (Administrador / Supervisor)
 * Archivo: app/Views/administrador/historial.php
 * 
 * Orquestador modular para la auditoría global de órdenes de reconexión y reclamos finalizados.
 * Sigue la arquitectura de componentes parciales en app/Views/administrador/partials/.
 * 
 * @var array $trabajos
 * @var int $totalTrabajos
 * @var int $p
 * @var int $totalPaginas
 * @var string $buscar
 * @var string $fechaDesde
 * @var string $fechaHasta
 * @var string $periodo
 * @var array $errores
 */

$trabajos      = isset($trabajos) ? $trabajos : [];
$totalTrabajos = isset($totalTrabajos) ? (int)$totalTrabajos : 0;
$p             = isset($p) ? (int)$p : 1;
$totalPaginas  = isset($totalPaginas) ? (int)$totalPaginas : 1;
$buscar        = isset($buscar) ? trim($buscar) : '';
$fechaDesde    = isset($fechaDesde) ? trim($fechaDesde) : '';
$fechaHasta    = isset($fechaHasta) ? trim($fechaHasta) : '';
$periodo       = isset($periodo) ? trim($periodo) : '';
$errores       = isset($errores) ? $errores : [];

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

// Comprobar si hay algún filtro activo
$hayFiltrosActivos = (!empty($buscar) || !empty($fechaDesde) || !empty($fechaHasta) || !empty($periodo));

// Construir query string para paginación
$queryParams = [];
if (!empty($buscar)) $queryParams['buscar'] = $buscar;
if (!empty($fechaDesde)) $queryParams['fecha_desde'] = $fechaDesde;
if (!empty($fechaHasta)) $queryParams['fecha_hasta'] = $fechaHasta;
if (!empty($periodo)) $queryParams['periodo'] = $periodo;
$queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Historial de Trabajos Concluidos</h1>
            <p class="text-secondary small mb-0">Registro y auditoría global de todas las órdenes de reconexión y reclamos técnicos finalizados.</p>
        </div>
        <div>
            <a href="/administrador/historial" class="btn btn-outline-secondary btn-sm py-1 px-2.5 shadow-sm" style="font-size: 0.78rem; font-weight: 500;">
                Actualizar datos
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($sesionError): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($sesionError, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <?php foreach ($errores as $err): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-3 shadow-sm border" role="alert">
                <div class="small text-dark"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- 1. Tarjeta de Filtros por Período y Rango de Fechas -->
    <?php require __DIR__ . '/partials/historial/filtros.php'; ?>

    <!-- 2. Tarjeta Principal con Lista de Historial -->
    <div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark fs-6">Registro de Órdenes Finalizadas</h5>
                <p class="text-muted small mb-0">Resultados ordenados cronológicamente por fecha de conclusión.</p>
            </div>
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5">
                Mostrando <?= count($trabajos) ?> de <?= $totalTrabajos ?> <?= ($totalTrabajos === 1) ? 'registro' : 'registros' ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($trabajos)): ?>
                <div class="text-center py-5 text-secondary">
                    <?php if ($hayFiltrosActivos): ?>
                        <p class="mb-1 fw-semibold text-dark">No se encontraron trabajos concluidos con los filtros seleccionados</p>
                        <p class="small text-muted mb-3">Pruebe ajustando el rango de fechas o limpiando el término de búsqueda.</p>
                        <a href="/administrador/historial" class="btn btn-sm btn-outline-secondary px-3">Restablecer todos los filtros</a>
                    <?php else: ?>
                        <p class="mb-1 fw-semibold text-dark">Aún no se registran trabajos concluidos</p>
                        <p class="small text-muted mb-0">Las órdenes finalizadas por los operadores se listarán aquí en tiempo real.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Vista Desktop: Tabla de Alta Densidad -->
                <?php require __DIR__ . '/partials/historial/tabla_desktop.php'; ?>

                <!-- Vista Móvil: Fichas Adaptadas -->
                <?php require __DIR__ . '/partials/historial/tarjetas_mobile.php'; ?>
            <?php endif; ?>
        </div>

        <!-- 3. Paginación Numérica -->
        <?php require __DIR__ . '/partials/historial/paginacion.php'; ?>
    </div>
</div>
