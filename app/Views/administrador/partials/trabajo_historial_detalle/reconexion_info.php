<?php
/**
 * Parcial: Ficha de Información de Reconexión (Columna Izquierda)
 * Ruta: app/Views/administrador/trabajo_historial_detalle/reconexion_info.php
 *
 * @var array       $trabajo
 * @var string      $idReconexion
 * @var string      $fechaFormateada
 * @var string      $zonaRuta
 * @var string      $ubicacion
 * @var string      $direccion
 * @var string      $codSocio
 * @var string      $nombreSocio
 * @var string      $glosaRegistro
 * @var bool        $esGpsValido
 * @var string      $coordsGpsLimpia
 * @var string|null $foto
 * @var string|null $apiFotoBaseUrl
 */
?>

<div class="card bg-white rounded-2 shadow-sm" style="border: 1px solid #94a3b8 !important;">
    <!-- Encabezado de la Ficha: Orden, Fecha y Plazo (24h) -->
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3" style="border-color: #e2e8f0 !important;">
        <span class="text-secondary fw-semibold font-monospace" style="font-size: 0.85rem;">
            Orden #<?= htmlspecialchars($idReconexion, ENT_QUOTES, 'UTF-8') ?>
        </span>
        <div class="text-end d-flex flex-column align-items-end">
            <span class="text-secondary small font-monospace d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;" title="Fecha de llegada de la solicitud">
                <?= $fechaFormateada ?>
            </span>
        </div>
    </div>

    <div class="card-body p-3">
        <!-- 1. Identificación del Socio Titular -->
        <div class="pb-2">
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                Socio Solicitante
            </div>
            <div class="text-secondary fs-6 text-break">
                <?= htmlspecialchars($nombreSocio, ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($codSocio)): ?>
                    <span class="text-muted ms-1 font-monospace">(Cód: <?= htmlspecialchars($codSocio, ENT_QUOTES, 'UTF-8') ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

        <!-- 2. Tipo de Reconexión -->
        <div class="py-2">
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                Tipo de Reconexión
            </div>
            <div class="text-secondary fs-6">
                <?= htmlspecialchars(isset($tipoReconexionTexto) ? $tipoReconexionTexto : 'Corte normal', ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($tipoReconexionDesc)): ?>
                    <span class="text-muted ms-1">(<?= htmlspecialchars($tipoReconexionDesc, ENT_QUOTES, 'UTF-8') ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

        <!-- 3. Ubicación Proporcionada (Condicional: GPS vs Domicilio Registrado) -->
        <div class="py-2">
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                Ubicación
            </div>
            <div class="text-secondary fs-6 text-break">
                <?php if ($esGpsValido): ?>
                    Ubicación referencial enviada por el socio (ver mapa y coordenadas abajo)
                <?php else: ?>
                    <div>
                        <?= !empty($direccion) ? htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8') : '<span class="text-muted fst-italic">Dirección no especificada</span>' ?>
                        <span class="text-muted ms-1">(Domicilio registrado)</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 p-2 mt-2 rounded bg-light border" style="border-color: #e2e8f0 !important;">
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.88rem; letter-spacing: 0.02em;">
                            <i class="bi bi-geo-alt-fill text-primary me-1"></i><?= htmlspecialchars($zonaRuta, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <?php if (!empty($ubicacion)): ?>
                            <span class="text-secondary font-monospace small" style="font-size: 0.78rem;">
                                U-Z-R: <strong class="text-dark"><?= htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8') ?></strong>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

        <!-- 4. Descripción del Socio / Glosa -->
        <div class="py-2">
            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                Descripción del Socio
            </div>
            <div class="text-secondary fs-6 text-break">
                <?= !empty($glosaRegistro) ? htmlspecialchars($glosaRegistro, ENT_QUOTES, 'UTF-8') : 'Reconexión de servicio de agua potable' ?>
            </div>
        </div>

        <?php 
        $mostrarOperadorCard = isset($mostrarOperador) 
            ? $mostrarOperador 
            : (!empty($esAdmin) && !empty($operadorMostrar) && (!empty($esEstadoNoConcluido) || !empty($esFinalizadoEnApi)) && empty($esPendiente));
        ?>
        <?php if ($mostrarOperadorCard && !empty($operadorMostrar)): ?>
            <!-- 5. Operador que realizó el trabajo (Solo Administrador y para trabajos no concluidos/no procedentes o concluidos) -->
            <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">
            <div class="py-2">
                <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem; letter-spacing: 0.02em;">
                    Operador que realizó el trabajo
                </div>
                <div class="text-secondary fs-6">
                    <?= htmlspecialchars($operadorMostrar, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <hr class="my-2.5" style="border-top: 1px solid #e2e8f0; opacity: 1;">

        <!-- Sección de Ubicación: GPS vs Domicilio Propio -->
        <?php 
            $tipoTrabajo = 'reconexion';
            include __DIR__ . '/ubicacion_gps.php'; 
        ?>

        <!-- Fotografía Adjunta -->
        <?php 
            $fotoId = $idReconexion;
            $subcarpeta = 'reconexiones';
            $etiqueta = 'Fotografía de referencia';
            include __DIR__ . '/fotografia.php'; 
        ?>
    </div>
</div>
