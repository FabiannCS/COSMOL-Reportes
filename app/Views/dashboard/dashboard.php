<?php
/**
 * Vista: Dashboard Principal y Métricas Operativas
 * Archivo: app/Views/dashboard/dashboard.php
 * 
 * Orquestador principal del panel de control que centraliza métricas en tiempo real,
 * auditoría de canales digitales (Chatbot WhatsApp y App Móvil) y balance de trabajos técnicos.
 * Implementa una arquitectura modular basada en componentes parciales (app/Views/dashboard/partials/).
 * 
 * @var int $totalConsultas
 * @var int $totalConsultasApp
 * @var int $totalNumerosUnicos
 * @var array $numerosMasActivos
 * @var int $totalPendientes
 * @var int $recPendCount
 * @var int $reclPendCount
 * @var int $reclAlcantarilladoPend
 * @var int $reclAguaPotablePend
 * @var int $totalConcluidos
 * @var int $recConCount
 * @var int $reclConCount
 * @var int $reclAlcantarilladoCon
 * @var int $reclAguaPotableCon
 * @var float $tasaResolucion
 * @var int $totalNoConcluidos
 * @var int $totalOperadores
 * @var array $operadoresPorEspecialidad
 * @var array $mapOpPorEsp
 * @var array $diasSemana
 * @var array $topTiposConsultas
 * @var array $apiStatus
 * @var array $filtros
 * @var array|null $usuario
 */

$rol    = isset($usuario['nombre_rol']) ? $usuario['nombre_rol'] : 'Usuario';
$nombre = isset($usuario['username']) ? $usuario['username'] : 'Invitado';

$periodoActivo     = isset($filtros['periodo']) ? $filtros['periodo'] : 'todos';
$fechaInicioFiltro = isset($filtros['fecha_inicio']) ? $filtros['fecha_inicio'] : '';
$fechaFinFiltro    = isset($filtros['fecha_fin']) ? $filtros['fecha_fin'] : '';
$hayFiltroActivo   = ($periodoActivo !== 'todos' || !empty($fechaInicioFiltro) || !empty($fechaFinFiltro));

// Etiquetas descriptivas del periodo activo
$textoPeriodo = 'Histórico completo';
if ($periodoActivo === 'hoy') {
    $textoPeriodo = 'Hoy (' . date('d/m/Y') . ')';
} elseif ($periodoActivo === '7dias') {
    $textoPeriodo = 'Últimos 7 días (' . date('d/m/Y', strtotime('-6 days')) . ' al ' . date('d/m/Y') . ')';
} elseif ($periodoActivo === '30dias') {
    $textoPeriodo = 'Últimos 30 días';
} elseif ($periodoActivo === 'mes') {
    $textoPeriodo = 'Este mes (' . date('m/Y') . ')';
} elseif ($periodoActivo === 'personalizado' && (!empty($fechaInicioFiltro) || !empty($fechaFinFiltro))) {
    $textoPeriodo = 'Rango: ' . ($fechaInicioFiltro ?: 'Inicio') . ' al ' . ($fechaFinFiltro ?: 'Hoy');
}

// Preparación de datos JSON para Chart.js
$labelsDias = [];
$dataWhatsAppDias = [];
$dataAppMovilDias = [];
foreach ($diasSemana as $dia) {
    $labelsDias[] = $dia['label'];
    $dataWhatsAppDias[] = (int)$dia['whatsapp'];
    $dataAppMovilDias[] = (int)$dia['app_movil'];
}

$labelsTipos = [];
$dataTipos = [];
foreach ($topTiposConsultas as $tipo) {
    $labelsTipos[] = $tipo['nombre'];
    $dataTipos[] = (int)$tipo['total'];
}
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Panel de Control Operativo</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small font-monospace">
                Actualizado: <?= date('H:i') ?>
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm" onclick="window.location.reload();" title="Recargar métricas">
                Actualizar
            </button>
        </div>
    </div>

    <!-- 1. Barra de Filtros Sutil y Compacta -->
    <?php require __DIR__ . '/partials/filtros_dashboard.php'; ?>

    <!-- 2. Tarjetas Métricas KPI Iniciales -->
    <?php require __DIR__ . '/partials/tarjetas_kpi.php'; ?>

    <!-- 3. Sección Principal de Gráficos (Evolución Canales y Balance Terreno) -->
    <?php require __DIR__ . '/partials/graficos_canales_terreno.php'; ?>

    <!-- 4. Sección Secundaria de Gráficos (Demanda Trámites y Cuadrillas) -->
    <?php require __DIR__ . '/partials/graficos_tramites_operadores.php'; ?>

    <!-- 5. Monitoreo de Tráfico Telefónico y Panel de Accesos Operativos -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Tabla: Control de Tráfico WhatsApp -->
        <div class="col-12 col-xl-8">
            <?php require __DIR__ . '/partials/tabla_trafico_socios.php'; ?>
        </div>

        <!-- Panel Lateral: Accesos Directos Operativos -->
        <div class="col-12 col-xl-4">
            <?php require __DIR__ . '/partials/panel_accesos_rapidos.php'; ?>
        </div>
    </div>
</div>

<!-- 6. Inicialización de Gráficos con Chart.js v4 -->
<?php require __DIR__ . '/partials/scripts_charts.php'; ?>
