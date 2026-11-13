<?php
/**
 * Componente: Tarjetas Métricas KPI Iniciales
 * Archivo: app/Views/dashboard/partials/tarjetas_kpi.php
 * 
 * Tarjetas de métricas de alto impacto para canales de atención digital
 * y trabajos de cuadrillas en terreno (sin descripciones largas ni icon-soup).
 * 
 * @var int $totalConsultas
 * @var int $totalNumerosUnicos
 * @var int $totalConsultasApp
 * @var int $totalPendientes
 * @var int $recPendCount
 * @var int $reclPendCount
 * @var int $totalConcluidos
 * @var int $recConCount
 * @var int $reclConCount
 * @var float $tasaResolucion
 */
?>
<div class="row g-2 g-md-3 mb-4">
    <!-- KPI 1: Consultas Chatbot WhatsApp -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 bg-white" style="border-top: 3px solid #10b981 !important;">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.05em;">
                            Consultas Chatbot WhatsApp
                        </span>
                    </div>
                    <div class="h2 fw-bold mb-0 text-dark font-monospace">
                        <?= number_format($totalConsultas, 0, '', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Registros en la App Móvil -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 bg-white" style="border-top: 3px solid #0284c7 !important;">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.05em;">
                            Registros en la App Móvil
                        </span>
                    </div>
                    <div class="h2 fw-bold mb-0 text-dark font-monospace">
                        <?= number_format($totalConsultasApp, 0, '', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Trabajos Pendientes (Cortes y Reconexiones) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 bg-white" style="border-top: 3px solid #f59e0b !important;">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.05em;">
                            Trabajos Pendientes
                        </span>
                    </div>
                    <div class="h2 fw-bold mb-0 text-warning-emphasis font-monospace">
                        <?= number_format($totalPendientes, 0, '', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 4: Trabajos Concluidos -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 bg-white" style="border-top: 3px solid #059669 !important;">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.05em;">
                            Trabajos Concluidos
                        </span>
                    </div>
                    <div class="h2 fw-bold mb-0 text-success font-monospace">
                        <?= number_format($totalConcluidos, 0, '', '.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
