<?php
/**
 * Componente: Gráficos de Canales Digitales y Balance Operativo en Terreno
 * Archivo: app/Views/dashboard/partials/graficos_canales_terreno.php
 * 
 * - Gráfico A: Evolución y Tráfico por Canal Digital (WhatsApp vs App Móvil).
 * - Gráfico B: Balance Operativo en Terreno (Concluidos vs Pendientes vs Contingencias).
 * 
 * @var array $diasSemana
 * @var int $totalConcluidos
 * @var int $totalPendientes
 * @var int $totalNoConcluidos
 * @var int $recConCount
 * @var int $reclConCount
 * @var int $recPendCount
 * @var int $reclPendCount
 */
?>
<div class="row g-2 g-md-3 mb-4">
    <!-- Gráfico A: Evolución Diaria de Consultas por Canal -->
    <div class="col-12 col-lg-7 col-xl-8">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2" style="border-color: #e2e8f0;">
                <div>
                    <h2 class="h6 fw-bold text-dark mb-0">
                        Evolución y Tráfico por Canal Digital
                    </h2>
                    <small class="text-muted">Comparativa diaria de interacciones en Chatbot WhatsApp frente a la App Móvil.</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2.5 py-1.5 small fw-normal">
                        <span class="d-inline-block rounded-circle me-1" style="width:8px; height:8px; background:#10b981;"></span>WhatsApp
                    </span>
                    <span class="badge bg-light text-dark border px-2.5 py-1.5 small fw-normal">
                        <span class="d-inline-block rounded-circle me-1" style="width:8px; height:8px; background:#0284c7;"></span>App Móvil
                    </span>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="chartEvolucionCanales"></canvas>
                </div>
            </div>
            <div class="card-footer bg-light border-top py-2.5 px-3 px-sm-4 d-flex flex-wrap justify-content-between align-items-center small text-secondary" style="border-color: #e2e8f0;">
                <span>Total del periodo filtrado: <strong><?= number_format(array_sum(array_column($diasSemana, 'total')), 0, '', '.') ?></strong> eventos registrados</span>
                <a href="/reportes/chatbot" class="text-decoration-none text-primary fw-medium">Ver auditoría completa &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Gráfico B: Balance Operativo en Terreno (Doughnut) -->
    <div class="col-12 col-lg-5 col-xl-4">
        <div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
            <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4" style="border-color: #e2e8f0;">
                <h2 class="h6 fw-bold text-dark mb-0">
                    Balance Operativo en Terreno
                </h2>
                <small class="text-muted">Relación entre trabajos concluidos y órdenes pendientes.</small>
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-center">
                <div style="position: relative; height: 210px; width: 100%;">
                    <canvas id="chartBalanceOperativo"></canvas>
                </div>
                <!-- Desglose estructurado -->
                <div class="mt-3 pt-3 border-top" style="border-color: #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                        <span class="d-flex align-items-center gap-1.5 text-secondary">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #10b981;"></span>
                            Concluidos (<?= $totalConcluidos ?>)
                        </span>
                        <span class="fw-bold text-dark font-monospace"><?= $recConCount ?> rec. / <?= $reclConCount ?> recl.</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                        <span class="d-flex align-items-center gap-1.5 text-secondary">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #f59e0b;"></span>
                            Pendientes (<?= $totalPendientes ?>)
                        </span>
                        <span class="fw-bold text-dark font-monospace"><?= $recPendCount ?> rec. / <?= $reclPendCount ?> recl.</span>
                    </div>
                    <?php if ($totalNoConcluidos > 0): ?>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span class="d-flex align-items-center gap-1.5 text-danger">
                            <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #ef4444;"></span>
                            Contingencias (No Concluidos)
                        </span>
                        <span class="fw-bold text-danger font-monospace"><?= $totalNoConcluidos ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
