# Guía de Implementación: Módulo de Reportes de la App de Socios en COSMOL-Reportes

> **Proyecto Destino:** `COSMOL-Reportes` (Sistema Web de Auditoría y Reportes de COSMOL R.L.)  
> **Ubicación del Documento:** `Docs/fabian/pendiente/GUIA_VISTA_APP_SOCIOS_COSMOL_REPORTES.md`  
> **Fecha de Actualización:** Septiembre 2026 (Versión 1.1 - Revisada y Corregida)  
> **Objetivo:** Guía técnica paso a paso para implementar la vista exclusiva **"App de Socios"** en la sección de **Reportes** de `COSMOL-Reportes`, visualizando en tiempo real los movimientos, logins, consultas de deuda, descargas PDF y pagos generados desde la aplicación móvil.

---

> [!IMPORTANT]
> ### PRINCIPIO DE ARQUITECTURA: CERO CAMBIOS ESTRUCTURALES EN BASE DE DATOS
> * **No se agregan nuevas columnas (Cero `ALTER TABLE`):** Las tablas existentes `consulta`, `usuario` y `tipo_consulta` ya contienen todas las columnas requeridas (`id_usuario`, `tipo_ubicacion`, `codigo_socio`, `id_tipo`, etc.).
> * **Integridad Referencial Garantizada:** Para que la base de datos PostgreSQL no rechace las inserciones por violación de clave foránea (`FOREIGN KEY`), se registran usuarios de sistema en la tabla `usuario` (`id_usuario = 2` para Chatbot, `id_usuario = 3` para App Móvil) y los nuevos tipos de eventos en `tipo_consulta` (`9` y `10`).

---

## 1. Visión General de la Integración

```
┌─────────────────────────────────────────────────────────┐
│                 App de Socios (COSMOL-app)              │
│       [Login, Deuda, Facturas, Descargas PDF, Pagos]    │
└────────────────────────────┬────────────────────────────┘
                             │
                             │ HTTP POST /api/consultas (Background)
                             │ Body JSON: { "id_usuario": 3, "tipo_ubicacion": "APP_MOVIL", ... }
                             │ Header: X-Reportes-Token: {TOKEN}
                             ▼
┌─────────────────────────────────────────────────────────┐
│                 COSMOL-Reportes (:8080)                 │
│  • Backend: ConsultaApiController::registrar()          │
│    Guarda el evento con id_usuario = 3 en tabla consulta│
│  • Frontend Web: Nueva vista en /reportes/app-socios    │
│    (Tarjetas KPI, Filtros interactivos y Tabla paginada)│
└─────────────────────────────────────────────────────────┘
```

---

# PARTE 1: BASE DE DATOS Y DATOS SEMILLA

Para que PostgreSQL acepte las inserciones sin violaciones de clave foránea (`FK`), se ejecutan las siguientes inserciones en la base de datos `cosmol_reportes` (también se incluyen en `database/init.sql` para nuevos despliegues):

```sql
-- 1. Insertar tipos de consulta exclusivos de la App Móvil (id_tipo 9 y 10)
INSERT INTO tipo_consulta (id_tipo, nombre, descripcion) VALUES
(9, 'Descarga de Factura PDF', 'Descarga o visualización de aviso de factura en formato PDF desde la app móvil'),
(10, 'Intento de Pago', 'Redirección o generación de enlace hacia pasarela de pagos desde la app móvil')
ON CONFLICT (id_tipo) DO NOTHING;

-- Sincronizar la secuencia del autoincremental
SELECT setval('tipo_consulta_id_tipo_seq', COALESCE((SELECT MAX(id_tipo) FROM tipo_consulta), 1));

-- 2. Insertar usuarios de sistema para satisfacer la FK consulta_id_usuario_fkey
-- id_rol = 2 corresponde al rol Supervisor/Sistema
INSERT INTO usuario (id_usuario, username, password_hash, id_rol, estado) VALUES
(2, 'chatbot_whatsapp', 'SISTEMA_NO_LOGIN', 2, 1),
(3, 'app_movil', 'SISTEMA_NO_LOGIN', 2, 1)
ON CONFLICT (id_usuario) DO NOTHING;

-- Sincronizar la secuencia de usuarios
SELECT setval('usuario_id_usuario_seq', COALESCE((SELECT MAX(id_usuario) FROM usuario), 1));
```

