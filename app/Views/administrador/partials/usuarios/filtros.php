<?php
/**
 * Componente: Filtros Avanzados de Gestión de Personal
 * Archivo: app/Views/administrador/partials/usuarios_filtros.php
 * 
 * Permite filtrar por Rol, Especialidad técnica, Estado (Activo/Inactivo) y Búsqueda por nombre.
 * 
 * @var array $roles
 * @var array $especialidades
 * @var int|null $idRol
 * @var int|null $idEspecialidad
 * @var int|null $estadoFiltro
 * @var string $buscar
 * @var bool $hayFiltrosActivos
 */
?>
<div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
    <div class="card-body p-3">
        <form action="/administrador/usuarios" method="GET" class="m-0">
            <div class="row g-2 align-items-end">
                <!-- Filtro por Rol -->
                <div class="col-6 col-md-3">
                    <label for="filtro_rol" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Rol</label>
                    <select name="id_rol" id="filtro_rol" class="form-select form-select-sm">
                        <option value="">Todos los roles</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int)$r['id_rol'] ?>" <?= ($idRol === (int)$r['id_rol']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro por Especialidad -->
                <div class="col-6 col-md-3">
                    <label for="filtro_especialidad" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Especialidad</label>
                    <select name="id_especialidad" id="filtro_especialidad" class="form-select form-select-sm">
                        <option value="">Todas las especialidades</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= (int)$esp['id_especialidad'] ?>" <?= ($idEspecialidad === (int)$esp['id_especialidad']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro por Estado -->
                <div class="col-6 col-md-2">
                    <label for="filtro_estado" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Estado</label>
                    <select name="estado" id="filtro_estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="1" <?= ($estadoFiltro === 1) ? 'selected' : '' ?>>Activo</option>
                        <option value="0" <?= ($estadoFiltro === 0) ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <!-- Búsqueda por Usuario -->
                <div class="col-6 col-md-3">
                    <label for="filtro_buscar" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Usuario</label>
                    <input type="text" name="buscar" id="filtro_buscar" class="form-control form-control-sm" placeholder="Buscar por usuario..." value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <!-- Botones de Acción -->
                <div class="col-12 col-md-1 d-flex gap-1.5">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        Filtrar
                    </button>
                    <?php if ($hayFiltrosActivos): ?>
                        <a href="/administrador/usuarios" class="btn btn-sm btn-outline-secondary px-2 flex-shrink-0" title="Restablecer filtros">
                            Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
