<?php
/**
 * Parcial: Tarjetas de Auditoría y Métricas KPI por Tipo de Consulta
 * Ubicación: app/Views/reportes/partials/tarjetas_kpi.php
 *
 * @var array    $totalesPorTipo    Conteo de consultas agrupadas por cada tipo
 * @var array    $queryParams       Parámetros GET activos
 * @var array    $filtros           Filtros aplicados actualmente
 * @var callable $getTipoBorderColor Función para color de borde según id_tipo
 */
?>
<?php if (!empty($totalesPorTipo)): ?>
    <div class="row g-2 g-md-3 mb-4">
        <?php foreach ($totalesPorTipo as $tipoStat): ?>
            <?php
            $idTipoStat   = (int)$tipoStat['id_tipo'];
            $nombreStat   = ($idTipoStat === 12) ? 'Pago Código QR' : $tipoStat['nombre'];
            $totalStat    = (int)$tipoStat['total'];
            $borderColor  = $getTipoBorderColor($idTipoStat);
            $esTipoActivo = (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === $idTipoStat);

            // Parámetros de URL al hacer clic en la tarjeta (toggle)
            $paramsFiltro = $queryParams;
            if ($esTipoActivo) {
                unset($paramsFiltro['id_tipo']);
            } else {
                $paramsFiltro['id_tipo'] = $idTipoStat;
            }
            $paramsFiltro['p'] = 1;
            $urlFiltroTipo = '/reportes/visualizar' . (!empty($paramsFiltro) ? '?' . http_build_query($paramsFiltro) : '');
            ?>
            <div class="col-6 col-md-4 col-xl">
                <a href="<?= htmlspecialchars($urlFiltroTipo, ENT_QUOTES, 'UTF-8') ?>" 
                   class="text-decoration-none d-block h-100" 
                   title="<?= $esTipoActivo ? 'Clic para quitar filtro' : 'Filtrar por ' . htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="card tarjeta-kpi shadow-sm rounded-3 h-100 <?= $esTipoActivo ? 'bg-primary-subtle' : 'bg-white' ?>" 
                         style="border: 1px solid <?= $esTipoActivo ? '#0284c7' : '#cbd5e1' ?>; border-top: 3px solid <?= $borderColor ?>;">
                        <div class="card-body p-2.5 text-center">
                            <div class="text-secondary small fw-semibold text-uppercase text-truncate mb-1" 
                                 style="font-size: 0.68rem; letter-spacing: 0.04em;" 
                                 title="<?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($nombreStat, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="h4 fw-bold text-dark mb-0 font-monospace">
                                <?= number_format($totalStat) ?>
                            </div>
                            <?php if ($esTipoActivo): ?>
                                <div class="mt-1">
                                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.62rem;">
                                        Filtro activo
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