---

# PARTE 2: CAMBIOS EN EL BACKEND

---

### 2.1 Controlador Receptor de Eventos (`app/Controllers/ConsultaApiController.php`)

En el método `registrar()` de `ConsultaApiController.php`:
1. Se extrae `id_usuario` enviado en el body (o se asigna `3` si `tipo_ubicacion === 'APP_MOVIL'`, o `2` por defecto para Chatbot).
2. Se incluye `id_usuario` en la sentencia `INSERT INTO consulta`:

```php
// En app/Controllers/ConsultaApiController.php -> método registrar()

$tipoUbicacion = isset($input['tipo_ubicacion']) && !empty($input['tipo_ubicacion']) ? trim((string)$input['tipo_ubicacion']) : null;

// Determinar el canal de usuario (3 = App Móvil, 2 = Chatbot)
$idUsuario = isset($input['id_usuario']) && !empty($input['id_usuario']) 
    ? (int)$input['id_usuario'] 
    : (($tipoUbicacion === 'APP_MOVIL') ? 3 : 2);

// Inserción en la tabla consulta persistiendo id_usuario
$stmt = $db->prepare("
    INSERT INTO consulta (codigo_socio, nombres, telefono, tipo_ubicacion, fecha_consulta, hora_consulta, id_tipo, id_usuario)
    VALUES (:codigo_socio, :nombres, :telefono, :tipo_ubicacion, :fecha_consulta, :hora_consulta, :id_tipo, :id_usuario)
");

$stmt->execute([
    ':codigo_socio'   => $codigoSocio,
    ':nombres'        => $nombres,
    ':telefono'       => $telefono,
    ':tipo_ubicacion' => $tipoUbicacion,
    ':fecha_consulta' => $fecha,
    ':hora_consulta'  => $hora,
    ':id_tipo'        => $idTipo,
    ':id_usuario'     => $idUsuario
]);
```

---

### 2.2 Consultas y Lógica de Datos (`app/Models/Reporte.php`)

En `app/Models/Reporte.php` se incorporan los métodos de consulta para la App de Socios, indexando los totales asociativamente por `id_tipo` para evitar errores de índice en la vista:

