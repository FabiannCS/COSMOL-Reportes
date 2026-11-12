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
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0" id="tablaDesktopNoConcluidos">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 75px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Servicio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 230px; font-size: 0.72rem; letter-spacing: 0.05em;">Socio</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 0.72rem; letter-spacing: 0.05em;">Estado</th>
                <th class="text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Informe Previo / Observación</th>
                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
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
                
                $estCalc = isset($trabajo['estado_calculado']) 
                    ? $trabajo['estado_calculado'] 
                    : (isset($trabajo['estado']) ? $trabajo['estado'] : 'NO CONCLUIDO');

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
                    <td class="text-end pe-3 pe-sm-4 text-nowrap">
                        <div class="d-inline-flex align-items-center gap-1.5 justify-content-end">
                            <?php if (!empty($coords)): ?>
                                <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-secondary px-2.5 py-1" 
                                   title="Ver en Google Maps">
                                    GPS
                                </a>
                            <?php endif; ?>
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
