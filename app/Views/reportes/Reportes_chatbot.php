<?php
/**
 * Vista Orquestadora: Reportes y Auditoría de Consultas del Chatbot de WhatsApp
 * Archivo: app/Views/reportes/Reportes_chatbot.php
 * 
 * Segmentada en componentes modulares en app/Views/reportes/partials/:
 *  - tarjetas_kpi.php    -> Tarjetas métricas y auditoría interactiva (incluye Pagos QR)
 *  - filtros.php         -> Formulario de filtrado multicriterio
 *  - tabla_socios.php    -> Modo agrupado por socio (escritorio + móvil)
 *  - tabla_acciones.php  -> Modo detallado cronológico (escritorio + móvil)
 *  - paginacion.php      -> Paginador y resumen de registros
 * Estilos centralizados en: public/assets/css/reportes.css
 *
 * @var string $vista            Modo de vista activo: 'socios' o 'acciones'
 * @var array  $socios           Lista de socios agrupados
 * @var array  $accionesPorSocio Lista de acciones indexada por socio_key
 * @var array  $consultas        Lista de consultas detalladas
 * @var array  $tiposConsulta    Catálogo de tipos de consulta
 * @var array  $totalesPorTipo   Conteo de consultas agrupadas por tipo
 * @var array  $filtros          Filtros aplicados (fecha_inicio, fecha_fin, id_tipo, buscar)
 * @var int    $pagina           Página actual
 * @var int    $totalPaginas     Total de páginas
 * @var int    $totalRegistros   Total de registros de la vista activa
 * @var int    $totalSocios      Total de usuarios únicos
 * @var int    $totalConsultas   Total de consultas registradas
 * @var int    $limit            Límite de registros por página
 */

$totalesPorTipo = isset($totalesPorTipo) ? $totalesPorTipo : [];
$vista          = isset($vista) ? $vista : 'socios';

// Asignación de estilos semánticos por tipo de evento del Chatbot
$getTipoBadgeClass = function ($idTipo) {
    switch ((int)$idTipo) {
        case 1:  return 'bg-primary-subtle text-primary border-primary-subtle';
        case 2:  return 'bg-warning-subtle text-dark border-warning-subtle';
        case 3:  return 'bg-info-subtle text-info-emphasis border-info-subtle';
        case 4:  return 'bg-danger-subtle text-danger border-danger-subtle';
        case 5:  return 'bg-success-subtle text-success border-success-subtle';
        case 6:  return 'bg-secondary-subtle text-secondary border-secondary-subtle';
        case 7:  return 'bg-primary-subtle text-primary border-primary-subtle';
        case 8:  return 'bg-info-subtle text-primary border-info-subtle';
        case 12: return 'bg-info-subtle text-primary border-info-subtle'; // Pago Código QR
        default: return 'bg-light text-secondary border-secondary-subtle';
    }
};

$getTipoBorderColor = function ($idTipo) {
    switch ((int)$idTipo) {
        case 1:  return '#0284c7'; // Azul COSMOL (Autenticación)
        case 2:  return '#f59e0b'; // Ámbar (Deuda)
        case 3:  return '#0ea5e9'; // Cian (Consumo)
        case 4:  return '#ef4444'; // Rojo (Reclamo)
        case 5:  return '#10b981'; // Esmeralda (Reconexión)
        case 6:  return '#64748b'; // Pizarra (Oficinas)
        case 7:  return '#6366f1'; // Índigo (Agente)
        case 8:  return '#8b5cf6'; // Violeta (Estado)
        case 12: return '#7c3aed'; // Violeta Fintech (Auditoría Pago QR)
        default: return '#64748b';
    }
};

// Construcción de parámetros de consulta GET
$queryParams = [];
$queryParams['vista'] = $vista;
if (!empty($filtros['fecha_inicio'])) $queryParams['fecha_inicio'] = $filtros['fecha_inicio'];
if (!empty($filtros['fecha_fin']))    $queryParams['fecha_fin']    = $filtros['fecha_fin'];
if (!empty($filtros['id_tipo']))      $queryParams['id_tipo']      = $filtros['id_tipo'];
if (!empty($filtros['buscar']))       $queryParams['buscar']       = $filtros['buscar'];

$exportQuery = http_build_query($queryParams);
$exportUrl   = '/reportes/exportar' . ($exportQuery ? '?' . $exportQuery : '');

$pageUrl = function ($pageNum) use ($queryParams) {
    $p = array_merge($queryParams, ['p' => $pageNum]);
    return '/reportes/visualizar?' . http_build_query($p);
};