```php
/**
 * Totales para Tarjetas KPI indexados asociativamente por id_tipo.
 *
 * @param array $filtros
 * @return array Mapa [id_tipo => total]
 */
public function getTotalesPorTipoConsultaApp($filtros = [])
{
    $sql = "SELECT 
                t.id_tipo,
                COUNT(c.id_consulta) as total
            FROM tipo_consulta t
            LEFT JOIN consulta c ON t.id_tipo = c.id_tipo 
                 AND (c.id_usuario = 3 OR c.tipo_ubicacion = 'APP_MOVIL')";

    $params = [];
    $conditions = [];

    if (!empty($filtros['fecha_inicio'])) {
        $conditions[] = "c.fecha_consulta >= :fecha_inicio";
        $params[':fecha_inicio'] = $filtros['fecha_inicio'];
    }

    if (!empty($filtros['fecha_fin'])) {
        $conditions[] = "c.fecha_consulta <= :fecha_fin";
        $params[':fecha_fin'] = $filtros['fecha_fin'];
    }

    if (!empty($filtros['buscar'])) {
        $conditions[] = "(c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
        $params[':buscar'] = '%' . $filtros['buscar'] . '%';
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " GROUP BY t.id_tipo ORDER BY t.id_tipo ASC";

    $stmt = $this->db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapeo asociativo [id_tipo => total] para lectura directa y segura
    $mapaTotales = [];
    foreach ($rows as $row) {
        $mapaTotales[(int)$row['id_tipo']] = (int)$row['total'];
    }

    return $mapaTotales;
}

/**
 * Listado paginado de eventos de la App de Socios.
 */
public function getConsultasAppPaginadas($filtros, $limit, $offset)
{
    $sql = "SELECT c.id_consulta, c.codigo_socio, c.nombres, c.telefono, c.tipo_ubicacion, 
                   c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username
            FROM consulta c
            LEFT JOIN tipo_consulta t ON c.id_tipo = t.id_tipo
            LEFT JOIN usuario u ON c.id_usuario = u.id_usuario
            WHERE (c.id_usuario = 3 OR c.tipo_ubicacion = 'APP_MOVIL')";

    $params = [];

    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND c.fecha_consulta >= :fecha_inicio";
        $params[':fecha_inicio'] = $filtros['fecha_inicio'];
    }

    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND c.fecha_consulta <= :fecha_fin";
        $params[':fecha_fin'] = $filtros['fecha_fin'];
    }

    if (!empty($filtros['id_tipo'])) {
        $sql .= " AND c.id_tipo = :id_tipo";
        $params[':id_tipo'] = $filtros['id_tipo'];
    }

    if (!empty($filtros['buscar'])) {
        $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
        $params[':buscar'] = '%' . $filtros['buscar'] . '%';
    }

    $sql .= " ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC, c.id_consulta DESC";
    $sql .= " LIMIT :limit OFFSET :offset";

    $stmt = $this->db()->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Total de registros filtrados para la paginación.
 */
public function getTotalConsultasApp($filtros)
{
    $sql = "SELECT COUNT(c.id_consulta) as total
            FROM consulta c
            WHERE (c.id_usuario = 3 OR c.tipo_ubicacion = 'APP_MOVIL')";

    $params = [];
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND c.fecha_consulta >= :fecha_inicio";
        $params[':fecha_inicio'] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND c.fecha_consulta <= :fecha_fin";
        $params[':fecha_fin'] = $filtros['fecha_fin'];
    }
    if (!empty($filtros['id_tipo'])) {
        $sql .= " AND c.id_tipo = :id_tipo";
        $params[':id_tipo'] = $filtros['id_tipo'];
    }
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (c.codigo_socio::text ILIKE :buscar OR c.nombres ILIKE :buscar OR c.telefono ILIKE :buscar)";
        $params[':buscar'] = '%' . $filtros['buscar'] . '%';
    }

    $stmt = $this->db()->prepare($sql);
    $stmt->execute($params);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($res['total'] ?? 0);
}
```

> **Nota de Aislamiento:** En el método existente `getConsultasPaginadas($filtros)` (usado por el Chatbot), se añade la condición defensiva `AND (c.id_usuario IS NULL OR c.id_usuario != 3) AND (c.tipo_ubicacion IS NULL OR c.tipo_ubicacion != 'APP_MOVIL')` para que los eventos de la app no se mezclen en la pantalla de WhatsApp.

---

### 2.3 Controlador Web (`app/Controllers/ReporteController.php`)

Se agrega el método `visualizarAppSocios()` utilizando el método correcto del framework base: **`$this->view(...)`**:

```php
public function visualizarAppSocios()
{
    $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
    $fechaFin    = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
    $idTipo      = isset($_GET['id_tipo']) && $_GET['id_tipo'] !== '' ? (int)$_GET['id_tipo'] : null;
    $buscar      = isset($_GET['buscar']) && $_GET['buscar'] !== '' ? trim($_GET['buscar']) : null;

    $filtros = [
        'fecha_inicio' => $fechaInicio,
        'fecha_fin'    => $fechaFin,
        'id_tipo'      => $idTipo,
        'buscar'       => $buscar,
    ];

    $pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    if ($pagina < 1) {
        $pagina = 1;
    }
    $limit = 15;
    $offset = ($pagina - 1) * $limit;

    $tiposConsulta   = $this->reporteModel->getTiposConsulta();
    $totalesPorTipo  = $this->reporteModel->getTotalesPorTipoConsultaApp($filtros);
    $totalRegistros  = $this->reporteModel->getTotalConsultasApp($filtros);
    $totalPaginas    = (int)ceil($totalRegistros / $limit);
    if ($totalPaginas < 1) {
        $totalPaginas = 1;
    }

    $consultas = $this->reporteModel->getConsultasAppPaginadas($filtros, $limit, $offset);

    // Invocación correcta con $this->view() dentro del layout principal
    $this->view('reportes/app_socios', [
        'titulo'         => 'Movimientos de la App de Socios',
        'consultas'      => $consultas,
        'tiposConsulta'  => $tiposConsulta,
        'totalesPorTipo' => $totalesPorTipo,
        'filtros'        => $filtros,
        'pagina'         => $pagina,
        'totalPaginas'   => $totalPaginas,
        'totalRegistros' => $totalRegistros,
        'limit'          => $limit
    ]);
}
```

