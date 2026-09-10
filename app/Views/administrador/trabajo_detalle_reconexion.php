<?php
/**
 * Vista: Detalle Supervisado de Reconexión (Administrador)
 * @var array $trabajo
 * @var string $apiFotoBaseUrl
 * @var string $error
 * Muestra la ficha completa de una reconexión en modo solo lectura.
 * Los datos ($trabajo, $apiFotoBaseUrl) son inyectados por AdministradorController::trabajoDetalle()
 */
?>

<div class="container-fluid p-0">
    <!-- Encabezado con navegación -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">
                Reconexión #<?= htmlspecialchars(isset($trabajo['id_reconexion']) ? $trabajo['id_reconexion'] : '—', ENT_QUOTES, 'UTF-8') ?>
            </h1>
            <p class="text-muted mb-0">Ficha supervisada — Solo lectura</p>
        </div>
        <a href="/administrador/trabajos" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($trabajo): ?>
    <div class="row g-4">
        <!-- Columna de Información -->
        <div class="col-lg-7 d-flex flex-column gap-4">
            <!-- Información del Socio -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 "><i class="bi bi-person-badge me-2"></i>Información del Socio</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">Código de Socio</dt>
                        <dd class="col-sm-7 fw-bold"><?= htmlspecialchars(isset($trabajo['cod_socio']) ? $trabajo['cod_socio'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Nombre</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['nombre_socio']) ? $trabajo['nombre_socio'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Descripción</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['descripcion']) ? $trabajo['descripcion'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>

                        <dt class="col-sm-5 text-muted">Glosa del Cliente</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['glosa']) ? $trabajo['glosa'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Ubicación y Coordenadas -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Ubicación</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">U-Z-R</dt>
                        <dd class="col-sm-7">
                            <?= htmlspecialchars(isset($trabajo['ubicacion']) ? $trabajo['ubicacion'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                            — Zona <?= htmlspecialchars(isset($trabajo['zona']) ? $trabajo['zona'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                            / Ruta <?= htmlspecialchars(isset($trabajo['ruta']) ? $trabajo['ruta'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-5 text-muted">Dirección</dt>
                        <dd class="col-sm-7"><?= htmlspecialchars(isset($trabajo['direccion']) ? $trabajo['direccion'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></dd>

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

            <!-- Fotografía -->
            <?php
            $foto = isset($trabajo['foto']) ? $trabajo['foto'] : '';
            $fotoUrl = '';
            if (!empty($foto)) {
                $fotoUrl = rtrim($apiFotoBaseUrl, '/') . '/' . ltrim($foto, '/');
            }
            ?>
            <?php if (!empty($fotoUrl)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Fotografía Adjunta</h6>
                </div>
                <div class="card-body text-center">
                    <img src="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" 
                         alt="Foto del trabajo" 
                         loading="lazy"
                         decoding="async"
                         class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                    <div class="mt-2">
                        <a href="<?= htmlspecialchars($fotoUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrows-fullscreen me-1"></i>Ver tamaño completo
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna de Conclusión -->
        <div class="col-lg-5">
            <?php 
                $estadoRaw = strtoupper(trim(isset($trabajo['estado']) ? (string)$trabajo['estado'] : ''));
                $estadoCalculado = isset($trabajo['estado_calculado']) 
                    ? $trabajo['estado_calculado'] 
                    : (function_exists('determinarEstadoTrabajo') ? determinarEstadoTrabajo($trabajo) : 'PENDIENTE');
                
                $tieneEstadoPendienteLocal = !empty($trabajo['estado_interno']);
                $esEstadoNoConcluido = ($estadoCalculado === 'NO CONCLUIDO' || $estadoCalculado === 'NO PROCEDENTE' || $tieneEstadoPendienteLocal);

                // Solo bloqueamos como solo-lectura si la API dice CONCLUIDO/CONCLUIDA Y NO es un estado NO CONCLUIDO / NO PROCEDENTE
                $esFinalizadoEnApi = ($estadoRaw === 'CONCLUIDA' || $estadoRaw === 'CONCLUIDO') && !$esEstadoNoConcluido;
            ?>
            
            <?php if ($esFinalizadoEnApi): ?>
                <div class="card shadow-sm h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Reconexión Concluida</h5>
                    </div>
                    <div class="card-body bg-light text-center d-flex flex-column justify-content-center align-items-center p-4">
                        <i class="bi bi-shield-check display-1 text-success mb-3"></i>
                        <h4 class="text-success mb-2">Trabajo Finalizado</h4>
                        <p class="text-muted mb-0">Esta reconexión ya fue procesada y guardada exitosamente.</p>
                    </div>
                </div>
            <?php else: ?>
            <div class="card shadow-sm h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check2-square me-2"></i>Concluir Trabajo
                        <?php if(!empty($trabajo['estado_interno'])): ?>
                            <span class="badge bg-warning ms-2"><?= htmlspecialchars($trabajo['estado_interno']) ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (hasPermission('trabajos.concluir')): ?>
                    <form action="/administrador/trabajos/concluir" method="POST" id="form-concluir">
                        <?= csrfField(); ?>
                        <input type="hidden" name="tipo" value="reconexion">
                        <input type="hidden" name="id_trabajo" value="<?= htmlspecialchars($trabajo['id_reconexion'] ?? '') ?>">
                        
                        <?php if(!empty($trabajo['glosa_interna'])): ?>
                            <div class="alert alert-warning mb-4">
                                <strong>Informe previo (<?= htmlspecialchars($trabajo['estado_interno']) ?>):</strong><br>
                                <?= nl2br(htmlspecialchars($trabajo['glosa_interna'])) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="estado" class="form-label fw-bold">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-select" required onchange="toggleReconexionFields()">
                                <option value="CONCLUIDO" selected>Concluido</option>
                                <option value="NO CONCLUIDO">No Concluido (Pendiente)</option>
                                <option value="NO PROCEDENTE">No Procedente</option>
                            </select>
                        </div>
                        
                        <div class="mb-4" id="div-lecturacion">
                            <label for="lecturacion" class="form-label fw-bold">Lecturación <span class="text-danger">*</span></label>
                            <input type="text" name="lecturacion" id="lecturacion" class="form-control" placeholder="Ingrese el valor de la lecturación..." value="<?= htmlspecialchars($trabajo['lectura_reconexion'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        
                        <script>
                            function toggleReconexionFields() {
                                const estado = document.getElementById('estado').value;
                                const lecturacion = document.getElementById('lecturacion');
                                const divLecturacion = document.getElementById('div-lecturacion');
                                
                                if (estado === 'CONCLUIDO') {
                                    divLecturacion.style.display = 'block';
                                    lecturacion.required = true;
                                } else {
                                    divLecturacion.style.display = 'none';
                                    lecturacion.required = false;
                                }
                            }
                        </script>

                        <div class="mb-4">
                            <label for="glosa" class="form-label fw-bold">Glosa / Observación <span class="text-danger">*</span></label>
                            <textarea name="glosa" id="glosa" class="form-control" rows="4" placeholder="Detalle qué trabajo se realizó o cualquier otra observación técnica..." required></textarea>
                            <div class="form-text">Si selecciona CONCLUIDO, esta conclusión actualizará el estado permanentemente en el servidor central de COSMOL.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-1"></i> Enviar Conclusión
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-shield-slash fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted fw-semibold mb-3">No cuenta con los permisos necesarios para concluir este trabajo.</p>
                        <button type="button" class="btn btn-secondary btn-lg disabled" disabled title="Permiso denegado">
                            <i class="bi bi-lock-fill me-1"></i> Conclusión Bloqueada
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
