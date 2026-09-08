<?php
/**
 * Vista: Dashboard Principal
 * @var int $totalConsultas
 * @var int $totalPendientes
 * @var int $recPendCount
 * @var int $reclPendCount
 * @var int $totalConcluidos
 * @var int $recConCount
 * @var int $reclConCount
 * @var int $totalOperadores
 * @var array $operadoresPorEspecialidad
 * @var array $consultas7Dias
 * @var array|null $usuario
 */

// Datos para Gráfico 1
$labelsDias = json_encode(array_keys($consultas7Dias ?? []));
$datosDias  = json_encode(array_values($consultas7Dias ?? []));

$rol = isset($usuario['nombre_rol']) ? $usuario['nombre_rol'] : 'Usuario';
$nombre = isset($usuario['username']) ? $usuario['username'] : 'Invitado';
$recPend = isset($recPendCount) ? (int)$recPendCount : 0;
$reclPend = isset($reclPendCount) ? (int)$reclPendCount : 0;
$recCon = isset($recConCount) ? (int)$recConCount : 0;
$reclCon = isset($reclConCount) ? (int)$reclConCount : 0;
$especialidades = isset($operadoresPorEspecialidad) && is_array($operadoresPorEspecialidad) ? $operadoresPorEspecialidad : [];
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1 text-dark">Panel Principal</h1>
            <p class="text-muted small mb-0">Resumen en tiempo real del estado de trabajos y consultas del chatbot de COSMOL.</p>
        </div>
    </div>

    <!-- Tarjetas de Resumen KPI Informativas -->
    <div class="row g-3 g-xl-4 mb-3">
        <?php
            $statTitle = 'Consultas Chatbot';
            $statValue = isset($totalConsultas) ? (int)$totalConsultas : 0;
            $statColor = 'primary';
            $statIcon  = 'bi-chat-dots-fill';
            $statExtra = '';
            $statValueColor = 'text-dark';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Trabajos Pendientes';
            $statValue = isset($totalPendientes) ? (int)$totalPendientes : 0;
            $statColor = 'warning';
            $statIcon  = 'bi-hourglass-split';
            $statExtra = '<small class="text-muted d-block text-truncate"><span class="fw-semibold">' . $recPend . '</span> rec. - <span class="fw-semibold">' . $reclPend . '</span> recl.</small>';
            $statValueColor = 'text-dark';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Trabajos Concluidos';
            $statValue = isset($totalConcluidos) ? (int)$totalConcluidos : 0;
            $statColor = 'success';
            $statIcon  = 'bi-check-circle-fill';
            $statExtra = '<small class="text-muted d-block text-truncate"><span class="fw-semibold">' . $recCon . '</span> rec. - <span class="fw-semibold">' . $reclCon . '</span> recl.</small>';
            $statValueColor = 'text-dark';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
        <?php
            $statTitle = 'Total Operadores';
            $statValue = isset($totalOperadores) ? (int)$totalOperadores : 0;
            $statColor = 'info';
            $statIcon  = 'bi-people-fill';
            $statExtra = '';
            $statValueColor = 'text-dark';
            include __DIR__ . '/../layouts/partials/stat_card.php';
        ?>
    </div>

    <!-- Gráficos Principales -->
    <div class="row g-3 g-xl-4 mb-4">
        <!-- Gráfico 1: Evolución de Consultas -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-graph-up text-primary me-2"></i>Evolución de Consultas (Últimos 7 días)</h5>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="chartConsultas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico 2: Estado de Trabajos -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Estado de Trabajos</h5>
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center" style="position: relative; min-height: 250px;">
                        <canvas id="chartEstadoTrabajos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico 3: Comparativa Reconexiones vs Reclamos -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4"><i class="bi bi-bar-chart-fill text-info me-2"></i>Comparativa: Reconexiones vs Reclamos</h5>
                    <div style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartComparativa"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución de Operadores por Especialidad -->
    <?php if (!empty($especialidades)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark h5">Operadores por Especialidad:</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($especialidades as $esp): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small fw-normal">
                            <i class="bi bi-person me-1 text-primary"></i>
                            <?= htmlspecialchars(isset($esp['especialidad']) ? $esp['especialidad'] : '', ENT_QUOTES, 'UTF-8') ?>:
                            <strong class="ms-1 text-primary"><?= isset($esp['total']) ? (int)$esp['total'] : 0 ?></strong>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Accesos Directos Dinámicos por Rol (Tamaño Compacto) -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="text-muted text-uppercase fw-bold small mb-0">
            Accesos Directos
        </h6>
    </div>
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <a href="/administrador/trabajos" class="btn btn-outline-warning w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-card-checklist me-2 text-warning fs-5 align-middle"></i>Ver Trabajos Pendientes</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
        <?php if ($rol === 'Administrador'): ?>
        <div class="col-12 col-md-4">
            <a href="/administrador/usuarios" class="btn btn-outline-info w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-people me-2 text-info fs-5 align-middle"></i>Gestionar Usuarios</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
        <?php endif; ?>
        <div class="col-12 col-md-4">
            <a href="/reportes/visualizar" class="btn btn-outline-primary w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary fs-5 align-middle"></i>Ver Reportes</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Local -->
<script src="/assets/vendor/chartjs/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Colores modernos
    const colPrimary = '#0d6efd';
    const colSuccess = '#198754';
    const colWarning = '#ffc107';
    const colDanger  = '#dc3545';
    
    // 1. Gráfico de Líneas: Evolución de Consultas
    const ctxConsultas = document.getElementById('chartConsultas').getContext('2d');
    new Chart(ctxConsultas, {
        type: 'line',
        data: {
            labels: <?= $labelsDias ?>,
            datasets: [{
                label: 'Consultas Registradas',
                data: <?= $datosDias ?>,
                borderColor: colPrimary,
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 3,
                pointBackgroundColor: colPrimary,
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: colPrimary,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 14, weight: 'bold' },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { borderDash: [4, 4], color: '#e9ecef' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Gráfico de Anillo: Estado de Trabajos
    const ctxEstado = document.getElementById('chartEstadoTrabajos').getContext('2d');
    const pendientes = <?= $totalPendientes ?>;
    const concluidos = <?= $totalConcluidos ?>;
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: ['Pendientes', 'Concluidos'],
            datasets: [{
                data: [pendientes, concluidos],
                backgroundColor: [colWarning, colSuccess],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
                }
            }
        }
    });

    // 3. Gráfico de Barras Agrupadas: Reconexiones vs Reclamos
    const ctxComparativa = document.getElementById('chartComparativa').getContext('2d');
    new Chart(ctxComparativa, {
        type: 'bar',
        data: {
            labels: ['Reconexiones', 'Reclamos'],
            datasets: [
                {
                    label: 'Pendientes',
                    data: [<?= $recPend ?>, <?= $reclPend ?>],
                    backgroundColor: colWarning,
                    borderRadius: 4
                },
                {
                    label: 'Concluidos',
                    data: [<?= $recCon ?>, <?= $reclCon ?>],
                    backgroundColor: colSuccess,
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, pointStyle: 'circle' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { borderDash: [4, 4], color: '#e9ecef' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
