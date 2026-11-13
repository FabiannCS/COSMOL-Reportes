<?php
/**
 * Componente: Fichas de Historial para Pantallas Móviles (< 768px)
 * Archivo: app/Views/administrador/partials/historial_tarjetas_mobile.php
 * 
 * Tarjetas adaptadas para teléfonos y pantallas pequeñas.
 * 
 * @var array $trabajos
 */
?>
<div class="d-block d-md-none p-3" id="listaMobileHistorial">
    <?php foreach ($trabajos as $trabajo): ?>
        <?php
        $tipoTrabajo = isset($trabajo['tipo_trabajo']) ? $trabajo['tipo_trabajo'] : 'reconexion';
        $idStr = ($tipoTrabajo === 'reconexion') 
            ? (isset($trabajo['id_reconexion']) ? $trabajo['id_reconexion'] : '—') 
            : (isset($trabajo['id_reclamo']) ? $trabajo['id_reclamo'] : '—');

        $idTipoRecl = isset($trabajo['id_tipo_reclamo']) ? (int)$trabajo['id_tipo_reclamo'] : 0;
        $nombreSocio = isset($trabajo['nombre_socio']) ? trim($trabajo['nombre_socio']) : 'Sin nombre registrado';
        $codSocio    = isset($trabajo['cod_socio']) ? trim($trabajo['cod_socio']) : '';
        $operador    = isset($trabajo['operador_nombre']) ? trim($trabajo['operador_nombre']) : 'Sistema';

        $fechaStr = isset($trabajo['fecha_actualizacion']) && !empty($trabajo['fecha_actualizacion'])
            ? $trabajo['fecha_actualizacion']
            : (isset($trabajo['fecha_conclusion']) && !empty($trabajo['fecha_conclusion'])
                ? $trabajo['fecha_conclusion']
                : (isset($trabajo['fecha_reconexion']) && !empty($trabajo['fecha_reconexion'])
                    ? $trabajo['fecha_reconexion']
                    : (isset($trabajo['fecha_solucion']) && !empty($trabajo['fecha_solucion'])
                        ? $trabajo['fecha_solucion']
                        : (isset($trabajo['fecha_registro']) ? $trabajo['fecha_registro'] : ''))));

        $fechaFormateada = '—';
        if (!empty($fechaStr)) {
            $ts = strtotime($fechaStr);
            $fechaFormateada = $ts ? date('d/m/Y H:i', $ts) : htmlspecialchars($fechaStr, ENT_QUOTES, 'UTF-8');
        }

        $estCalc = isset($trabajo['estado_calculado']) 
            ? $trabajo['estado_calculado'] 
            : (isset($trabajo['estado']) ? $trabajo['estado'] : 'CONCLUIDO');

        $servicioEtiqueta = 'Reconexión';
        $servicioClass    = 'bg-warning-subtle text-dark border-warning-subtle';

        if ($tipoTrabajo !== 'reconexion') {
            if ($idTipoRecl === 2) {
                $servicioEtiqueta = 'Agua Potable';
                $servicioClass    = 'bg-info-subtle text-info-emphasis border-info-subtle';
            } else {
                $servicioEtiqueta = 'Alcantarillado';
                $servicioClass    = 'bg-success-subtle text-success-emphasis border-success-subtle';
            }
        }

        $estadoEtiqueta = ($tipoTrabajo === 'reconexion') ? 'Concluida' : 'Solucionado';
        $estadoClass    = 'bg-success-subtle text-success border-success-subtle';

        if ($estCalc === 'NO CONCLUIDO') {
            $estadoEtiqueta = 'No Concluido';
            $estadoClass    = 'bg-danger-subtle text-danger border-danger-subtle';
        } elseif ($estCalc === 'NO PROCEDENTE') {
            $estadoEtiqueta = 'No Procedente';
            $estadoClass    = 'bg-secondary-subtle text-secondary border-secondary-subtle';
        }
        ?>
        <div class="card border rounded-3 p-3 mb-2.5 shadow-sm" style="border-color: #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="font-monospace fw-bold text-muted small">#<?= htmlspecialchars($idStr, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="badge border fw-normal py-0.5 px-2 <?= $servicioClass ?>" style="font-size: 0.72rem;">
                        <?= htmlspecialchars($servicioEtiqueta, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <span class="badge border fw-normal <?= $estadoClass ?>">
                    <?= htmlspecialchars($estadoEtiqueta, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="fw-semibold text-dark"><?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?></div>
            <?php if ($codSocio): ?>
                <div class="text-secondary font-monospace small">Cód. <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 pt-2 border-top small" style="border-color: #f1f5f9;">
                <div class="text-secondary">
                    <span class="text-muted">Operador:</span> <?= htmlspecialchars($operador, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="text-muted font-monospace" style="font-size: 0.78rem;">
                    <?= $fechaFormateada ?>
                </div>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3 pt-2 border-top" style="border-color: #f1f5f9;">
                <a href="/administrador/trabajos/detalle?tipo=<?= urlencode($tipoTrabajo) ?>&id=<?= urlencode($idStr) ?>&origen=historial" class="btn btn-sm btn-primary px-3">
                    Ver detalle
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
