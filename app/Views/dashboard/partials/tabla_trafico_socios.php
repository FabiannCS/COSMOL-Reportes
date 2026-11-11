<?php
/**
 * Componente: Tabla de Monitoreo de Tráfico Telefónico
 * Archivo: app/Views/dashboard/partials/tabla_trafico_socios.php
 * 
 * Socios con mayor interacción en el canal Chatbot WhatsApp para control preventivo.
 * 
 * @var array $numerosMasActivos
 */
?>
<div class="card shadow-sm border rounded-3 h-100" style="border-color: #e2e8f0;">
    <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2" style="border-color: #e2e8f0;">
        <div>
            <h2 class="h6 fw-bold text-dark mb-0">
                Monitoreo de Tráfico: Socios con Mayor Interacción
            </h2>
            <small class="text-muted">Control preventivo para detectar consultas reiteradas en los canales de atención.</small>
        </div>
        <a href="/reportes/chatbot" class="btn btn-sm btn-outline-primary px-3">
            Ver todos
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($numerosMasActivos)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th scope="col" class="ps-3 ps-sm-4 py-2.5 text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Teléfono</th>
                        <th scope="col" class="py-2.5 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Último Solicitante</th>
                        <th scope="col" class="py-2.5 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 0.72rem; letter-spacing: 0.05em;">Consultas</th>
                        <th scope="col" class="py-2.5 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Última Actividad</th>
                        <th scope="col" class="py-2.5 text-secondary text-uppercase fw-semibold text-center" style="width: 130px; font-size: 0.72rem; letter-spacing: 0.05em;">Nivel</th>
                        <th scope="col" class="pe-3 pe-sm-4 py-2.5 text-secondary text-uppercase fw-semibold text-end" style="width: 100px; font-size: 0.72rem; letter-spacing: 0.05em;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($numerosMasActivos as $item): ?>
                        <?php
                            $totalNum = (int)$item['total_consultas'];
                            $nivelBadge = $totalNum >= 25 
                                ? 'bg-danger-subtle text-danger border-danger-subtle' 
                                : ($totalNum >= 10 ? 'bg-warning-subtle text-dark border-warning-subtle' : 'bg-success-subtle text-success border-success-subtle');
                            $nivelTexto = $totalNum >= 25 ? 'Frecuente' : ($totalNum >= 10 ? 'Moderado' : 'Normal');
                        ?>
                        <tr>
                            <td class="ps-3 ps-sm-4 py-2.5 font-monospace text-secondary small">
                                <?= htmlspecialchars($item['telefono'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="py-2.5 fw-semibold text-dark small text-truncate" style="max-width: 200px;">
                                <?= htmlspecialchars(!empty($item['ultimo_nombre']) ? $item['ultimo_nombre'] : 'Socio', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="py-2.5 text-center">
                                <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">
                                    <?= $totalNum ?>
                                </span>
                            </td>
                            <td class="py-2.5 text-muted font-monospace small">
                                <?= htmlspecialchars($item['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="py-2.5 text-center">
                                <span class="badge <?= $nivelBadge ?> border px-2 py-0.5 rounded-pill small">
                                    <?= $nivelTexto ?>
                                </span>
                            </td>
                            <td class="pe-3 pe-sm-4 py-2.5 text-end">
                                <a href="/reportes/chatbot?buscar=<?= urlencode($item['telefono']) ?>" class="btn btn-sm btn-outline-primary px-2.5 py-0.5" style="font-size: 0.75rem;" title="Ver historial de este socio">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="p-4 text-center text-muted small">
            No se han registrado consultas reiteradas en este periodo.
        </div>
        <?php endif; ?>
    </div>
</div>
