<?php
/**
 * Vista: Dashboard Principal
 * @var int $totalConsultas Total de consultas del chatbot.
 * @var int $totalPendientes Total de trabajos pendientes.
 * @var int $totalConcluidos Total de trabajos concluidos.
 * @var int $totalOperadores Total de operadores activos.
 */
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 text-dark">Panel Principal</h1>
            <p class="text-muted small mb-0">Bienvenido al Sistema de Reportes y Gestión de Trabajos de COSMOL.</p>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Consultas Chatbot</div>
                        <div class="h4 fw-bold mb-0 text-dark"><?= isset($totalConsultas) ? (int)$totalConsultas : 0 ?></div>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                        <i class="bi bi-chat-dots-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Trabajos Pendientes</div>
                        <div class="h4 fw-bold mb-0 text-dark"><?= isset($totalPendientes) ? (int)$totalPendientes : 0 ?></div>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Trabajos Concluidos</div>
                        <div class="h4 fw-bold mb-0 text-dark"><?= isset($totalConcluidos) ? (int)$totalConcluidos : 0 ?></div>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Operadores Activos</div>
                        <div class="h4 fw-bold mb-0 text-dark"><?= isset($totalOperadores) ? (int)$totalOperadores : 0 ?></div>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Directos -->
    <div class="row g-3 mt-2">
        <div class="col-12 col-md-4">
            <a href="/administrador/trabajos" class="btn btn-outline-warning w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-2">
                <span class="fw-bold fs-5 text-dark"><i class="bi bi-card-checklist me-2 text-warning"></i>Ver Trabajos Pendientes</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="/administrador/usuarios" class="btn btn-outline-info w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-2">
                <span class="fw-bold fs-5 text-dark"><i class="bi bi-people me-2 text-info"></i>Gestionar Personal</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="/reportes/visualizar" class="btn btn-outline-primary w-100 py-3 text-start d-flex align-items-center justify-content-between rounded-3 border-2">
                <span class="fw-bold fs-5 text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Ver Reportes</span>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        </div>
    </div>
</div>
