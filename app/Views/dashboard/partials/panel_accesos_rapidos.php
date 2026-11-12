<?php
/**
 * Componente: Panel Lateral de Accesos Directos Operativos
 * Archivo: app/Views/dashboard/partials/panel_accesos_rapidos.php
 * 
 * Enlaces rápidos a los módulos más frecuentados según rol y permisos.
 * 
 * @var string $rol
 * @var int $totalPendientes
 * @var int $totalNoConcluidos
 * @var int $totalOperadores
 * @var int $totalConsultas
 * @var int $totalConsultasApp
 */
?>
<div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
    <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4" style="border-color: #e2e8f0;">
        <h2 class="h6 fw-bold text-dark mb-0">
            Accesos Directos Operativos
        </h2>
    </div>
    <div class="card-body p-3 d-flex flex-column justify-content-between gap-2.5">
        <div class="d-grid gap-2">
            <a href="/administrador/trabajos" class="btn btn-light border text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between hover-shadow transition">
                <div>
                    <div class="fw-semibold text-dark small">Supervisión de Trabajos</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Reconexiones y reclamos pendientes en terreno</small>
                </div>
                <span class="badge bg-warning-subtle text-dark border font-monospace"><?= $totalPendientes ?></span>
            </a>

            <a href="/administrador/trabajos-no-concluidos" class="btn btn-light border text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between hover-shadow transition">
                <div>
                    <div class="fw-semibold text-dark small">Trabajos No Concluidos</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Contingencias registradas por operadores</small>
                </div>
                <span class="badge bg-danger-subtle text-danger border font-monospace"><?= $totalNoConcluidos ?></span>
            </a>

            <?php if ($rol === 'Administrador'): ?>
            <a href="/administrador/usuarios" class="btn btn-light border text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between hover-shadow transition">
                <div>
                    <div class="fw-semibold text-dark small">Gestión de Personal</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Operadores técnicos, especialidades y roles</small>
                </div>
                <span class="badge bg-primary-subtle text-primary border font-monospace"><?= $totalOperadores ?></span>
            </a>
            <?php endif; ?>

            <a href="/reportes/chatbot" class="btn btn-light border text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between hover-shadow transition">
                <div>
                    <div class="fw-semibold text-dark small">Auditoría Chatbot WhatsApp</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Consultas de socios y pagos QR</small>
                </div>
                <span class="badge bg-success-subtle text-success border font-monospace"><?= $totalConsultas ?></span>
            </a>

            <a href="/reportes/app-movil" class="btn btn-light border text-start p-2.5 rounded-3 d-flex align-items-center justify-content-between hover-shadow transition">
                <div>
                    <div class="fw-semibold text-dark small">Auditoría App Móvil</div>
                    <small class="text-muted" style="font-size: 0.75rem;">Facturas en PDF y pasarelas digitales</small>
                </div>
                <span class="badge bg-info-subtle text-primary border font-monospace"><?= $totalConsultasApp ?></span>
            </a>
        </div>
    </div>
</div>
