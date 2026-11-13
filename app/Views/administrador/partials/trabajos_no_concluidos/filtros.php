<?php
/**
 * Componente: Barra de Filtros Segmentados y Buscador de Trabajos No Concluidos
 * Archivo: app/Views/administrador/partials/contingencias_filtros.php
 * 
 * Botones de segmentación por servicio (Reconexiones, Agua Potable, Alcantarillado) y buscador.
 * 
 * @var array $trabajos
 * @var int $cntReconexiones
 * @var int $cntAguaPotable
 * @var int $cntAlcantarilla
 * @var string $buscar
 */
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
    <!-- Control segmentado COSMOL para alternar tipos de trabajo -->
    <div class="p-1 rounded-3 border d-inline-flex flex-wrap gap-1 w-100 w-md-auto" id="filtroTipoContingencias" style="background-color: #f1f5f9; border-color: #e2e8f0 !important;" role="group" aria-label="Filtrar por servicio">
        <button type="button" class="btn btn-sm filtro-contingencia-btn active" data-target="todos">
            Todos <span class="badge rounded-pill ms-1 font-monospace filtro-badge"><?= count($trabajos) ?></span>
        </button>
        <button type="button" class="btn btn-sm filtro-contingencia-btn" data-target="reconexion">
            Reconexiones <span class="badge rounded-pill ms-1 font-monospace filtro-badge"><?= $cntReconexiones ?></span>
        </button>
        <button type="button" class="btn btn-sm filtro-contingencia-btn" data-target="agua">
            Agua Potable <span class="badge rounded-pill ms-1 font-monospace filtro-badge"><?= $cntAguaPotable ?></span>
        </button>
        <button type="button" class="btn btn-sm filtro-contingencia-btn" data-target="alcantarillado">
            Alcantarillado <span class="badge rounded-pill ms-1 font-monospace filtro-badge"><?= $cntAlcantarilla ?></span>
        </button>
    </div>

    <!-- Buscador Global de Base de Datos -->
    <form action="/administrador/trabajos-no-concluidos" method="GET" class="d-flex w-100 w-md-auto m-0 gap-2">
        <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar socio, código o estado..." value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>" style="min-width: 220px;">
        <button type="submit" class="btn btn-sm btn-primary px-3">Buscar</button>
        <?php if (!empty($buscar)): ?>
            <a href="/administrador/trabajos-no-concluidos" class="btn btn-sm btn-outline-secondary px-2.5 flex-shrink-0" title="Limpiar filtro">
                Limpiar
            </a>
        <?php endif; ?>
    </form>
</div>
