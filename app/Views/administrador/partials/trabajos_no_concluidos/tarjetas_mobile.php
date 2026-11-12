<?php
/**
 * Componente: Fichas de Trabajos No Concluidos para Móviles (< 768px)
 * Archivo: app/Views/administrador/partials/contingencias_tarjetas_mobile.php
 * 
 * Fichas táctiles adaptadas con información técnica y glosa previa.
 * 
 * @var array $trabajos
 */

$esAdmin = function_exists('hasRole') ? hasRole('Administrador') : (isset($_SESSION['usuario']['nombre_rol']) && $_SESSION['usuario']['nombre_rol'] === 'Administrador');
?>
<div class="d-block d-md-none p-3" id="listaMobileNoConcluidos">
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

        $glosaMostrar = !empty($trabajo['glosa_interna']) 
            ? $trabajo['glosa_interna'] 
            : (!empty($trabajo['glosa']) ? $trabajo['glosa'] : 'Sin observación registrada');

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
        <div class="card border rounded-3 p-3 mb-2.5 shadow-sm fila-contingencia-item" data-categoria="<?= $categoriaKey ?>" style="border-color: #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="font-monospace fw-bold text-muted small">#<?= htmlspecialchars($idStr, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge border fw-normal py-0.5 px-2 <?= $servicioClass ?>" style="font-size: 0.72rem;">
                        <?= htmlspecialchars($servicioEtiqueta, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <?php if ($estCalc === 'NO PROCEDENTE'): ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fw-normal">No Procedente</span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-normal">No Concluido</span>
                <?php endif; ?>
            </div>

            <div class="fw-semibold text-dark"><?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?></div>
            <?php if ($codSocio): ?>
                <div class="text-secondary font-monospace small">Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($esAdmin): ?>
                <div class="mt-2 text-secondary small d-flex align-items-center justify-content-between flex-wrap gap-1 p-2 rounded bg-light border" style="border-color: #f1f5f9;">
                    <span>
                        <span class="text-muted">Operador:</span>
                        <strong class="text-dark"><?= htmlspecialchars($operadorReporta, ENT_QUOTES, 'UTF-8') ?></strong>
                    </span>
                    <?php if (!empty($fechaSeguimiento)): ?>
                        <span class="font-monospace text-muted" style="font-size: 0.72rem;">
                            <?= htmlspecialchars(date('d/m/Y H:i', strtotime($fechaSeguimiento)), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-2.5">
                <div class="text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Informe de Terreno:</div>
                <div class="bloque-observacion">
                    <?= htmlspecialchars($glosaMostrar, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center gap-2 mt-3 pt-2 border-top" style="border-color: #f1f5f9;">
                <?php if (!empty($coords)): ?>
                    <a href="https://www.google.com/maps?q=<?= urlencode($coords) ?>" target="_blank" class="btn btn-sm btn-outline-secondary px-3">
                        GPS
                    </a>
                <?php endif; ?>
                <a href="/administrador/trabajos/detalle?tipo=<?= urlencode($tipoTrabajo) ?>&id=<?= urlencode($idStr) ?>&origen=no_concluidos" class="btn btn-sm btn-primary px-3">
                    Gestionar
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
