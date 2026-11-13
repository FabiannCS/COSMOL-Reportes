<?php
/**
 * Parcial: Barra de Paginación y Resumen de Registros
 * Ubicación: app/Views/reportes/partials/paginacion.php
 *
 * @var string   $vista          Modo de vista activo ('socios' o 'acciones')
 * @var int      $desde          Primer registro visible
 * @var int      $hasta          Último registro visible
 * @var int      $totalRegistros Total de registros en la vista
 * @var int      $pagina         Página actual
 * @var int      $totalPaginas   Total de páginas
 * @var callable $pageUrl        Función generadora de URLs paginadas
 */
?>
<?php if ($totalPaginas > 1 || $totalRegistros > 0): ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top bg-light gap-3" style="border-color: #e2e8f0;">
        <div class="small text-secondary">
            <?php if ($vista === 'socios'): ?>
                Mostrando usuarios del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de <strong><?= number_format($totalRegistros) ?> usuarios únicos</strong>
            <?php else: ?>
                Mostrando consultas del <strong><?= $desde ?></strong> al <strong><?= $hasta ?></strong> de <strong><?= number_format($totalRegistros) ?> registradas</strong>
            <?php endif; ?>
        </div>
        <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Navegación de páginas">
                <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center justify-content-md-end">
                    <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $pageUrl($pagina - 1) ?>#tabla-reportes" <?= ($pagina <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Anterior
                        </a>
                    </li>

                    <?php
                    $startPage = max(1, $pagina - 2);
                    $endPage   = min($totalPaginas, $pagina + 2);
                    if ($startPage > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>#tabla-reportes">1</a></li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl($i) ?>#tabla-reportes"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPaginas): ?>
                        <?php if ($endPage < $totalPaginas - 1): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= $pageUrl($totalPaginas) ?>#tabla-reportes"><?= $totalPaginas ?></a></li>
                    <?php endif; ?>

                    <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $pageUrl($pagina + 1) ?>#tabla-reportes" <?= ($pagina >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Siguiente
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
<?php endif; ?>
