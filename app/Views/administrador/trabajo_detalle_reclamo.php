<?php
/**
 * Vista: Detalle Supervisado de Reclamo (Administrador)
 * @var array $trabajo
 * @var string $apiFotoBaseUrl
 * @var string $error
 * Muestra la ficha completa de un reclamo en modo solo lectura.
 * Los datos ($trabajo, $apiFotoBaseUrl) son inyectados por AdministradorController::trabajoDetalle()
 */
?>

<div class="container-fluid p-0">
    <!-- Encabezado con navegación -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-exclamation-circle-fill text-info me-2"></i>Reclamo #<?= htmlspecialchars(isset($trabajo['id_reclamo']) ? $trabajo['id_reclamo'] : '—', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-muted mb-0">Ficha supervisada — Solo lectura</p>
        </div>
        <a href="/administrador/trabajos" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver a Supervisión
        </a>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($trabajo): ?>
    <div class="row g-4">
        <!-- Información del Reclamo -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información del Reclamo</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">Tipo de Reclamo</dt>
                        <dd class="col-sm-7">
                            <?php
                            $tipoReclamo = '—';
                            $tipoBadge   = 'secondary';
                            if (isset($trabajo['id_tipo_reclamo'])) {
                                if ((int)$trabajo['id_tipo_reclamo'] === 2) {
                                    $tipoReclamo = 'Agua Potable';
                                    $tipoBadge   = 'info';
                                } elseif ((int)$trabajo['id_tipo_reclamo'] === 3) {
                                    $tipoReclamo = 'Alcantarillado';
                                    $tipoBadge   = 'success';
                                } else {
                                    $tipoReclamo = 'Tipo ' . $trabajo['id_tipo_reclamo'];
                                }
                            }
                            ?>
                            <span class="badge bg-<?= $tipoBadge ?>"><?= htmlspecialchars($tipoReclamo, ENT_QUOTES, 'UTF-8') ?></span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Código de Socio</dt>
                        <dd class="col-sm-7 fw-bold"><?= htmlspecialchars(isset($trabajo['cod_socio']) ? $trabajo['cod_socio'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Nombre</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['nombre_socio']) ? $trabajo['nombre_socio'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Descripción</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['descripcion']) ? $trabajo['descripcion'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Glosa del Cliente</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['glosa']) ? $trabajo['glosa'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Ubicación y Coordenadas -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Ubicación</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">U-Z-R</dt>
                        <dd class="col-sm-7">
                            <?= htmlspecialchars(isset($trabajo['ubicacion']) ? $trabajo['ubicacion'] : '—', ENT_QUOTES, 'UTF-8') ?>
                            — Zona <?= htmlspecialchars(isset($trabajo['zona']) ? $trabajo['zona'] : '—', ENT_QUOTES, 'UTF-8') ?>
                            / Ruta <?= htmlspecialchars(isset($trabajo['ruta']) ? $trabajo['ruta'] : '—', ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-5 text-muted">Dirección</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['direccion']) ? $trabajo['direccion'] : '—', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Coordenadas GPS</dt>
                        <dd class="col-sm-7">
                            <?php if (!empty($trabajo['coordenadas_gps'])): ?>
                                <code><?= htmlspecialchars($trabajo['coordenadas_gps'], ENT_QUOTES, 'UTF-8') ?></code>
                                <a href="https://www.google.com/maps?q=<?= urlencode($trabajo['coordenadas_gps']) ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                    <i class="bi bi-map me-1"></i>Ver Mapa
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Sin coordenadas</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Fotografía -->
        <?php
        $foto = isset($trabajo['foto']) ? $trabajo['foto'] : '';
        $fotoUrl = '';
        if (!empty($foto)) {
            $fotoUrl = rtrim($apiFotoBaseUrl, '/') . '/' . ltrim($foto, '/');
        }
        ?>
        <?php if (!empty($fotoUrl)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Fotografía Adjunta</h6>
                </div>
                <div class="card-body text-center">
                    <img src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" 
                         alt="Foto del reclamo" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                    <div class="mt-2">
                        <a href="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrows-fullscreen me-1"></i>Ver tamaño completo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
