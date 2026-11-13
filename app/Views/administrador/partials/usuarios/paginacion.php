<?php
/**
 * Componente: Paginación de Usuarios
 * Archivo: app/Views/administrador/partials/usuarios_paginacion.php
 * 
 * Controles de paginación numérica con preservación de filtros en la query string.
 * 
 * @var int $totalPaginas
 * @var int $paginaActual
 * @var int $totalUsuarios
 * @var string $queryString
 */
?>
<?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
    <div class="card-footer bg-white py-3 border-top" style="border-color: #e2e8f0;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="text-secondary small">
                Página <span class="fw-semibold text-dark"><?= $paginaActual ?></span> de <span class="fw-semibold text-dark"><?= $totalPaginas ?></span> (Total: <?= $totalUsuarios ?>)
            </div>
            <nav aria-label="Navegación de páginas">
                <ul class="pagination pagination-sm flex-wrap justify-content-center justify-content-md-end mb-0">
                    <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $paginaActual - 1 ?><?= $queryString ?>#tabla-usuarios" <?= ($paginaActual <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i === $paginaActual) ? 'active' : '' ?>">
                            <a class="page-link" href="?p=<?= $i ?><?= $queryString ?>#tabla-usuarios"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?p=<?= $paginaActual + 1 ?><?= $queryString ?>#tabla-usuarios" <?= ($paginaActual >= $totalPaginas) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>
