<?php
/**
 * Componente: Filtros por Período y Rango de Fechas del Historial
 * Archivo: app/Views/administrador/partials/historial_filtros.php
 * 
 * Atajos de período rápido (Hoy, 7 días, 30 días, Mes) y formulario de rango de fechas.
 * 
 * @var string $buscar
 * @var string $periodo
 * @var string $fechaDesde
 * @var string $fechaHasta
 * @var bool $hayFiltrosActivos
 */
?>
<div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
    <div class="card-body p-3">
        <!-- Selector de Atajos Rápidos de Período con Identidad COSMOL -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3 pb-3 border-bottom" style="border-color: #f1f5f9;">
            <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                Período Rápido:
            </div>
            <div class="p-1 rounded-3 border d-inline-flex flex-wrap gap-1" style="background-color: #f1f5f9; border-color: #e2e8f0 !important;">
                <a href="?buscar=<?= urlencode($buscar) ?>" class="filtro-periodo-btn <?= (empty($periodo) && empty($fechaDesde) && empty($fechaHasta)) ? 'active' : '' ?>">
                    Todo el Historial
                </a>
                <a href="?periodo=hoy<?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" class="filtro-periodo-btn <?= ($periodo === 'hoy') ? 'active' : '' ?>">
                    Hoy
                </a>
                <a href="?periodo=7dias<?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" class="filtro-periodo-btn <?= ($periodo === '7dias') ? 'active' : '' ?>">
                    Últimos 7 días
                </a>
                <a href="?periodo=30dias<?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" class="filtro-periodo-btn <?= ($periodo === '30dias') ? 'active' : '' ?>">
                    Últimos 30 días
                </a>
                <a href="?periodo=mes<?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" class="filtro-periodo-btn <?= ($periodo === 'mes') ? 'active' : '' ?>">
                    Este Mes
                </a>
            </div>
        </div>

        <!-- Formulario de Rango de Fechas y Búsqueda -->
        <form action="/administrador/historial" method="GET" class="m-0">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="fecha_desde" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Desde Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde" value="<?= htmlspecialchars($fechaDesde, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-6 col-md-3">
                    <label for="fecha_hasta" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Hasta Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta" value="<?= htmlspecialchars($fechaHasta, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label for="buscar" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Búsqueda</label>
                    <input type="text" class="form-control form-control-sm" id="buscar" name="buscar" placeholder="Socio, código u operador..." value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1.5">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        Filtrar
                    </button>
                    <?php if ($hayFiltrosActivos): ?>
                        <a href="/administrador/historial" class="btn btn-sm btn-outline-secondary px-2.5 flex-shrink-0" title="Restablecer filtros">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
