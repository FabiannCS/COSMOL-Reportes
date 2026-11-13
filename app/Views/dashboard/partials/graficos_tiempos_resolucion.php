<?php
/**
 * Componente: Gráficos de Tiempo Promedio de Atención y Resolución
 * Archivo: app/Views/dashboard/partials/graficos_tiempos_resolucion.php
 * 
 * - Gráfico E: Tiempo Promedio de Resolución por Servicio (Reconexión y Reclamo Independiente).
 * - Gráfico F: Tiempo Promedio de Atención por Técnico / Operador.
 * 
 * @var array $metricasTiempos
 */

$metricasTiempos = isset($metricasTiempos) ? $metricasTiempos : [];
?>
<div class="row g-2 g-md-3 mb-4">
    <!-- Gráfico E: Promedio de Resolución por Servicio (Reconexión y Reclamo Independiente) -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2" style="border-color: #e2e8f0;">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0">
                        Tiempo Promedio por Servicio
                    </h2>
                    <small class="text-muted">Tiempo promedio desde que el socio genera la solicitud hasta su solución efectiva en terreno.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2.5 py-1.5 small fw-normal font-monospace">
                        Global: <strong><?= isset($metricasTiempos['promedioGlobalTexto']) ? $metricasTiempos['promedioGlobalTexto'] : '0h' ?></strong>
                    </span>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartTiemposServicio"></canvas>
                </div>

                <!-- Resumen de Promedios Independientes -->
                <div class="row g-2 mt-3 pt-3 border-top" style="border-color: #f1f5f9;">
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center" style="border-color: #f1f5f9;">
                            <span class="text-secondary small d-block" style="font-size: 0.72rem;">Reconexión</span>
                            <strong class="text-dark font-monospace d-block" style="font-size: 0.88rem;"><?= isset($metricasTiempos['promedioRecTexto']) ? $metricasTiempos['promedioRecTexto'] : '0h' ?></strong>
                            <span class="text-muted small" style="font-size: 0.68rem;"><?= isset($metricasTiempos['totalCasosRec']) ? $metricasTiempos['totalCasosRec'] : 0 ?> casos</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center" style="border-color: #f1f5f9;">
                            <span class="text-secondary small d-block" style="font-size: 0.72rem;">Agua Potable</span>
                            <strong class="text-dark font-monospace d-block" style="font-size: 0.88rem;"><?= isset($metricasTiempos['promedioReclAguaTexto']) ? $metricasTiempos['promedioReclAguaTexto'] : '0h' ?></strong>
                            <span class="text-muted small" style="font-size: 0.68rem;"><?= isset($metricasTiempos['totalCasosReclAgua']) ? $metricasTiempos['totalCasosReclAgua'] : 0 ?> casos</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 rounded bg-light border text-center" style="border-color: #f1f5f9;">
                            <span class="text-secondary small d-block" style="font-size: 0.72rem;">Alcantarillado</span>
                            <strong class="text-dark font-monospace d-block" style="font-size: 0.88rem;"><?= isset($metricasTiempos['promedioReclAlcTexto']) ? $metricasTiempos['promedioReclAlcTexto'] : '0h' ?></strong>
                            <span class="text-muted small" style="font-size: 0.68rem;"><?= isset($metricasTiempos['totalCasosReclAlc']) ? $metricasTiempos['totalCasosReclAlc'] : 0 ?> casos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico F: Tiempo Promedio de Atención por Técnico -->
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-color: #e2e8f0;">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0">
                        Tiempo Promedio por Técnico
                    </h2>
                    <small class="text-muted">Horas promedio que tardan los técnicos en concluir sus trabajos.</small>
                </div>
                <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                    Técnicos: <strong><?= !empty($metricasTiempos['tecnicos']) ? count($metricasTiempos['tecnicos']) : 0 ?></strong>
                </span>
            </div>
            <div class="card-body p-3 p-md-4">
                <?php if (empty($metricasTiempos['tecnicos'])): ?>
                    <div class="d-flex flex-column align-items-center justify-content-center text-secondary py-5" style="height: 260px;">
                        <p class="small mb-0">No hay registros de trabajos concluidos en el periodo seleccionado.</p>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="chartTiemposTecnicos"></canvas>
                    </div>
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center small text-secondary" style="border-color: #f1f5f9;">
                        <span>Evaluados: <strong><?= isset($metricasTiempos['totalCasosGlobal']) ? $metricasTiempos['totalCasosGlobal'] : 0 ?></strong> trabajos concluidos</span>
                        <span class="text-muted font-monospace" style="font-size: 0.75rem;">Valores en horas reloj</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