---

### 2.4 Registro de Rutas (`app/Config/routes.php`)

Se registra en el bloque `'GET'` con la autenticación y permisos de seguridad:

```php
// En app/Config/routes.php -> sección 'GET':
'/reportes/app-socios' => ['ReporteController', 'visualizarAppSocios', ['auth', 'permission:reportes.ver']],
```

---

# PARTE 3: CAMBIOS EN EL FRONTEND

---

### 3.1 Menú Lateral (`app/Views/layouts/partials/sidebar.php`)

Se incorpora la opción **"App de Socios"** con icono móvil (`bi-phone`):

```php
<!-- Módulo Reportes (Visible para Administrador y Supervisor) -->
<?php if ($rolActual !== 'Operador' && $hasPermission('reportes.ver')): ?>
    <li class="sidebar-section-title">Reportes</li>

    <!-- 1. Reporte de Consultas Chatbot (WhatsApp) -->
    <li class="nav-item">
        <a class="nav-link <?= ($currentUri === '/reportes/visualizar') ? 'active' : '' ?>" href="/reportes/visualizar" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Consultas Chatbot">
            <i class="bi bi-whatsapp"></i>
            <span style="color: #f8fafc;">Consultas Chatbot</span>
        </a>
    </li>

    <!-- 2. Reporte de Movimientos App de Socios -->
    <li class="nav-item">
        <a class="nav-link <?= ($currentUri === '/reportes/app-socios') ? 'active' : '' ?>" href="/reportes/app-socios" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="App de Socios">
            <i class="bi bi-phone"></i>
            <span style="color: #f8fafc;">App de Socios</span>
        </a>
    </li>
<?php endif; ?>
```

---

### 3.2 Pantalla Visual del Reporte (`app/Views/reportes/app_socios.php`)

Vista completa con Bootstrap 5.3, tarjetas KPI de lectura asociativa, filtros y **paginación funcional**:

