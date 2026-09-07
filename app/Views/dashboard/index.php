<?php
/**
 * Vista: Dashboard Principal
 * @var int $totalConsultas
 * @var int $totalPendientes
 * @var int $recPendCount
 * @var int $reclPendCount
 * @var int $totalConcluidos
 * @var int $recConCount
 * @var int $reclConCount
 * @var int $totalOperadores
 * @var array $operadoresPorEspecialidad
 * @var array|null $usuario
 */

$rol = isset($usuario['nombre_rol']) ? $usuario['nombre_rol'] : 'Usuario';
$nombre = isset($usuario['username']) ? $usuario['username'] : 'Invitado';
$recPend = isset($recPendCount) ? (int)$recPendCount : 0;
$reclPend = isset($reclPendCount) ? (int)$reclPendCount : 0;
$recCon = isset($recConCount) ? (int)$recConCount : 0;
$reclCon = isset($reclConCount) ? (int)$reclConCount : 0;
$especialidades = isset($operadoresPorEspecialidad) && is_array($operadoresPorEspecialidad) ? $operadoresPorEspecialidad : [];
?>
<div class="container-fluid p-0">
    <!-- Encabezado de Página -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1 text-dark">Panel Principal</h1>
            <p class="text-muted small mb-0">Resumen en tiempo real del estado de trabajos y consultas del chatbot de COSMOL.</p>
        </div>
    </div>

    <!-- Tarjetas de Resumen KPI Informativas -->
    <div class="row g-3 g-xl-4 mb-3">
        <!-- Consultas Chatbot -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="me-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Consultas Chatbot</span>
                        <span class="h2 fw-bold mb-1 d-block text-dark"><?= isset($totalConsultas) ? (int)$totalConsultas : 0 ?></span>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-chat-dots-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trabajos Pendientes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="me-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Trabajos Pendientes</span>
                        <span class="h2 fw-bold mb-1 d-block text-dark"><?= isset($totalPendientes) ? (int)$totalPendientes : 0 ?></span>
                        <small class="text-muted d-block text-truncate">
                            <span class="fw-semibold"><?= $recPend ?></span> rec. - <span class="fw-semibold"><?= $reclPend ?></span> recl.
                        </small>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-hourglass-split fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trabajos Concluidos -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="me-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Trabajos Concluidos</span>
                        <span class="h2 fw-bold mb-1 d-block text-dark"><?= isset($totalConcluidos) ? (int)$totalConcluidos : 0 ?></span>
                        <small class="text-muted d-block text-truncate">
                            <span class="fw-semibold"><?= $recCon ?></span> rec. - <span class="fw-semibold"><?= $reclCon ?></span> recl.
                        </small>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Operadores -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div class="me-3">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Operadores</span>
                        <span class="h2 fw-bold mb-1 d-block text-dark"><?= isset($totalOperadores) ? (int)$totalOperadores : 0 ?></span>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 d-flex align-items-center justify-content-center">
                        <i class="bi bi-people-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribución de Operadores por Especialidad -->
    <?php if (!empty($especialidades)): ?>
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark h5">Operadores por Especialidad:</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($especialidades as $esp): ?>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small fw-normal">
                            <i class="bi bi-person me-1 text-primary"></i>
                            <?= htmlspecialchars(isset($esp['especialidad']) ? $esp['especialidad'] : '', ENT_QUOTES, 'UTF-8') ?>:
                            <strong class="ms-1 text-primary"><?= isset($esp['total']) ? (int)$esp['total'] : 0 ?></strong>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Accesos Directos Dinámicos por Rol (Tamaño Compacto) -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="text-muted text-uppercase fw-bold small mb-0">
            Accesos Directos
        </h6>
    </div>
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <a href="/administrador/trabajos" class="btn btn-outline-warning w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-card-checklist me-2 text-warning fs-5 align-middle"></i>Ver Trabajos Pendientes</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
        <?php if ($rol === 'Administrador'): ?>
        <div class="col-12 col-md-4">
            <a href="/administrador/usuarios" class="btn btn-outline-info w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-people me-2 text-info fs-5 align-middle"></i>Gestionar Usuarios</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
        <?php endif; ?>
        <div class="col-12 col-md-4">
            <a href="/reportes/visualizar" class="btn btn-outline-primary w-100 py-2 px-3 text-start d-flex align-items-center justify-content-between rounded-3 border">
                <span class="fw-semibold small text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary fs-5 align-middle"></i>Ver Reportes</span>
                <i class="bi bi-chevron-right text-muted small"></i>
            </a>
        </div>
    </div>
</div>
