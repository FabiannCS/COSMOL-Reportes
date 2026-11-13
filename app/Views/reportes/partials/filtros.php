<?php
/**
 * Parcial: Formulario de Filtros de Búsqueda
 * Ubicación: app/Views/reportes/partials/filtros.php
 *
 * @var string $vista             Modo de vista activo ('socios' o 'acciones')
 * @var array  $filtros           Filtros aplicados (fecha_inicio, fecha_fin, id_tipo, buscar)
 * @var array  $tiposConsulta     Catálogo de tipos de consulta
 * @var bool   $hayFiltrosActivos Indicador de si hay filtros activos para botón Limpiar
 */
?>
<div class="card shadow-sm border rounded-3 mb-4" style="border-color: #e2e8f0;">
    <div class="card-body p-3">
        <form action="/reportes/visualizar" method="GET" class="row g-2 align-items-end m-0">
            <input type="hidden" name="vista" value="<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Fecha Inicio -->
            <div class="col-6 col-md-2">
                <label for="fecha_inicio" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Desde</label>
                <input type="date" 
                       class="form-control form-control-sm" 
                       id="fecha_inicio" 
                       name="fecha_inicio" 
                       value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <!-- Fecha Fin -->
            <div class="col-6 col-md-2">
                <label for="fecha_fin" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Hasta</label>
                <input type="date" 
                       class="form-control form-control-sm" 
                       id="fecha_fin" 
                       name="fecha_fin" 
                       value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <!-- Tipo de Consulta -->
            <div class="col-12 col-md-3">
                <label for="id_tipo" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Tipo de Consulta</label>
                <select class="form-select form-select-sm" id="id_tipo" name="id_tipo">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tiposConsulta as $tipo): ?>
                        <option value="<?= (int)$tipo['id_tipo'] ?>" <?= (!empty($filtros['id_tipo']) && (int)$filtros['id_tipo'] === (int)$tipo['id_tipo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tipo['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Búsqueda General -->
            <div class="col-12 col-md-3">
                <label for="buscar" class="form-label text-secondary small fw-semibold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Buscar</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="buscar" 
                       name="buscar" 
                       placeholder="Cód. socio, nombre o teléfono..."
                       value="<?= htmlspecialchars($filtros['buscar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <!-- Botones Filtrar y Limpiar -->
            <div class="col-12 col-md-2 d-flex gap-1.5">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    Filtrar
                </button>
                <?php if ($hayFiltrosActivos): ?>
                    <a href="/reportes/visualizar?vista=<?= htmlspecialchars($vista, ENT_QUOTES, 'UTF-8') ?>" 
                       class="btn btn-sm btn-outline-secondary px-2.5 flex-shrink-0" 
                       title="Restablecer filtros">
                        Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