```html
<?php
$queryParams = [];
if (!empty($filtros['fecha_inicio'])) $queryParams['fecha_inicio'] = $filtros['fecha_inicio'];
if (!empty($filtros['fecha_fin']))    $queryParams['fecha_fin']    = $filtros['fecha_fin'];
if (!empty($filtros['id_tipo']))      $queryParams['id_tipo']      = $filtros['id_tipo'];
if (!empty($filtros['buscar']))       $queryParams['buscar']       = $filtros['buscar'];

$pageUrl = function ($pageNum) use ($queryParams) {
    $p = array_merge($queryParams, ['p' => $pageNum]);
    return '/reportes/app-socios?' . http_build_query($p);
};

$desde = $totalRegistros > 0 ? (($pagina - 1) * $limit) + 1 : 0;
$hasta = min($pagina * $limit, $totalRegistros);
?>

<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="bi bi-phone text-primary me-2"></i>Reporte de Movimientos — App de Socios
            </h4>
            <p class="text-muted small mb-0">Auditoría en tiempo real de consultas, descargas PDF y pagos desde la aplicación móvil</p>
        </div>
        <span class="badge bg-primary px-3 py-2 fs-7">
            <i class="bi bi-check-circle me-1"></i> Canal: App Móvil (id_usuario = 3)
        </span>
    </div>

    <!-- Tarjetas KPI Resumen -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4 rounded-3 h-100">
                <span class="text-muted small fw-semibold">Accesos y Logins</span>
                <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format($totalesPorTipo[1] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4 rounded-3 h-100">
                <span class="text-muted small fw-semibold">Consultas de Deuda</span>
                <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format($totalesPorTipo[2] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-warning border-4 rounded-3 h-100">
                <span class="text-muted small fw-semibold">Historial de Facturas</span>
                <h3 class="fw-bold mb-0 text-warning mt-1"><?= number_format($totalesPorTipo[3] ?? 0) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 border-start border-success border-4 rounded-3 h-100">
                <span class="text-muted small fw-semibold">Descargas PDF y Pagos</span>
                <h3 class="fw-bold mb-0 text-success mt-1">
                    <?= number_format(($totalesPorTipo[9] ?? 0) + ($totalesPorTipo[10] ?? 0)) ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card border-0 shadow-sm mb-4 rounded-3">
        <div class="card-body p-3">
            <form method="GET" action="/reportes/app-socios" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Fecha Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Fecha Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Tipo de Movimiento</label>
                    <select name="id_tipo" class="form-select form-select-sm">
                        <option value="">-- Todos los Movimientos --</option>
                        <option value="1" <?= ($filtros['id_tipo'] == 1) ? 'selected' : '' ?>>Autenticación / Acceso</option>
                        <option value="2" <?= ($filtros['id_tipo'] == 2) ? 'selected' : '' ?>>Consulta de Deuda</option>
                        <option value="3" <?= ($filtros['id_tipo'] == 3) ? 'selected' : '' ?>>Historial de Facturas</option>
                        <option value="9" <?= ($filtros['id_tipo'] == 9) ? 'selected' : '' ?>>Descarga de Factura PDF</option>
                        <option value="10" <?= ($filtros['id_tipo'] == 10) ? 'selected' : '' ?>>Intento de Pago</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-funnel me-1"></i>Filtrar
                    </button>
                    <a href="/reportes/app-socios" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Resultados -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"># ID</th>
                        <th>Cód. Socio</th>
                        <th>Nombre Titular</th>
                        <th>Teléfono</th>
                        <th>Acción Realizada</th>
                        <th>Fecha y Hora</th>
                        <th class="text-center">Canal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consultas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No se encontraron movimientos registrados para la App de Socios.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($consultas as $item): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-muted"><?= $item['id_consulta'] ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $item['codigo_socio'] ?></span></td>
                                <td class="fw-semibold"><?= htmlspecialchars($item['nombres'] ?? 'No disponible') ?></td>
                                <td><?= htmlspecialchars($item['telefono'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <?= htmlspecialchars($item['tipo'] ?? 'Consulta General') ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= $item['fecha_consulta'] ?> <?= $item['hora_consulta'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-2">APP_MOVIL</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="card-footer bg-white border-0 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <span class="small text-muted">
                    Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= number_format($totalRegistros) ?></strong> movimientos
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($pagina <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl($pagina - 1) ?>">Anterior</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= ($i === $pagina) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($pagina >= $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl($pagina + 1) ?>">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
```

---

# PARTE 4: PROCEDIMIENTO DE PRUEBA PRE-FINAL CON NGROK

Para validar el enlace entre la App Móvil local y `COSMOL-Reportes`:

1. **Levantar el túnel en la máquina donde corre `COSMOL-Reportes`:**
   ```bash
   ngrok http 8080
   ```
2. **Enviar petición de prueba sintética desde la máquina de desarrollo de la App:**
   ```bash
   curl -X POST "https://{TUNEL_NGROK}.ngrok-free.app/api/consultas" \
        -H "Content-Type: application/json" \
        -H "X-Reportes-Token: {TOKEN_EN_ENV}" \
        -d '{
          "codigo_socio": 23807,
          "nombres": "PRUEBA PRE-FINAL NGROK",
          "telefono": "+59170000000",
          "id_usuario": 3,
          "id_tipo": 1,
          "tipo_consulta": "Autenticación / Acceso",
          "tipo_ubicacion": "APP_MOVIL"
        }'
   ```
3. **Verificar respuesta y UI:**
   * La respuesta debe ser `HTTP 201: {"status":"success","message":"Consulta registrada"}`.
   * Al refrescar `/reportes/app-socios`, el registro debe aparecer en la primera fila de la tabla y sumar `+1` a la tarjeta de **Accesos y Logins**.
