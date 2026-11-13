<?php
/**
 * Componente: Tabla de Trabajos No Concluidos para Pantallas Grandes (Desktop)
 * Archivo: app/Views/administrador/partials/contingencias_tabla_desktop.php
 * 
 * Vista tabular de alta densidad (visible en pantallas >= 768px).
 * 
 * @var array $trabajos
 */
?>
<?php
$esAdmin = function_exists('hasRole') ? hasRole('Administrador') : (isset($_SESSION['usuario']['nombre_rol']) && $_SESSION['usuario']['nombre_rol'] === 'Administrador');
?>
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0" id="tablaDesktopNoConcluidos">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 75px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 125px; font-size: 0.72rem; letter-spacing: 0.05em;">Servicio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Llegada / Plazo (24h)</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 0.72rem; letter-spacing: 0.05em;">Socio</th>
                <?php if ($esAdmin): ?>
                    <th class="text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Operador</th>
                <?php endif; ?>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 0.72rem; letter-spacing: 0.05em;">Estado</th>
                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Informe Previo / Observación</th>
                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($trabajos as $trabajo): ?>
                <?php
                $tipoTrabajo = isset($trabajo['tipo_trabajo']) ? $trabajo['tipo_trabajo'] : 'reconexion';
                $idStr = ($tipoTrabajo === 'reconexion') 
                    ? (isset($trabajo['id_reconexion']) ? $trabajo['id_reconexion'] : '—') 
                    : (isset($trabajo['id_reclamo']) ? $trabajo['id_reclamo'] : '—');
                
                $idTipoRecl = isset($trabajo['id_tipo_reclamo']) ? (int)$trabajo['id_tipo_reclamo'] : 0;
                $nombreSocio = isset($trabajo['nombre_socio']) ? trim($trabajo['nombre_socio']) : 'Sin nombre registrado';
                $codSocio    = isset($trabajo['cod_socio']) ? trim($trabajo['cod_socio']) : '';
                $coords      = isset($trabajo['coordenadas_gps']) ? preg_replace('/\s+/', '', $trabajo['coordenadas_gps']) : '';
                
                $operadorReporta = !empty($trabajo['operador_reporta']) 
                    ? $trabajo['operador_reporta'] 
                    : (!empty($trabajo['nombre_operador']) ? $trabajo['nombre_operador'] : 'No asignado');
                $fechaSeguimiento = !empty($trabajo['fecha_seguimiento']) ? $trabajo['fecha_seguimiento'] : '';

                $estCalc = isset($trabajo['estado_calculado']) 
                    ? $trabajo['estado_calculado'] 
                    : (isset($trabajo['estado']) ? $trabajo['estado'] : 'NO CONCLUIDO');

                $fechaReg = !empty($trabajo['fecha_registro']) ? $trabajo['fecha_registro'] : '';
                $plazo = function_exists('calcularPlazo24h') 
                    ? calcularPlazo24h($fechaReg, $estCalc) 
                    : ['fechaLlegada' => $fechaReg, 'label' => '24h', 'badgeClass' => 'bg-light text-secondary', 'fechaLimite' => '', 'estadoPlazo' => 'desconocido'];

                $glosaMostrar = !empty($trabajo['glosa_interna']) 
                    ? $trabajo['glosa_interna'] 
                    : (!empty($trabajo['glosa']) ? $trabajo['glosa'] : 'Sin observación registrada');

                // Distintivo de Servicio
                $servicioEtiqueta = 'Reconexión';
                $servicioClass    = 'bg-warning-subtle text-dark border-warning-subtle';
                $categoriaKey     = 'reconexion';

                if ($tipoTrabajo !== 'reconexion') {
                    if ($idTipoRecl === 2) {
                        $servicioEtiqueta = 'Agua Potable';
                        $servicioClass    = 'bg-info-subtle text-info-emphasis border-info-subtle';
                        $categoriaKey     = 'agua';
                    } else {
                        $servicioEtiqueta = 'Alcantarillado';
                        $servicioClass    = 'bg-success-subtle text-success-emphasis border-success-subtle';
                        $categoriaKey     = 'alcantarillado';
                    }
                }
                ?>
                <tr class="fila-contingencia-item" data-categoria="<?= $categoriaKey ?>">
                    <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                        #<?= htmlspecialchars($idStr, ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="text-center">
                        <span class="badge border fw-normal py-1 px-2 <?= $servicioClass ?>">
                            <?= htmlspecialchars($servicioEtiqueta, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="busqueda-fecha" style="width: 170px;">
                        <div class="d-flex flex-column gap-1">
                            <div class="text-dark fw-semibold font-monospace small d-inline-flex align-items-center gap-1">
                                <i class="bi bi-clock-history text-primary" style="font-size: 0.8rem;"></i>
                                <?= $plazo['fechaLlegada'] ?>
                            </div>
                            <?php if ($plazo['estadoPlazo'] !== 'desconocido'): ?>
                                <div>
                                    <span class="badge font-monospace <?= $plazo['badgeClass'] ?>" style="font-size: 0.70rem; letter-spacing: 0.02em;" title="Límite 24h: <?= $plazo['fechaLimite'] ?>">
                                        <?= $plazo['label'] ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark text-break">
                            <?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php if ($codSocio): ?>
                            <div class="text-secondary font-monospace" style="font-size: 0.76rem;">
                                Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php if ($esAdmin): ?>
                        <td>
                            <div class="d-flex align-items-center gap-1.5 text-dark small">
                                <span class="fw-medium text-break"><?= htmlspecialchars($operadorReporta, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php if (!empty($fechaSeguimiento)): ?>
                                <div class="text-secondary font-monospace" style="font-size: 0.72rem;">
                                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($fechaSeguimiento)), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    <td class="text-center">
                        <?php if ($estCalc === 'NO PROCEDENTE'): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-normal py-1 px-2">
                                No Procedente
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal py-1 px-2">
                                No Concluido
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="small" style="max-width: 320px;">
                        <div class="bloque-observacion" title="<?= htmlspecialchars($glosaMostrar, ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($glosaMostrar, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </td>
                    <td class="text-center pe-3 pe-sm-4 text-nowrap">
                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                            <a href="/administrador/trabajos/detalle?tipo=<?= urlencode($tipoTrabajo) ?>&id=<?= urlencode($idStr) ?>&origen=no_concluidos" 
                               class="btn btn-sm btn-outline-primary px-3 py-1">
                                Gestionar
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
