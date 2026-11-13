<?php
/**
 * Componente: Paginación de Trabajos No Concluidos
 * Archivo: app/Views/administrador/partials/contingencias_paginacion.php
 * 
 * Paginación numérica preservando el parámetro de búsqueda.
 * 
 * @var int $totalPaginas
 * @var int $p
 * @var int $totalTrabajos
 * @var array $trabajos
 * @var string $buscar
 */
?>
<?php if ($totalPaginas > 1): ?>
    <div class="card-footer bg-white py-3 border-top" style="border-color: #e2e8f0;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-secondary small">
                Mostrando <?= count($trabajos) ?> de <?= $totalTrabajos ?> resultados (Página <?= $p ?> de <?= $totalPaginas ?>)
            </div>
            <nav aria-label="Paginación de trabajos no concluidos">
                <ul class="pagination pagination-sm flex-wrap justify-content-center justify-content-md-end mb-0">
                    <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $p - 1 ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" <?= ($p <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                    </li>
                    <?php
                    $startPage = max(1, $p - 2);
                    $endPage   = min($totalPaginas, $p + 2);

                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?p=1' . (!empty($buscar) ? '&buscar=' . urlencode($buscar) : '') . '">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        $active = ($i === $p) ? 'active' : '';
                        echo '<li class="page-item ' . $active . '"><a class="page-link" href="?p=' . $i . (!empty($buscar) ? '&buscar=' . urlencode($buscar) : '') . '">' . $i . '</a></li>';
                    }

                    if ($endPage < $totalPaginas) {
                        if ($endPage < $totalPaginas - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?p=' . $totalPaginas . (!empty($buscar) ? '&buscar=' . urlencode($buscar) : '') . '">' . $totalPaginas . '</a></li>';
                    }
                    ?>
                    <li class="page-item <?= ($p >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $p + 1 ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" <?= ($p >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>
