<?php
/**
 * Vista Parcial para renderizar una tarjeta de trabajo (Reclamo o Reconexión)
 *
 * @var array $t     Datos del trabajo
 * @var string $tipo 'reclamo' o 'reconexion'
 * @var bool $is_historial (Opcional) Indica si se renderiza en el historial
 */

$is_historial = $is_historial ?? false;
$id_trabajo = $t['id_reclamo'] ?? $t['id_reconexion'] ?? 'N/A';
$estado = function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($t) : strtoupper(trim($t['estado'] ?? 'PENDIENTE'));

$badgeClass = 'bg-warning text-dark';
$borderClass = 'border-warning';
if ($estado === 'CONCLUIDA' || $estado === 'CONCLUIDO') {
    $badgeClass = 'bg-success text-white';
    $borderClass = 'border-success';
} elseif ($estado === 'NO CONCLUIDO') {
    $badgeClass = 'bg-danger text-white';
    $borderClass = 'border-danger';
} elseif ($estado === 'NO PROCEDENTE' || $estado === 'ANULADO' || $estado === 'CANCELADO') {
    $badgeClass = 'bg-secondary text-white';
    $borderClass = 'border-secondary';
}

$descripcion = $t['descripcion'] ?? ($tipo === 'reconexion' ? 'Reconexión de servicio' : 'Sin descripción');
$glosa = $t['glosa'] ?? '';

if ($is_historial) {
    $texto_boton = 'Ver Detalle';
    $btnClass = 'btn-outline-secondary';
} else {
    $texto_boton = ($tipo === 'reconexion') ? 'Reconectar' : 'Detalle';
    $btnClass = 'btn-outline-primary';
}

$socio = $t['nombre_socio'] ?? $t['nombre'] ?? '';
?>
<div class="col">
    <!-- Se usa 'border' para el borde sutil por defecto, y se puede añadir <?= $borderClass ?> si se quiere el borde de color del estado -->
    <div class="card h-100 shadow-sm border task-card <?= $is_historial ? 'bg-light' : '' ?>" style="cursor: pointer;" onclick="window.location='/operador/detalle?id=<?= urlencode($id_trabajo) ?>'">
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <span class="badge bg-light text-dark border me-1">#<?= htmlspecialchars($id_trabajo) ?></span>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($estado) ?></span>
                </div>
                <div class="small text-muted fw-medium">
                    <?php
                        $fechaStr = $is_historial ? ($t['fecha_actualizacion'] ?? $t['fecha_registro'] ?? '') : ($t['fecha_registro'] ?? '');
                        if ($fechaStr) {
                            $fecha = date_create($fechaStr);
                            echo $fecha ? date_format($fecha, 'd/m/Y H:i') : htmlspecialchars($fechaStr);
                        } else {
                            echo '—';
                        }
                    ?>
                </div>
            </div>

            <?php if (!empty($socio)): ?>
                <h6 class="card-title mb-1 fw-bold text-truncate" title="<?= htmlspecialchars(trim($socio)) ?>">
                    <i class="bi bi-person-fill text-secondary me-1"></i><?= htmlspecialchars(trim($socio)) ?>
                </h6>
                <?php if (!empty($t['cod_socio'])): ?>
                    <div class="text-muted small mb-3"><span class="fw-semibold">Cód. Socio:</span> <?= htmlspecialchars($t['cod_socio']) ?></div>
                <?php else: ?>
                    <div class="mb-3"></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="small text-break flex-grow-1 border-top pt-3">
                <div class="mb-1 text-secondary"><strong class="text-dark">Descripción:</strong> <?= htmlspecialchars($descripcion) ?></div>
                <?php if ($is_historial && !empty($glosa)): ?>
                    <div class="mt-2 bg-light p-2 rounded border-start border-3 border-info">
                        <span class="fw-bold text-info-emphasis d-block mb-1" style="font-size: 0.75rem;"><i class="bi bi-chat-text-fill me-1"></i>CONCLUSIÓN</span>
                        <div class="fst-italic text-dark"><?= htmlspecialchars($glosa) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer <?= $is_historial ? 'bg-light' : 'bg-white' ?> border-top border-light pt-3 pb-3 d-flex justify-content-between align-items-center">
            <div>
                <?php if (!empty($t['foto'])): ?>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1">
                        <i class="bi bi-camera-fill me-1"></i>Foto adjunta
                    </span>
                <?php endif; ?>
            </div>
            <a href="/operador/detalle?id=<?= urlencode($id_trabajo) ?>" class="btn btn-sm <?= $btnClass ?> rounded-pill px-3 fw-semibold" onclick="event.stopPropagation();">
                <?= htmlspecialchars($texto_boton) ?> <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>
