<?php
/**
 * Vista de Supervisión de Trabajos (Administrador / Supervisor)
 * 
 * @var array $reconexiones Lista completa de reconexiones pendientes.
 * @var array $reclamos Lista completa de reclamos pendientes.
 * @var array $reconexionesPaginadas
 * @var array $reclamosPaginados
 * @var array $reclamosAguaPotable
 * @var array $reclamosAlcantarillado
 * @var array $errores
 * @var int $pRec
 * @var int $totalPaginasRec
 * @var int $pRecl
 * @var int $totalPaginasRecl
 */

$reconexiones           = isset($reconexiones) ? $reconexiones : [];
$reclamos               = isset($reclamos) ? $reclamos : [];
$reconexionesPaginadas  = isset($reconexionesPaginadas) ? $reconexionesPaginadas : [];
$reclamosPaginados      = isset($reclamosPaginados) ? $reclamosPaginados : [];
$reclamosAguaPotable    = isset($reclamosAguaPotable) ? $reclamosAguaPotable : [];
$reclamosAlcantarillado = isset($reclamosAlcantarillado) ? $reclamosAlcantarillado : [];
$errores                = isset($errores) ? $errores : [];
$pRec                   = isset($pRec) ? (int)$pRec : 1;
$totalPaginasRec        = isset($totalPaginasRec) ? (int)$totalPaginasRec : 1;
$pRecl                  = isset($pRecl) ? (int)$pRecl : 1;
$totalPaginasRecl       = isset($totalPaginasRecl) ? (int)$totalPaginasRecl : 1;

$totalPendientes = count($reconexiones) + count($reclamos);

$mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
unset($_SESSION['mensaje']);
$sesionError = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']);
?>

<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Supervisión de Trabajos</h1>
        </div>
        <div>
            <a href="/administrador/trabajos" class="btn btn-outline-secondary btn-sm py-1 px-2.5 shadow-sm" style="font-size: 0.78rem; font-weight: 500;">
                Actualizar datos
            </a>
        </div>
    </div>

    <!-- Alertas de Retroalimentación y Estado de APIs -->
    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($sesionError): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-sm border" role="alert">
            <div class="small text-dark"><?= htmlspecialchars($sesionError, ENT_QUOTES, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <?php foreach ($errores as $err): ?>
            <div class="alert alert-warning alert-dismissible fade show mb-3 shadow-sm border" role="alert">
                <div class="small text-dark"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Métricas Resumen Operativo (2x2 en móviles, 4x1 en desktop) con bordes distintivos -->
    <div class="row g-3 mb-4">
        <!-- Total Pendientes (Azul Oficial COSMOL) -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 h-100" style="border: 1px solid #cbd5e1; border-top: 3px solid #0284c7; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Total Pendientes</div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="h3 fw-bold text-dark mb-0 font-monospace"><?= number_format($totalPendientes) ?></span>
                        <span class="text-muted small">órdenes activas</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reconexiones (Ámbar Operativo) -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 h-100" style="border: 1px solid #cbd5e1; border-top: 3px solid #f59e0b; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Reconexiones</div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="h3 fw-bold text-dark mb-0 font-monospace"><?= number_format(count($reconexiones)) ?></span>
                        <span class="text-muted small">en cola</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agua Potable (Cian Agua) -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 h-100" style="border: 1px solid #cbd5e1; border-top: 3px solid #0ea5e9; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Agua Potable</div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="h3 fw-bold text-dark mb-0 font-monospace"><?= number_format(count($reclamosAguaPotable)) ?></span>
                        <span class="text-muted small">reclamos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alcantarillado (Verde Sanitario) -->
        <div class="col-6 col-lg-3">
            <div class="card shadow-sm rounded-3 h-100" style="border: 1px solid #cbd5e1; border-top: 3px solid #059669; background: #ffffff;">
                <div class="card-body p-3">
                    <div class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Alcantarillado</div>
                    <div class="d-flex align-items-baseline gap-2 mt-1">
                        <span class="h3 fw-bold text-dark mb-0 font-monospace"><?= number_format(count($reclamosAlcantarillado)) ?></span>
                        <span class="text-muted small">reclamos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Selección Rápida de Sección con Identidad COSMOL -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <div class="p-1 rounded-3 border d-inline-flex flex-wrap gap-1 w-100 w-sm-auto" id="filtroTipoTrabajos" style="background-color: #f1f5f9; border-color: #e2e8f0 !important;" role="group" aria-label="Filtrar tipo de trabajo">
            <button type="button" class="btn btn-sm filtro-trabajo-btn active" data-target="todos">
                Todas las órdenes <span class="badge rounded-pill ms-1 font-monospace filtro-contador"><?= $totalPendientes ?></span>
            </button>
            <button type="button" class="btn btn-sm filtro-trabajo-btn" data-target="reconexiones">
                Reconexiones <span class="badge rounded-pill ms-1 font-monospace filtro-contador"><?= count($reconexiones) ?></span>
            </button>
            <button type="button" class="btn btn-sm filtro-trabajo-btn" data-target="reclamos">
                Reclamos <span class="badge rounded-pill ms-1 font-monospace filtro-contador"><?= count($reclamos) ?></span>
            </button>
        </div>
        <div class="small text-secondary d-none d-sm-block">
            Datos externos en tiempo real
        </div>
    </div>

    <!-- Tabla 1: Reconexiones Pendientes -->
    <?php include __DIR__ . '/partials/trabajos/tabla_reconexiones.php'; ?>

    <!-- Tabla 2: Reclamos Pendientes -->
    <?php include __DIR__ . '/partials/trabajos/tabla_reclamos.php'; ?>
</div>

<!-- Control de Vistas y Soporte BFCache en iOS/Safari -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Selector de vista entre tablas con colores COSMOL
    var botonesFiltro = document.querySelectorAll('#filtroTipoTrabajos .filtro-trabajo-btn');
    var secRec = document.getElementById('tabla-reconexiones');
    var secRecl = document.getElementById('tabla-reclamos');

    function aplicarFiltro(tipo) {
        if (!secRec || !secRecl) return;
        if (tipo === 'reconexiones') {
            secRec.style.display = '';
            secRecl.style.display = 'none';
        } else if (tipo === 'reclamos') {
            secRec.style.display = 'none';
            secRecl.style.display = '';
        } else {
            secRec.style.display = '';
            secRecl.style.display = '';
        }
        botonesFiltro.forEach(function (btn) {
            if (btn.getAttribute('data-target') === tipo) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    botonesFiltro.forEach(function (btn) {
        btn.addEventListener('click', function () {
            aplicarFiltro(this.getAttribute('data-target'));
        });
    });

    // Detectar parámetros URL para activar pestaña correcta
    try {
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('precl') && !urlParams.has('prec')) {
            aplicarFiltro('reclamos');
        } else if (window.location.hash === '#tabla-reclamos') {
            aplicarFiltro('reclamos');
        } else if (urlParams.has('prec') && !urlParams.has('precl')) {
            aplicarFiltro('reconexiones');
        } else {
            aplicarFiltro('todos');
        }
    } catch (e) {}

    // Soporte para Safari BFCache
    window.addEventListener("pageshow", function(event) {
        if (event.persisted) {
            var tables = document.querySelectorAll('.table-responsive');
            tables.forEach(function(container) {
                var currentDisplay = container.style.display;
                container.style.display = 'none';
                container.offsetHeight;
                container.style.display = currentDisplay || '';
            });
        }
    });
});
</script>
