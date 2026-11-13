<?php
/**
 * Componente: Gráficos de Servicios Más Demandados y Carga vs Cuadrillas
 * Archivo: app/Views/dashboard/partials/graficos_tramites_operadores.php
 * 
 * - Gráfico C: Servicios y Trámites más Demandados por Socios.
 * - Gráfico D: Carga Operativa Pendiente vs Personal Técnico Asignado.
 * 
 * @var int $totalOperadores
 */
?>
<div class="row g-2 g-md-3 mb-4">
    <!-- Gráfico C: Top Trámites y Consultas Frecuentes -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0">
                        Servicios y Trámites más Consultados
                    </h2>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartTopConsultas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico D: Carga Operativa vs Personal Asignado -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0">
                        Carga de Trabajo vs Personal Asignado
                    </h2>
                </div>
                <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                    Personal: <strong><?= $totalOperadores ?></strong>
                </span>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartCargaOperadores"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
