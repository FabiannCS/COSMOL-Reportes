# Fase 8: Módulo de Reportes de Consultas del Chatbot (Completado)

> **Estado:** Completado ✅  
> **Responsabilidad:** Centralización, visualización estructurada, filtrado dinámico y exportación a formato de hoja de cálculo (CSV) de las consultas realizadas por los socios al chatbot de COSMOL.

---

## 1. Arquitectura y Capa de Datos (Model)

- **Modelo `app/Models/Reporte.php`:**
  - Extiende de `App\Core\Model` y utiliza consultas preparadas con PDO (`pdo_pgsql`) para garantizar seguridad frente a inyecciones SQL.
  - Implementa:
    - `getTiposConsulta()`: Obtiene el catálogo ordenado de tipos de consulta para los filtros.
    - `getConsultasPaginadas($filtros, $limit, $offset)`: Ejecuta la consulta combinando `consulta`, `tipo_consulta` y `usuario` mediante `LEFT JOIN`, aplicando filtros dinámicos (fecha inicio, fecha fin, tipo) y orden descendente.
    - `getTotalConsultas($filtros)`: Realiza el conteo total para el cálculo de páginas.
    - `getAllConsultasExport($filtros)`: Extrae el universo completo de datos filtrados para la exportación sin paginación.
- **Datos de Prueba (`database/seed_reportes.sql`):** Semilla aplicada con 3 tipos de consulta y 4 registros históricos de atención.

---

## 2. Lógica de Negocio y Enrutamiento (Controller & Routes)

- **Controlador `app/Controllers/ReporteController.php`:**
  - `visualizar()`: Procesa parámetros `GET`, calcula la paginación (15 elementos por página) y prepara los datos para la interfaz.
  - `exportar()`: Genera una descarga en tiempo real vía stream (`php://output`) con codificación UTF-8 y marca BOM (`\xEF\xBB\xBF`) para compatibilidad perfecta con Microsoft Excel y hojas de cálculo.
- **Enrutamiento RBAC en `app/Config/routes.php`:**
  ```php
  '/reportes/visualizar' => ['ReporteController', 'visualizar', ['auth', 'role:Administrador,Supervisor']],
  '/reportes/exportar'   => ['ReporteController', 'exportar',   ['auth', 'role:Administrador,Supervisor']],
  ```
- **Control de Acceso (`RoleMiddleware.php`):**
  - Si un usuario con rol `Operador` intenta ingresar a `/reportes/*`, es redirigido automáticamente a su área de trabajo (`/operador/trabajos`).
  - Usuarios no autenticados son redirigidos a `/login`.

---

## 3. Interfaz de Usuario y Navegación (View)

- **Vista `app/Views/reportes/visualizar.php`:**
  - **Cabecera:** Título, descripción y botón directo a *Exportar a CSV* que arrastra los filtros activos.
  - **Filtros Combinados:** Tarjeta interactiva con campos para rango de fechas (`fecha_inicio`, `fecha_fin`) y selector dinámico de tipos (`id_tipo`). Botones de *Filtrar* y *Limpiar*.
  - **Tabla Historial:** Vista estilizada con Bootstrap 5.3, badges por tipo de consulta, visualización clara de fecha/hora con iconos, y badge distintivo para el usuario o chatbot.
  - **Paginación Inteligente:** Preserva automáticamente los filtros actuales en la URL al cambiar de página.
- **Menú Lateral (`sidebar.php`):** Enlace visible y funcional tanto para `Administrador` como para `Supervisor` con detección de ruta activa.

---

## 4. Resultados de Pruebas de Validación (Paso 5)

| Caso de Prueba | Entrada / Contexto | Resultado Esperado | Resultado Obtenido | Estado |
|---|---|---|---|:---:|
| **Sintaxis PHP 7.3** | `php -l` en Model, Controller, View, Middleware | 0 errores de sintaxis | `No syntax errors detected` | ✅ Superado |
| **RBAC Administrador** | Sesión `admin` a `/reportes/visualizar` | HTTP 200 (Acceso concedido) | HTTP 200 | ✅ Superado |
| **RBAC Supervisor** | Sesión `Daniel` a `/reportes/visualizar` | HTTP 200 (Acceso concedido) | HTTP 200 | ✅ Superado |
| **RBAC Operador** | Sesión `carlos` a `/reportes/visualizar` | HTTP 302 a `/operador/trabajos` | 302 Redirigido a `/operador/trabajos` | ✅ Superado |
| **Sin Autenticación** | Petición anónima a `/reportes/visualizar` | HTTP 302 a `/login` | 302 Redirigido a `/login` | ✅ Superado |
| **Filtro por Tipo** | `id_tipo = 2` | Solo consultas de tipo 2 | 1 registro obtenido | ✅ Superado |
| **Filtro por Rango Fechas**| `2023-10-03` a `2023-10-03` | Solo consultas del día 3 | 2 registros obtenidos | ✅ Superado |
| **Filtro Combinado** | Fechas + `id_tipo = 1` | Intersección (AND) | 1 registro obtenido | ✅ Superado |
| **Exportación CSV Base**| `/reportes/exportar` | Descarga de archivo CSV con BOM | Archivo generado con 4 registros | ✅ Superado |
| **Exportación CSV Filtrada**| `/reportes/exportar?id_tipo=2` | Descarga solo registros filtrados | Archivo con 1 registro | ✅ Superado |
