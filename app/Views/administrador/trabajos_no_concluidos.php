<?php
/**
 * Vista: Trabajos No Concluidos / En Revisión (Administrador / Supervisor)
 * Archivo: app/Views/administrador/trabajos_no_concluidos.php
 * 
 * Orquestador modular para la auditoría y gestión de contingencias reportadas en terreno.
 * Sigue la arquitectura de componentes parciales en app/Views/administrador/partials/.
 * 
 * @var array $trabajos
 * @var int $totalTrabajos
 * @var int $p
 * @var int $totalPaginas
 * @var string $buscar
 * @var array $errores
 */

$trabajos      = isset($trabajos) ? $trabajos : [];
$totalTrabajos = isset($totalTrabajos) ? (int)$totalTrabajos : 0;
$p             = isset($p) ? (int)$p : 1;
$totalPaginas  = isset($totalPaginas) ? (int)$totalPaginas : 1;
$buscar        = isset($buscar) ? trim($buscar) : '';
$errores       = isset($errores) ? $errores : [];

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

// Conteo por servicio para la barra de filtros segmentada
$cntReconexiones = 0;
$cntAguaPotable  = 0;
$cntAlcantarilla = 0;

foreach ($trabajos as $t) {
    $tipo = isset($t['tipo_trabajo']) ? $t['tipo_trabajo'] : '';
    $idTipoRecl = isset($t['id_tipo_reclamo']) ? (int)$t['id_tipo_reclamo'] : 0;

    if ($tipo === 'reconexion') {
        $cntReconexiones++;
    } elseif ($idTipoRecl === 2) {
        $cntAguaPotable++;
    } elseif ($idTipoRecl === 3) {
        $cntAlcantarilla++;
    }
}
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Trabajos No Concluidos / En Revisión</h1>
            <p class="text-secondary small mb-0">Trabajos reportados por operadores que requieren intervención supervisada.</p>
        </div>
        <div>
            <a href="/administrador/trabajos-no-concluidos" class="btn btn-outline-secondary btn-sm py-1 px-2.5 shadow-sm" style="font-size: 0.78rem; font-weight: 500;">
                Actualizar lista
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

    <!-- 1. Barra de Control: Segmentos COSMOL y Buscador -->
    <?php require __DIR__ . '/partials/trabajos_no_concluidos/filtros.php'; ?>

    <!-- 2. Tarjeta Principal con Lista de Trabajos -->
    <div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold mb-0 text-dark fs-6">Trabajos en Revisión</h5>
            </div>
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5">
                Mostrando <?= count($trabajos) ?> de <?= $totalTrabajos ?> <?= ($totalTrabajos === 1) ? 'caso' : 'casos' ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($trabajos)): ?>
                <div class="text-center py-5 text-secondary">
                    <?php if (!empty($buscar)): ?>
                        <p class="mb-1 fw-semibold text-dark">No se encontraron resultados para "<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>"</p>
                        <p class="small text-muted mb-3">Intente buscando con otro término, código de socio o limpie el filtro.</p>
                        <a href="/administrador/trabajos-no-concluidos" class="btn btn-sm btn-outline-secondary px-3">Ver todos los trabajos</a>
                    <?php else: ?>
                        <p class="mb-1 fw-semibold text-dark">No hay trabajos en revisión o no concluidos</p>
                        <p class="small text-muted mb-0">Todas las órdenes operativas se encuentran al día o concluidas.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Vista Desktop: Tabla de Alta Densidad -->
                <?php require __DIR__ . '/partials/trabajos_no_concluidos/tabla_desktop.php'; ?>

                <!-- Vista Móvil: Fichas Adaptadas -->
                <?php require __DIR__ . '/partials/trabajos_no_concluidos/tarjetas_mobile.php'; ?>
            <?php endif; ?>
        </div>

        <!-- 3. Paginación Numérica -->
        <?php require __DIR__ . '/partials/trabajos_no_concluidos/paginacion.php'; ?>
    </div>
</div>

<!-- 4. Scripts de Filtrado Interactivo -->
<?php require __DIR__ . '/partials/trabajos_no_concluidos/scripts.php'; ?>
