<?php
/**
 * Vista Parcial: Tabla de Reclamos (Administrador / Supervisor)
 * 
 * @var array $reclamos
 * @var array $reclamosPaginados
 * @var int $pRecl
 * @var int $totalPaginasRecl
 * @var int $pRec
 */
?>
<div class="card border shadow-sm rounded-3 mb-4" id="tabla-reclamos" style="border-color: #e2e8f0;">
    <div class="card-header bg-white border-bottom py-3 px-3 px-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2" style="border-color: #e2e8f0;">
        <div>
            <h5 class="card-title fw-bold mb-0 text-dark fs-6">Reclamos Técnicos Pendientes</h5>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-end">
            <input type="text" id="filtroReclamos" class="form-control form-control-sm" placeholder="Buscar en esta página..." style="max-width: 220px;">
            <span class="badge bg-light text-secondary border fw-normal py-1.5 px-2.5 text-nowrap">
                <?= count($reclamos) ?> pendientes
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($reclamosPaginados)): ?>
            <!-- Vista de Escritorio: Tabla de Alta Densidad (Visible en pantallas medianas y grandes) -->
            <div class="d-none d-md-block table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaDesktopReclamos">
                    <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 75px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                            <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 0.72rem; letter-spacing: 0.05em;">Servicio</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="width: 175px; font-size: 0.72rem; letter-spacing: 0.05em;">Llegada / Plazo (24h)</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="width: 210px; font-size: 0.72rem; letter-spacing: 0.05em;">Socio</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Descripción</th>
                            <th class="text-secondary text-uppercase fw-semibold" style="width: 210px; font-size: 0.72rem; letter-spacing: 0.05em;">Ubicación</th>
                            <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 0.72rem; letter-spacing: 0.05em;">Estado</th>
                            <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reclamosPaginados as $recl): ?>
                            <?php
                            $idRecl      = isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '';
                            $tipoReclamo = 'Reclamo';
                            $tipoBadgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                            if (isset($recl['id_tipo_reclamo'])) {
                                if ((int)$recl['id_tipo_reclamo'] === 2) {
                                    $tipoReclamo = 'Agua Potable';
                                    $tipoBadgeClass = 'bg-info-subtle text-info-emphasis border-info-subtle';
                                } elseif ((int)$recl['id_tipo_reclamo'] === 3) {
                                    $tipoReclamo = 'Alcantarillado';
                                    $tipoBadgeClass = 'bg-success-subtle text-success-emphasis border-success-subtle';
                                }
                            }
                            $codSocio    = isset($recl['cod_socio']) ? trim($recl['cod_socio']) : '';
                            $nombreSocio = isset($recl['nombre_socio']) ? trim($recl['nombre_socio']) : '';
                            $rawCoords   = isset($recl['coordenadas_gps']) ? $recl['coordenadas_gps'] : '';
                            $direccion   = isset($recl['direccion_predio']) ? trim($recl['direccion_predio']) : '';
                            $gpsInfo     = function_exists('normalizarYValidarGps') 
                                ? normalizarYValidarGps($rawCoords, $direccion) 
                                : ['esGps' => false, 'urlMaps' => '', 'urlDomicilio' => '', 'enMontero' => false];
                            $estCalc     = isset($recl['estado_calculado']) ? $recl['estado_calculado'] : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($recl) : 'PENDIENTE');
                            $desc        = isset($recl['descripcion']) ? trim($recl['descripcion']) : '';
                            $ubicacion   = isset($recl['ubicacion']) ? trim($recl['ubicacion']) : '';
                            $zona        = isset($recl['zona']) ? trim($recl['zona']) : '';
                            $ruta        = isset($recl['ruta']) ? trim($recl['ruta']) : '';
                            $fechaReg    = isset($recl['fecha_registro']) ? $recl['fecha_registro'] : '';
                            $plazo       = function_exists('calcularPlazo24h') 
                                ? calcularPlazo24h($fechaReg, $estCalc) 
                                : ['fechaLlegada' => $fechaReg, 'label' => '24h', 'badgeClass' => 'bg-light text-secondary', 'fechaLimite' => ''];
                            ?>
                            <tr class="fila-reclamo-item">
                                <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                                    #<?= htmlspecialchars($idRecl ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge border fw-normal py-1 px-2 busqueda-tipo <?= $tipoBadgeClass ?>">
                                        <?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="busqueda-fecha" style="width: 175px;">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="text-dark fw-semibold font-monospace small d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-clock-history text-primary" style="font-size: 0.8rem;"></i>
                                            <?= $plazo['fechaLlegada'] ?>
                                        </div>
                                        <div>
                                            <span class="badge font-monospace <?= $plazo['badgeClass'] ?>" style="font-size: 0.70rem; letter-spacing: 0.02em;" title="Límite 24h: <?= $plazo['fechaLimite'] ?>">
                                                <?= $plazo['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-break busqueda-socio">
                                        <?= htmlspecialchars($nombreSocio ?: 'Sin nombre registrado', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <?php if ($codSocio): ?>
                                        <div class="text-secondary font-monospace busqueda-cod" style="font-size: 0.76rem;">
                                            Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-secondary small busqueda-desc" style="max-width: 220px; line-height: 1.4;">
                                    <div class="text-truncate" title="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($desc ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="small busqueda-ubi">
                                    <div class="fw-medium text-dark text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($ubicacion ?: $direccion, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($ubicacion ?: ($direccion ?: '—'), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.76rem;">
                                        Zona: <?= htmlspecialchars($zona ?: '—', ENT_QUOTES, 'UTF-8') ?> | Ruta: <?= htmlspecialchars($ruta ?: '—', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal py-1 px-2">
                                            No Concluido
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-secondary border border-secondary-subtle fw-normal py-1 px-2">
                                            Pendiente
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 pe-sm-4 text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                                        <?php if (!empty($gpsInfo['esGps'])): ?>
                                            <a href="<?= htmlspecialchars($gpsInfo['urlMaps'], ENT_QUOTES, 'UTF-8') ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="btn btn-sm btn-outline-danger px-2.5 py-1 <?= empty($gpsInfo['enMontero']) ? 'border-warning text-warning-emphasis' : '' ?>" 
                                               title="<?= empty($gpsInfo['enMontero']) ? 'Ubicación GPS referencial (fuera de Montero)' : 'Ver ubicación GPS en Google Maps' ?>">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>GPS
                                            </a>
                                        <?php elseif (!empty($gpsInfo['urlDomicilio'])): ?>
                                            <a href="<?= htmlspecialchars($gpsInfo['urlDomicilio'], ENT_QUOTES, 'UTF-8') ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="btn btn-sm btn-outline-secondary px-2 py-1" 
                                               title="Buscar domicilio en Montero">
                                                <i class="bi bi-house me-1"></i>Domicilio
                                            </a>
                                        <?php endif; ?>
                                        <a href="/administrador/trabajos/detalle?tipo=reclamo&id=<?= urlencode($idRecl) ?>&origen=trabajos" 
                                           class="btn btn-sm btn-outline-primary px-3 py-1">
                                            <?= ($estCalc === 'NO CONCLUIDO') ? 'Revisar' : 'Concluir' ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Vista Móvil: Fichas de Trabajo Adaptadas (Visible en celulares y pantallas < 768px) -->
            <div class="d-block d-md-none p-3" id="listaMobileReclamos">
                <?php foreach ($reclamosPaginados as $recl): ?>
                    <?php
                    $idRecl      = isset($recl['id_reclamo']) ? $recl['id_reclamo'] : '';
                    $tipoReclamo = 'Reclamo';
                    $tipoBadgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                    if (isset($recl['id_tipo_reclamo'])) {
                        if ((int)$recl['id_tipo_reclamo'] === 2) {
                            $tipoReclamo = 'Agua Potable';
                            $tipoBadgeClass = 'bg-info-subtle text-info-emphasis border-info-subtle';
                        } elseif ((int)$recl['id_tipo_reclamo'] === 3) {
                            $tipoReclamo = 'Alcantarillado';
                            $tipoBadgeClass = 'bg-success-subtle text-success-emphasis border-success-subtle';
                        }
                    }
                    $codSocio    = isset($recl['cod_socio']) ? trim($recl['cod_socio']) : '';
                    $nombreSocio = isset($recl['nombre_socio']) ? trim($recl['nombre_socio']) : '';
                    $coords      = isset($recl['coordenadas_gps']) ? preg_replace('/\s+/', '', $recl['coordenadas_gps']) : '';
                    $estCalc     = isset($recl['estado_calculado']) ? $recl['estado_calculado'] : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($recl) : 'PENDIENTE');
                    $desc        = isset($recl['descripcion']) ? trim($recl['descripcion']) : '';
                    $ubicacion   = isset($recl['ubicacion']) ? trim($recl['ubicacion']) : '';
                    $direccion   = isset($recl['direccion_predio']) ? trim($recl['direccion_predio']) : '';
                    $zona        = isset($recl['zona']) ? trim($recl['zona']) : '';
                    $ruta        = isset($recl['ruta']) ? trim($recl['ruta']) : '';
                    $fechaReg    = isset($recl['fecha_registro']) ? $recl['fecha_registro'] : '';
                    $plazo       = function_exists('calcularPlazo24h') 
                        ? calcularPlazo24h($fechaReg, $estCalc) 
                        : ['fechaLlegada' => $fechaReg, 'label' => '24h', 'badgeClass' => 'bg-light text-secondary', 'fechaLimite' => ''];
                    ?>
                    <div class="card border rounded-3 p-3 mb-2.5 shadow-sm fila-reclamo-item" style="border-color: #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="font-monospace fw-bold text-muted small">#<?= htmlspecialchars($idRecl ?: '—', ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge border fw-normal py-0.5 px-2 busqueda-tipo <?= $tipoBadgeClass ?>" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <?php if ($estCalc === 'NO CONCLUIDO'): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal">No Concluido</span>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary border border-secondary-subtle fw-normal">Pendiente</span>
                            <?php endif; ?>
                        </div>

                        <!-- Fila de Llegada y Plazo 24h -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 py-1.5 px-2 my-2 rounded bg-light border busqueda-fecha" style="border-color: #e2e8f0 !important;">
                            <span class="text-secondary font-monospace small d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                <i class="bi bi-clock-history text-primary"></i> Llegada: <strong class="text-dark"><?= $plazo['fechaLlegada'] ?></strong>
                            </span>
                            <span class="badge font-monospace <?= $plazo['badgeClass'] ?>" style="font-size: 0.70rem;" title="Límite: <?= $plazo['fechaLimite'] ?>">
                                <?= $plazo['label'] ?>
                            </span>
                        </div>

                        <div class="fw-semibold text-dark busqueda-socio"><?= htmlspecialchars($nombreSocio ?: 'Sin nombre registrado', ENT_QUOTES, 'UTF-8') ?></div>
                        <?php if ($codSocio): ?>
                            <div class="text-secondary font-monospace small busqueda-cod">Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?></div>
                        <?php endif; ?>
                        
                        <div class="small text-secondary mt-2 busqueda-ubi">
                            <div><?= htmlspecialchars($ubicacion ?: ($direccion ?: 'Sin dirección detallada'), ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="text-muted" style="font-size: 0.76rem;">Zona: <?= htmlspecialchars($zona ?: '—', ENT_QUOTES, 'UTF-8') ?> | Ruta: <?= htmlspecialchars($ruta ?: '—', ENT_QUOTES, 'UTF-8') ?></div>
                        </div>

                        <?php if ($desc): ?>
                            <div class="small text-muted mt-2 pt-2 border-top busqueda-desc" style="border-color: #f1f5f9; font-size: 0.82rem;">
                                <?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end align-items-center gap-2 mt-3 pt-2 border-top" style="border-color: #f1f5f9;">
                            <?php if (!empty($gpsInfo['esGps'])): ?>
                                <a href="<?= htmlspecialchars($gpsInfo['urlMaps'], ENT_QUOTES, 'UTF-8') ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-danger px-3 <?= empty($gpsInfo['enMontero']) ? 'border-warning text-warning-emphasis' : '' ?>"
                                   title="<?= empty($gpsInfo['enMontero']) ? 'Ubicación GPS fuera de Montero' : 'Ver ubicación GPS' ?>">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>GPS
                                </a>
                            <?php elseif (!empty($gpsInfo['urlDomicilio'])): ?>
                                <a href="<?= htmlspecialchars($gpsInfo['urlDomicilio'], ENT_QUOTES, 'UTF-8') ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-secondary px-2.5"
                                   title="Buscar domicilio en Montero">
                                    <i class="bi bi-house me-1"></i>Domicilio
                                </a>
                            <?php endif; ?>
                            <a href="/administrador/trabajos/detalle?tipo=reclamo&id=<?= urlencode($idRecl) ?>&origen=trabajos" class="btn btn-sm btn-primary px-3">
                                <?= ($estCalc === 'NO CONCLUIDO') ? 'Revisar' : 'Concluir' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-secondary">
                <p class="mb-1 fw-semibold text-dark">No hay reclamos pendientes</p>
                <p class="small text-muted mb-0">No se registran reclamos técnicos pendientes en el sistema externo.</p>
            </div>
        <?php endif; ?>

        <!-- Paginación Reclamos -->
        <?php if ($totalPaginasRecl > 1): ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 p-3 border-top bg-light" style="border-color: #e2e8f0;">
                <div class="small text-secondary">
                    Página <span class="fw-semibold text-dark"><?= $pRecl ?></span> de <span class="fw-semibold text-dark"><?= $totalPaginasRecl ?></span> (Total: <?= count($reclamos) ?>)
                </div>
                <nav aria-label="Paginación Reclamos">
                    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                        <li class="page-item <?= ($pRecl <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $pRecl - 1 ?>#tabla-reclamos" <?= ($pRecl <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                Anterior
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPaginasRecl; $i++): ?>
                            <li class="page-item <?= ($i === $pRecl) ? 'active' : '' ?>">
                                <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $i ?>#tabla-reclamos"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pRecl >= $totalPaginasRecl) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?prec=<?= $pRec ?>&precl=<?= $pRecl + 1 ?>#tabla-reclamos" <?= ($pRecl >= $totalPaginasRecl) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                Siguiente
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var inputFiltroRecl = document.getElementById('filtroReclamos');
    if (inputFiltroRecl) {
        inputFiltroRecl.addEventListener('input', function () {
            var termino = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('#tabla-reclamos .fila-reclamo-item');
            items.forEach(function (item) {
                var textoSocio = (item.querySelector('.busqueda-socio') ? item.querySelector('.busqueda-socio').textContent : '').toLowerCase();
                var textoCod   = (item.querySelector('.busqueda-cod') ? item.querySelector('.busqueda-cod').textContent : '').toLowerCase();
                var textoTipo  = (item.querySelector('.busqueda-tipo') ? item.querySelector('.busqueda-tipo').textContent : '').toLowerCase();
                var textoDesc  = (item.querySelector('.busqueda-desc') ? item.querySelector('.busqueda-desc').textContent : '').toLowerCase();
                var textoUbi   = (item.querySelector('.busqueda-ubi') ? item.querySelector('.busqueda-ubi').textContent : '').toLowerCase();
                var textoFecha = (item.querySelector('.busqueda-fecha') ? item.querySelector('.busqueda-fecha').textContent : '').toLowerCase();

                if (termino === '' || 
                    textoSocio.indexOf(termino) !== -1 || 
                    textoCod.indexOf(termino) !== -1 || 
                    textoTipo.indexOf(termino) !== -1 || 
                    textoDesc.indexOf(termino) !== -1 || 
                    textoUbi.indexOf(termino) !== -1 ||
                    textoFecha.indexOf(termino) !== -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>
