<?php
/**
 * Vista: Lista de Reconexiones Pendientes (Módulo Operador)
 * Diseñada para máxima simplicidad, claridad explícita y sin iconos superfluos.
 * 
 * @var string      $title
 * @var string      $especialidad
 * @var array|null  $trabajos
 * @var string|null $error
 */

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);

$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);

$totalPendientes = is_array($trabajos) ? count($trabajos) : 0;
?>

<div class="container-fluid px-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 mb-md-4 gap-2 border-bottom pb-3" style="border-color: #e2e8f0 !important;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h3 fw-bold mb-0 text-dark">Reconexiones Pendientes</h1>
                <?php if (is_array($trabajos)): ?>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.8rem;">
                        <?= $totalPendientes ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-secondary small mb-0">Especialidad Reconexión &bull; Cooperativa COSMOL</p>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-sm-end">
            <a href="/operador/historial" class="btn btn-sm btn-outline-secondary px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.8125rem;">
                Ver Mi Historial
            </a>
            <a href="/operador/trabajos" class="btn btn-sm btn-primary px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.8125rem;">
                Actualizar Lista
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border mb-3" role="alert" style="border-color: #a7f3d0 !important;">
            <div class="small fw-semibold"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($error || $sesionError): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border mb-3" role="alert" style="border-color: #fecaca !important;">
            <div class="small fw-semibold"><?= htmlspecialchars($error ?: $sesionError, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <?php if ($trabajos === null): ?>
        <!-- Estado de Error de Conexión Externa -->
        <div class="card shadow-sm border text-center p-4 p-md-5 bg-white rounded-2" style="border-color: #cbd5e1 !important;">
            <h2 class="h5 fw-bold text-dark mb-2">No se pudo conectar con el servidor de trabajos</h2>
            <p class="text-secondary small mb-3">
                No fue posible consultar la lista de reconexiones en tiempo real. Por favor verifique su conexión a internet o intente nuevamente.
            </p>
            <div>
                <a href="/operador/trabajos" class="btn btn-primary px-3 py-2 fw-semibold" style="font-size: 0.875rem;">
                    Reintentar Conexión
                </a>
            </div>
        </div>

    <?php elseif (empty($trabajos)): ?>
        <!-- Estado Vacío Positivo (0 Pendientes) -->
        <div class="card shadow-sm border text-center p-4 p-md-5 bg-white rounded-2" style="border-color: #cbd5e1 !important;">
            <h2 class="h5 fw-bold text-dark mb-2">¡Todo al día!</h2>
            <p class="text-secondary small mb-3">
                No tienes reconexiones pendientes en este momento. Las nuevas órdenes asignadas aparecerán aquí automáticamente al actualizar.
            </p>
            <div>
                <a href="/operador/trabajos" class="btn btn-outline-secondary px-3 py-2 fw-semibold" style="font-size: 0.875rem;">
                    Actualizar Lista
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- Barra de Búsqueda Rápida en Tiempo Real para el Operador -->
        <div class="card shadow-sm border mb-3 mb-md-4 bg-white rounded-2" style="border-color: #cbd5e1 !important;">
            <div class="card-body p-2 p-md-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md">
                        <label for="buscar-reconexiones" class="visually-hidden">Buscar</label>
                        <input type="text" 
                               id="buscar-reconexiones" 
                               class="form-control" 
                               placeholder="Escriba aquí para buscar por socio, código, calle, zona o número de orden..." 
                               style="border-color: #cbd5e1; font-size: 0.875rem;"
                               autocomplete="off">
                    </div>
                    <div class="col-12 col-md-auto text-end">
                        <span class="text-secondary small" id="contador-filtrados" style="font-size: 0.8125rem;">
                            Mostrando <strong><?= $totalPendientes ?></strong> de <?= $totalPendientes ?> trabajos
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid Directo de Tarjetas de Reconexión -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 g-md-4" id="contenedor-reconexiones">
            <?php foreach ($trabajos as $t): ?>
                <?php
                    $tipo = 'reconexion';
                    include __DIR__ . '/partials/trabajo_card.php';
                ?>
            <?php endforeach; ?>
        </div>

        <!-- Mensaje de Búsqueda sin Resultados -->
        <div id="sin-resultados-busqueda" class="card shadow-sm border text-center p-4 bg-white d-none mt-3 rounded-2" style="border-color: #cbd5e1 !important;">
            <div class="fw-bold text-dark mb-1">Sin coincidencias</div>
            <div class="text-secondary small">No se encontraron trabajos con ese término de búsqueda.</div>
        </div>

        <!-- Script de Búsqueda Rápida en Cliente (JavaScript Plano sin frameworks) -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var input = document.getElementById('buscar-reconexiones');
                var items = document.querySelectorAll('#contenedor-reconexiones .col-trabajo');
                var contador = document.getElementById('contador-filtrados');
                var sinResultados = document.getElementById('sin-resultados-busqueda');
                var total = items.length;

                if (input && items.length > 0) {
                    input.addEventListener('input', function() {
                        var q = this.value.toLowerCase().trim();
                        var visibles = 0;
                        items.forEach(function(el) {
                            var txt = el.getAttribute('data-busqueda') || el.textContent.toLowerCase();
                            if (q === '' || txt.indexOf(q) !== -1) {
                                el.classList.remove('d-none');
                                visibles++;
                            } else {
                                el.classList.add('d-none');
                            }
                        });
                        if (contador) {
                            contador.innerHTML = 'Mostrando <strong>' + visibles + '</strong> de ' + total + ' trabajos';
                        }
                        if (sinResultados) {
                            if (visibles === 0) {
                                sinResultados.classList.remove('d-none');
                            } else {
                                sinResultados.classList.add('d-none');
                            }
                        }
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>
