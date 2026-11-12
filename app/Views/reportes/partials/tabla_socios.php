<?php
/**
 * Parcial: Vista Agrupada por Socios (Usuarios Únicos)
 * Ubicación: app/Views/reportes/partials/tabla_socios.php
 *
 * @var array    $socios            Lista de socios paginados
 * @var array    $accionesPorSocio  Detalle de acciones indexado por socio_key
 * @var int      $desde             Índice correlativo inicial
 * @var bool     $esFiltroQrActivo  Indicador si se está auditando Pagos QR
 * @var callable $getTipoBadgeClass Función para badges de tipo de evento
 */
?>

<!-- Versión Escritorio: Tabla de Alta Densidad (≥ 768px) -->
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-center text-secondary text-uppercase fw-semibold" style="width: 60px; font-size: 0.72rem; letter-spacing: 0.05em;">#</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Cód. Socio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Titular / Solicitante</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Teléfono</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Total Consultas</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 0.72rem; letter-spacing: 0.05em;">Última Actividad</th>
                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($socios)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-secondary">
                        <p class="mb-1 fw-semibold text-dark">
                            <?= $esFiltroQrActivo ? 'No se encontraron pagos por QR registrados' : 'No se encontraron usuarios' ?>
                        </p>
                        <p class="small text-muted mb-0">No existen registros que coincidan con los criterios de filtrado aplicados.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($socios as $idx => $s): ?>
                    <?php 
                    $numUsuario = $desde + $idx;
                    $sKey = $s['socio_key'];
                    $misAcciones = isset($accionesPorSocio[$sKey]) ? $accionesPorSocio[$sKey] : [];
                    $collapseId = 'collapse-socio-' . $idx;
                    $codSocio = !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C';
                    ?>
                    <tr class="align-middle">
                        <td class="ps-3 ps-sm-4 text-center text-muted font-monospace small">
                            #<?= $numUsuario ?>
                        </td>
                        <td>
                            <span class="font-monospace text-secondary small">
                                <?= $codSocio ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">
                                <?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </td>
                        <td class="font-monospace small text-secondary">
                            <?= !empty($s['telefono']) ? htmlspecialchars($s['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">
                                <?= (int)$s['total_consultas'] ?> <?= (int)$s['total_consultas'] === 1 ? 'consulta' : 'consultas' ?>
                            </span>
                        </td>
                        <td class="text-center text-muted font-monospace small">
                            <div><?= htmlspecialchars($s['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div style="font-size: 0.72rem;"><?= htmlspecialchars($s['ultima_hora'], ENT_QUOTES, 'UTF-8') ?></div>
                        </td>
                        <td class="text-end pe-3 pe-sm-4 text-nowrap">
                            <button class="btn btn-sm btn-outline-primary px-2.5 py-1"
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#<?= $collapseId ?>" 
                                    aria-expanded="false" 
                                    aria-controls="<?= $collapseId ?>">
                                Ver consultas (<?= (int)$s['total_consultas'] ?>)
                            </button>
                        </td>
                    </tr>

                    <!-- Fila Colapsable de Acciones del Usuario -->
                    <tr class="p-0 border-0">
                        <td colspan="7" class="p-0 border-0">
                            <div class="collapse" id="<?= $collapseId ?>">
                                <div class="p-3 bg-light border-top border-bottom" style="border-color: #e2e8f0;">
                                    <div class="card border rounded-3 shadow-sm bg-white" style="border-color: #cbd5e1;">
                                        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom" style="border-color: #e2e8f0;">
                                            <div class="text-dark small fw-semibold">
                                                Consultas de <?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Titular', ENT_QUOTES, 'UTF-8') ?> (Cód. <?= $codSocio ?>)
                                            </div>
                                            <span class="badge bg-light text-secondary border fw-normal">
                                                <?= count($misAcciones) ?> <?= count($misAcciones) === 1 ? 'registro' : 'registros' ?>
                                            </span>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0 align-middle">
                                                    <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                        <tr>
                                                            <th class="ps-3 py-2 text-secondary text-uppercase fw-semibold" style="width: 50px; font-size: 0.68rem;">#</th>
                                                            <th class="py-2 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.68rem;">Teléfono</th>
                                                            <th class="py-2 text-secondary text-uppercase fw-semibold" style="font-size: 0.68rem;">Tipo de Consulta</th>
                                                            <th class="py-2 text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.68rem;">Fecha y Hora</th>
                                                            <th class="pe-3 py-2 text-end text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.68rem;">Atendido por</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($misAcciones)): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-3 text-muted small">
                                                                    No hay consultas adicionales para este socio en el filtro actual.
                                                                </td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($misAcciones as $accIdx => $acc): ?>
                                                                <?php $badgeClass = $getTipoBadgeClass($acc['id_tipo'] ?? 0); ?>
                                                                <tr>
                                                                    <td class="ps-3 py-2 text-muted font-monospace small"><?= $accIdx + 1 ?></td>
                                                                    <td class="py-2 font-monospace small text-secondary">
                                                                        <?= !empty($acc['telefono']) ? htmlspecialchars($acc['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?>
                                                                    </td>
                                                                    <td class="py-2">
                                                                        <span class="badge border fw-normal py-1 px-2 <?= $badgeClass ?>">
                                                                            <?= htmlspecialchars($acc['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="py-2 text-muted font-monospace small">
                                                                        <?= htmlspecialchars($acc['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($acc['hora_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </td>
                                                                    <td class="pe-3 py-2 text-end">
                                                                        <?php if (!empty($acc['username'])): ?>
                                                                            <span class="badge bg-light text-dark border fw-normal py-1 px-2 font-monospace small">
                                                                                <?= htmlspecialchars($acc['username'], ENT_QUOTES, 'UTF-8') ?>
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Versión Móvil: Fichas de Socios Adaptadas (< 768px) -->
<div class="d-block d-md-none p-3" id="listaMobileSocios">
    <?php if (empty($socios)): ?>
        <div class="text-center py-4 text-secondary">
            <p class="mb-1 fw-semibold text-dark">
                <?= $esFiltroQrActivo ? 'No se encontraron pagos por QR registrados' : 'No se encontraron usuarios' ?>
            </p>
            <p class="small text-muted mb-0">No existen registros que coincidan con los criterios aplicados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($socios as $idx => $s): ?>
            <?php 
            $numUsuario = $desde + $idx;
            $sKey = $s['socio_key'];
            $misAcciones = isset($accionesPorSocio[$sKey]) ? $accionesPorSocio[$sKey] : [];
            $collapseId = 'collapse-mobile-socio-' . $idx;
            $codSocio = !empty($s['codigo_socio']) ? htmlspecialchars($s['codigo_socio'], ENT_QUOTES, 'UTF-8') : 'S/C';
            ?>
            <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-monospace fw-bold text-muted small">#<?= $numUsuario ?> &bull; Cód. <?= $codSocio ?></span>
                    <span class="badge bg-light text-dark border font-monospace">
                        <?= (int)$s['total_consultas'] ?> <?= (int)$s['total_consultas'] === 1 ? 'consulta' : 'consultas' ?>
                    </span>
                </div>
                <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars(!empty($s['nombres']) ? $s['nombres'] : 'Desconocido', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="d-flex justify-content-between align-items-center small text-secondary mt-1">
                    <span>Tel: <?= !empty($s['telefono']) ? htmlspecialchars($s['telefono'], ENT_QUOTES, 'UTF-8') : '—' ?></span>
                    <span class="text-muted font-monospace" style="font-size: 0.74rem;">Último: <?= htmlspecialchars($s['ultima_fecha'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="mt-2.5 pt-2 border-top" style="border-color: #f1f5f9;">
                    <button class="btn btn-sm btn-outline-primary w-100 py-1"
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#<?= $collapseId ?>" 
                            aria-expanded="false" 
                            aria-controls="<?= $collapseId ?>">
                        Ver consultas detalladas (<?= (int)$s['total_consultas'] ?>)
                    </button>

                    <div class="collapse mt-2" id="<?= $collapseId ?>">
                        <div class="border rounded-2 p-2 bg-light">
                            <?php if (empty($misAcciones)): ?>
                                <div class="text-center text-muted small py-2">Sin consultas adicionales</div>
                            <?php else: ?>
                                <?php foreach ($misAcciones as $acc): ?>
                                    <?php $badgeClass = $getTipoBadgeClass($acc['id_tipo'] ?? 0); ?>
                                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-color: #e2e8f0;">
                                        <span class="badge border fw-normal <?= $badgeClass ?>" style="font-size: 0.7rem;">
                                            <?= htmlspecialchars($acc['tipo'] ?? 'General', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="font-monospace text-muted" style="font-size: 0.72rem;">
                                            <?= htmlspecialchars($acc['fecha_consulta'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
