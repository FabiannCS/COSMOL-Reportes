<?php
/**
 * Componente: Barra de Filtros Temporales del Dashboard
 * Archivo: app/Views/dashboard/partials/filtros_dashboard.php
 * 
 * Barra sutil y compacta para filtrado por periodos predefinidos o rango de fechas.
 * 
 * @var string $periodoActivo
 * @var string $textoPeriodo
 * @var string $fechaInicioFiltro
 * @var string $fechaFinFiltro
 * @var bool $hayFiltroActivo
 */
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 px-3 mb-3 bg-white border rounded-3" style="border-color: #e2e8f0;">
    <!-- Selector rápido de periodos (Segmented control) -->
    <div class="d-flex align-items-center flex-wrap gap-1">
        <span class="text-secondary small fw-semibold text-uppercase me-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Periodo:</span>
        <div class="d-inline-flex p-0.5 bg-light rounded-2 border" style="border-color: #e2e8f0;">
            <a href="/dashboard?periodo=todos" class="btn btn-sm py-0.5 px-2 rounded-1 <?= ($periodoActivo === 'todos') ? 'bg-white text-dark shadow-sm fw-semibold border' : 'text-secondary border-0' ?>" style="font-size: 0.75rem; border-color: #cbd5e1 !important;">
                Todos
            </a>
            <a href="/dashboard?periodo=hoy" class="btn btn-sm py-0.5 px-2 rounded-1 <?= ($periodoActivo === 'hoy') ? 'bg-white text-dark shadow-sm fw-semibold border' : 'text-secondary border-0' ?>" style="font-size: 0.75rem; border-color: #cbd5e1 !important;">
                Hoy
            </a>
            <a href="/dashboard?periodo=7dias" class="btn btn-sm py-0.5 px-2 rounded-1 <?= ($periodoActivo === '7dias') ? 'bg-white text-dark shadow-sm fw-semibold border' : 'text-secondary border-0' ?>" style="font-size: 0.75rem; border-color: #cbd5e1 !important;">
                7 días
            </a>
            <a href="/dashboard?periodo=30dias" class="btn btn-sm py-0.5 px-2 rounded-1 <?= ($periodoActivo === '30dias') ? 'bg-white text-dark shadow-sm fw-semibold border' : 'text-secondary border-0' ?>" style="font-size: 0.75rem; border-color: #cbd5e1 !important;">
                30 días
            </a>
            <a href="/dashboard?periodo=mes" class="btn btn-sm py-0.5 px-2 rounded-1 <?= ($periodoActivo === 'mes') ? 'bg-white text-dark shadow-sm fw-semibold border' : 'text-secondary border-0' ?>" style="font-size: 0.75rem; border-color: #cbd5e1 !important;">
                Este mes
            </a>
        </div>
        <span class="badge bg-light text-secondary border font-monospace ms-1 small fw-normal d-none d-lg-inline" style="font-size: 0.72rem;">
            <?= htmlspecialchars($textoPeriodo, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </div>

    <!-- Rango de Fechas Compacto -->
    <form action="/dashboard" method="GET" class="d-flex align-items-center flex-wrap gap-1.5 ms-auto m-0">
        <input type="hidden" name="periodo" value="personalizado">
        <span class="text-secondary small fw-semibold text-uppercase d-none d-sm-inline" style="font-size: 0.68rem; letter-spacing: 0.05em;">Rango:</span>
        <input type="date" 
               class="form-control form-control-sm py-0 px-2 text-secondary" 
               id="fecha_inicio" 
               name="fecha_inicio" 
               value="<?= htmlspecialchars($fechaInicioFiltro, ENT_QUOTES, 'UTF-8') ?>"
               style="width: 122px; height: 28px; font-size: 0.75rem;" 
               title="Fecha de inicio">
        <span class="text-muted small">&ndash;</span>
        <input type="date" 
               class="form-control form-control-sm py-0 px-2 text-secondary" 
               id="fecha_fin" 
               name="fecha_fin" 
               value="<?= htmlspecialchars($fechaFinFiltro, ENT_QUOTES, 'UTF-8') ?>"
               style="width: 122px; height: 28px; font-size: 0.75rem;" 
               title="Fecha de fin">
        <button type="submit" class="btn btn-sm btn-primary py-0 px-2.5 d-flex align-items-center" style="height: 28px; font-size: 0.75rem;" title="Aplicar rango de fechas">
            Filtrar
        </button>
        <?php if ($hayFiltroActivo): ?>
            <a href="/dashboard" class="btn btn-sm btn-outline-secondary py-0 px-2 d-flex align-items-center" style="height: 28px; font-size: 0.75rem;" title="Restablecer filtros">
                Limpiar
            </a>
        <?php endif; ?>
    </form>
</div>
