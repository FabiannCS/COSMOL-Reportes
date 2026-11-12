<?php
/**
 * Parcial: Vista de Registro Detallado de Acciones (Cronología)
 * Ubicación: app/Views/reportes/partials/tabla_acciones.php
 *
 * @var array    $consultas         Lista de consultas paginadas
 * @var bool     $esFiltroQrActivo  Indicador si se está auditando Pagos QR
 * @var callable $getTipoBadgeClass Función para badges de tipo de evento
 */
?>

<!-- Versión Escritorio (≥ 768px) -->
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 80px; font-size: 0.72rem; letter-spacing: 0.05em;"># ID</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Cód. Socio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Titular / Solicitante</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Teléfono WhatsApp</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 0.72rem; letter-spacing: 0.05em;">Tipo de Consulta</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Fecha y Hora</th>
                <th class="pe-3 pe-sm-4 text-end text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Atendido por</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($consultas)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <p class="mb-1 fw-semibold text-dark">
                            <?= $esFiltroQrActivo ? 'No se encontraron pagos por QR registrados' : 'No se encontraron consultas' ?>
                        </p>
                        <p class="small text-muted mb-0">No existen registros que coincidan con los criterios de búsqueda aplicados.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($consultas as $row): ?>
                    <?php $badgeClass = $getTipoBadgeClass($row['id_tipo'] ?? 0); ?>
                    <tr>
                        <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                            #<?= (int)$row['id_consulta'] ?>
                        </td>
                        <td class="font-monospace text-secondary small">
                            <?= !empty($row['codigo_socio']) ? htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C' ?>
                        </td>
                        <td class="fw-semibold text-dark">
                            <?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="font-monospace text-secondary small">
                            <?= !empty($row['telefono']) ? htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                        </td>
                        <td>
                            <span class="badge border fw-normal py-1 px-2 <?= $badgeClass ?>">
                                <?= htmlspecialchars($row['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="text-muted font-monospace small">
                            <?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="pe-3 pe-sm-4 text-end">
                            <?php if (!empty($row['username'])): ?>
                                <span class="badge bg-light text-dark border fw-normal py-1 px-2 font-monospace small">
                                    <?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary border-primary-subtle fw-normal py-1 px-2 small">
                                    Chatbot
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Versión Móvil: Fichas de Acciones (< 768px) -->
<div class="d-block d-md-none p-3" id="listaMobileAcciones">
    <?php if (empty($consultas)): ?>
        <div class="text-center py-4 text-secondary">
            <p class="mb-1 fw-semibold text-dark">
                <?= $esFiltroQrActivo ? 'No se encontraron pagos por QR registrados' : 'No se encontraron consultas' ?>
            </p>
            <p class="small text-muted mb-0">No hay registros con los filtros actuales.</p>
        </div>
    <?php else: ?>
        <?php foreach ($consultas as $row): ?>
            <?php $badgeClass = $getTipoBadgeClass($row['id_tipo'] ?? 0); ?>
            <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-monospace fw-bold text-muted small">#<?= (int)$row['id_consulta'] ?> &bull; Cód. <?= !empty($row['codigo_socio']) ? htmlspecialchars($row['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C' ?></span>
                    <span class="badge border fw-normal <?= $badgeClass ?>" style="font-size: 0.72rem;">
                        <?= htmlspecialchars($row['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div class="fw-semibold text-dark"><?= htmlspecialchars(!empty($row['nombres']) ? $row['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="d-flex justify-content-between align-items-center small text-secondary mt-2 pt-2 border-top" style="border-color: #f1f5f9;">
                    <span>Tel: <?= !empty($row['telefono']) ? htmlspecialchars($row['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?></span>
                    <span class="text-muted font-monospace" style="font-size: 0.74rem;">
                        <?= htmlspecialchars($row['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($row['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <div class="mt-1 text-end">
                    <span class="badge bg-light text-secondary border fw-normal" style="font-size: 0.68rem;">
                        Atendido por: <?= !empty($row['username']) ? htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') : 'Chatbot' ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
