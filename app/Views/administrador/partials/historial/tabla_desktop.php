<?php
/**
 * Componente: Tabla de Historial para Pantallas Grandes (Desktop)
 * Archivo: app/Views/administrador/partials/historial_tabla_desktop.php
 * 
 * Vista tabular de órdenes concluidas (visible en pantallas >= 768px).
 * 
 * @var array $trabajos
 */
?>
<div class="d-none d-md-block table-responsive">
    <table class="table table-hover align-middle mb-0" id="tablaDesktopHistorial">
        <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <tr>
                <th class="ps-3 ps-sm-4 text-secondary text-uppercase fw-semibold" style="width: 75px; font-size: 0.72rem; letter-spacing: 0.05em;">ID</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.05em;">Servicio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 230px; font-size: 0.72rem; letter-spacing: 0.05em;">Socio</th>
                <th class="text-secondary text-uppercase fw-semibold" style="width: 180px; font-size: 0.72rem; letter-spacing: 0.05em;">Concluido por</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.05em;">Fecha Conclusión</th>
                <th class="text-center text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 0.72rem; letter-spacing: 0.05em;">Estado</th>
                <th class="text-end pe-3 pe-sm-4 text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 0.72rem; letter-spacing: 0.05em;">Acciones</th>
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
                $operador    = isset($trabajo['operador_nombre']) ? trim($trabajo['operador_nombre']) : 'Sistema';

                // Fecha de conclusión o actualización
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

                // Distintivo de Servicio
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

                // Distintivo de Estado
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
                <tr>
                    <td class="ps-3 ps-sm-4 text-muted font-monospace small">
                        <?= htmlspecialchars($idStr, ENT_QUOTES, 'UTF-8') ?>
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
                    <td>
                        <div class="text-dark small fw-medium">
                            <?= htmlspecialchars($operador, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    </td>
                    <td class="text-center text-muted font-monospace small">
                        <?= $fechaFormateada ?>
                    </td>
                    <td class="text-center">
                        <span class="badge border fw-normal py-1 px-2 <?= $estadoClass ?>">
                            <?= htmlspecialchars($estadoEtiqueta, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="text-end pe-3 pe-sm-4 text-nowrap">
                        <a href="/administrador/trabajos/detalle?tipo=<?= urlencode($tipoTrabajo) ?>&id=<?= urlencode($idStr) ?>&origen=historial" 
                           class="btn btn-sm btn-outline-primary px-2.5 py-1">
                            Ver detalle
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
