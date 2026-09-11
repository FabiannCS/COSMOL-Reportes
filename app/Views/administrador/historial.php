<?php
$trabajos      = isset($trabajos) ? $trabajos : [];
$totalTrabajos = isset($totalTrabajos) ? $totalTrabajos : 0;
$p             = isset($p) ? $p : 1;
$totalPaginas  = isset($totalPaginas) ? $totalPaginas : 1;
$buscar        = isset($buscar) ? $buscar : '';
$errores       = isset($errores) ? $errores : [];

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 mb-md-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">
                Historial de Trabajos Concluidos
            </h1>
            <p class="text-muted mb-0">Visualiza los trabajos operativos que has finalizado.</p>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0"></i>
            <div class="small flex-grow-1"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($sesionError): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0"></i>
            <div class="small flex-grow-1"><?= htmlspecialchars($sesionError, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjeta resumen -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-3">
                <div class="card-body text-center">
                    <div class="text-xs fw-bold text-success text-uppercase mb-2" style="font-size: 0.9rem; letter-spacing: 0.5px;">
                        Total Trabajos Concluidos (Todos los operadores)
                    </div>
                    <div class="display-5 fw-bold text-dark mb-0">
                        <?= number_format($totalTrabajos) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Historial -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h5 class="card-title mb-0 fw-bold d-flex align-items-center text-dark">
                <i class="bi bi-journal-check text-success me-2 fs-4"></i>
                Registro Global de Actividades
            </h5>
            <form action="" method="GET" class="d-flex w-100 w-md-auto m-0">
                <input type="text" name="buscar" class="form-control form-control-sm me-2" placeholder="Buscar por socio, código u operador..." value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
                <?php if (!empty($buscar)): ?>
                    <a href="/administrador/historial" class="btn btn-sm btn-outline-secondary ms-1 flex-shrink-0" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>ID Trabajo</th>
                            <th>Socio</th>
                            <th>Realizado por</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trabajos)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1 text-secondary opacity-50"></i></div>
                                    Aún no se registra ningún trabajo concluido.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trabajos as $trabajo): ?>
                                <tr>
                                    <td>
                                        <?php if ($trabajo['tipo_trabajo'] === 'reconexion'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-lightning-charge me-1"></i> Reconexión</span>
                                        <?php else: ?>
                                            <?php if (isset($trabajo['id_tipo_reclamo']) && $trabajo['id_tipo_reclamo'] == 2): ?>
                                                <span class="badge bg-info text-dark"><i class="bi bi-droplet me-1"></i> Agua Potable</span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white"><i class="bi bi-water me-1"></i> Alcantarillado</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <?php 
                                        $id_str = $trabajo['tipo_trabajo'] === 'reconexion' 
                                            ? ($trabajo['id_reconexion'] ?? '—') 
                                            : ($trabajo['id_reclamo'] ?? '—');
                                    ?>
                                    <td class="fw-bold">#<?= htmlspecialchars($id_str, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($trabajo['nombre_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small">Cod: <?= htmlspecialchars($trabajo['cod_socio'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace fw-normal">
                                            <i class="bi bi-person me-1 text-secondary"></i><?= htmlspecialchars($trabajo['operador_nombre'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $estCalc = isset($trabajo['estado_calculado']) ? $trabajo['estado_calculado'] : ($trabajo['estado'] ?? 'CONCLUIDO');
                                        ?>
                                        <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill px-3">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>No Concluido
                                            </span>
                                        <?php elseif ($estCalc === 'NO PROCEDENTE'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary border-opacity-25 rounded-pill px-3">
                                                <i class="bi bi-slash-circle me-1"></i>No Procedente
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-3">
                                                <i class="bi bi-check-circle-fill me-1"></i><?= ($trabajo['tipo_trabajo'] === 'reconexion' ? 'Concluida' : 'Solucionado') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/administrador/trabajos/detalle?tipo=<?= urlencode($trabajo['tipo_trabajo']) ?>&id=<?= urlencode($id_str) ?>&origen=historial" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-eye"></i> Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                <div class="text-muted small">
                    Mostrando <?= count($trabajos) ?> de <?= $totalTrabajos ?> resultados (Página <?= $p ?> de <?= $totalPaginas ?: 1 ?>)
                </div>
                <?php if ($totalPaginas > 1): ?>
                    <nav aria-label="Paginación de historial" class="overflow-auto w-100 mt-2 mt-md-0">
                        <ul class="pagination pagination-sm flex-wrap justify-content-md-end mb-0">
                            <li class="page-item <?= ($p <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?p=<?= $p - 1 ?><?= !empty($buscar) ? '&buscar=' . urlencode($buscar) : '' ?>" <?= ($p <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Anterior</a>
                            </li>
                            <?php
                            // Mostrar hasta 5 páginas alrededor de la actual
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
