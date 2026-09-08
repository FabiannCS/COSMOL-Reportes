<?php
/**
 * @var string $title
 * @var array $usuario
 * @var string|null $especialidad
 */
?>

<div class="row justify-content-center mt-3">
    <div class="col-12 col-md-8 col-lg-6">
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-circle me-2 text-secondary"></i> Mi Perfil
                </h5>
                <?php if (isset($usuario['estado']) && $usuario['estado'] == 1): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">Activo</span>
                <?php else: ?>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1">Inactivo</span>
                <?php endif; ?>
            </div>
            
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">Nombre de Usuario</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($usuario['username']) ?></span>
                    </li>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">Rol del Sistema</span>
                        <span class="text-dark"><?= htmlspecialchars($usuario['nombre_rol'] ?? 'Sin rol') ?></span>
                    </li>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">ID de Cuenta</span>
                        <span class="text-dark"><?= htmlspecialchars($usuario['id_usuario']) ?></span>
                    </li>
                    
                    <?php if ($especialidad): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">Especialidad Operativa</span>
                        <span class="text-dark"><?= htmlspecialchars($especialidad) ?></span>
                    </li>
                    <?php endif; ?>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">Fecha de Registro</span>
                        <span class="text-dark">
                            <?php 
                                if (!empty($usuario['fecha_creacion'])) {
                                    $fecha = date_create($usuario['fecha_creacion']);
                                    echo $fecha ? date_format($fecha, 'd/m/Y') : '-';
                                } else {
                                    echo '-';
                                }
                            ?>
                        </span>
                    </li>
                    
                    <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <span class="text-muted fw-semibold">Última actualización</span>
                        <span class="text-dark">
                            <?php 
                                if (!empty($usuario['fecha_actualizacion'])) {
                                    $fecha = date_create($usuario['fecha_actualizacion']);
                                    echo $fecha ? date_format($fecha, 'd/m/Y H:i') : '-';
                                } else {
                                    echo '-';
                                }
                            ?>
                        </span>
                    </li>
                </ul>
            </div>
            
            <div class="card-footer bg-white border-top text-end py-3">
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
        
    </div>
</div>