// URLs para alternar entre modos de vista
$paramsSocios   = array_merge($queryParams, ['vista' => 'socios', 'p' => 1]);
$paramsAcciones = array_merge($queryParams, ['vista' => 'acciones', 'p' => 1]);
$urlVistaSocios   = '/reportes/visualizar?' . http_build_query($paramsSocios);
$urlVistaAcciones = '/reportes/visualizar?' . http_build_query($paramsAcciones);

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);

$hayFiltrosActivos = (!empty($filtros['fecha_inicio']) || !empty($filtros['fecha_fin']) || !empty($filtros['id_tipo']) || !empty($filtros['buscar']));
$esFiltroQrActivo  = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === 12);
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Consultas Chatbot WhatsApp</h1>
        </div>
        <div>
            <?php if (hasPermission('reportes.exportar')): ?>
                <a href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-success btn-sm py-1 px-3 shadow-sm" style="font-size: 0.78rem; font-weight: 500;">
                    Exportar a CSV (<?= $vista === 'socios' ? 'Socios' : 'Acciones' ?>)
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-3 shadow-sm disabled" disabled title="No posee permiso para exportar reportes" style="font-size: 0.78rem; font-weight: 500;">
                    Exportar a CSV
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 1. Tarjetas de Auditoría por Tipo de Consulta (KPIs Clicables) -->
    <?php require __DIR__ . '/partials/tarjetas_kpi.php'; ?>

    <!-- 2. Formulario de Filtros de Búsqueda -->
    <?php require __DIR__ . '/partials/filtros.php'; ?>

    <!-- Selector de Modos de Vista (Segmentos COSMOL) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
        <div class="p-1 rounded-3 border d-inline-flex flex-wrap gap-1 w-100 w-md-auto" style="background-color: #f1f5f9; border-color: #e2e8f0 !important;" role="group" aria-label="Modos de Vista">
            <a href="<?= htmlspecialchars($urlVistaSocios, ENT_QUOTES, 'UTF-8') ?>" 
               class="filtro-vista-btn <?= $vista === 'socios' ? 'active' : '' ?>">
                Por Socios (Usuarios Únicos) <span class="badge rounded-pill ms-1.5 font-monospace filtro-badge"><?= number_format($totalSocios) ?></span>
            </a>
            <a href="<?= htmlspecialchars($urlVistaAcciones, ENT_QUOTES, 'UTF-8') ?>" 
               class="filtro-vista-btn <?= $vista === 'acciones' ? 'active' : '' ?>">
                Registro Detallado de Acciones <span class="badge rounded-pill ms-1.5 font-monospace filtro-badge"><?= number_format($totalConsultas) ?></span>
            </a>
        </div>
        <div class="small text-secondary d-none d-md-block">
            Mostrando <?= $desde ?> - <?= $hasta ?> de <?= number_format($totalRegistros) ?> registros
        </div>
    </div>

    <!-- 3. Contenedor de Registros y Reportes -->
    <div class="card shadow-sm border rounded-3 mb-4" id="tabla-reportes" style="border-color: #e2e8f0;">
        <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
            <div>
                <h5 class="card-title fw-bold text-dark mb-0 fs-6">
                    <?php if ($esFiltroQrActivo): ?>
                        Auditoría: Socios con Pagos por Código QR
                    <?php else: ?>
                        <?= ($vista === 'socios') ? 'Usuarios Únicos del Chatbot WhatsApp' : 'Historial Detallado de Consultas' ?>
                    <?php endif; ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?php if ($esFiltroQrActivo): ?>
                        Verificación específica de socios y transacciones que efectuaron pagos mediante QR a través del Chatbot.
                    <?php else: ?>
                        <?= ($vista === 'socios') ? 'Agrupación individual de socios y su volumen de actividad vía WhatsApp.' : 'Cronología completa de solicitudes y atenciones registradas en tiempo real.' ?>
                    <?php endif; ?>
                </p>
            </div>
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5">
                Total: <?= number_format($totalRegistros) ?>
            </span>
        </div>

        <div class="card-body p-0">
            <!-- Renderizado modular según el modo de vista activo -->
            <?php if ($vista === 'socios'): ?>
                <?php require __DIR__ . '/partials/tabla_socios.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/partials/tabla_acciones.php'; ?>
            <?php endif; ?>

            <!-- 4. Paginación y Resumen -->
            <?php require __DIR__ . '/partials/paginacion.php'; ?>
        </div>
    </div>
</div>
