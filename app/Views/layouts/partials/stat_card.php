<?php
/**
 * Componente Reutilizable: Tarjeta de Métrica / KPI Estadístico
 *
 * Renderiza una tarjeta compacta con ícono, título, valor numérico
 * y un subtexto opcional. Diseñada para usar clases utilitarias de Bootstrap 5.
 *
 * Variables esperadas (definidas antes de incluir este archivo):
 *
 * @var string $statTitle      Etiqueta superior de la métrica (ej. "Total Usuarios").
 * @var mixed  $statValue      Valor numérico principal a mostrar.
 * @var string $statColor      Nombre del color Bootstrap: primary, success, danger, warning, info, secondary.
 * @var string $statIcon       Clase del ícono Bootstrap Icons (ej. "bi-people-fill").
 * @var string $statExtra      (Opcional) HTML adicional debajo del valor (ej. desglose).
 * @var string $statValueColor (Opcional) Clase de color para el valor. Por defecto: "text-dark".
 */

$statExtra      = isset($statExtra) ? $statExtra : '';
$statValueColor = isset($statValueColor) ? $statValueColor : 'text-dark';
?>
<div class="col-12 col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm border-start border-<?= $statColor ?> border-4 h-100 py-3">
        <div class="card-body text-center">
            <div class="text-xs fw-bold text-<?= $statColor ?> text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                <?= htmlspecialchars($statTitle, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="display-5 fw-bold <?= $statValueColor ?> mb-0">
                <?= $statValue ?>
            </div>
            <?php if (!empty($statExtra)): ?>
                <div class="mt-2">
                    <?= $statExtra ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
