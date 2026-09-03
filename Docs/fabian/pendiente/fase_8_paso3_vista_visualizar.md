# Fase 8 — Paso 3: Interfaz de Visualización y Filtros

> **Dependencias:** Pasos 1 y 2 completados.
> **Archivos afectados:** `app/Views/reportes/visualizar.php` (nuevo), `app/Views/layouts/partials/sidebar.php` (modificar).

---

## Objetivo

Construir la interfaz de usuario donde el administrador o supervisor pueda visualizar el listado de consultas generadas por el chatbot, aplicar filtros combinados de fecha y tipo, y navegar por los resultados mediante paginación.

---

## Tareas

### 3.1 Construir `app/Views/reportes/visualizar.php`

Esta vista integrará tanto el **formulario de filtrado** (propuesto como alternativa a la vista `filtrar.php` del plan original) como la **tabla de resultados**.

**Estructura de la vista:**
1. **Header de Página:** Título "Visualización de Reportes" e icono representativo.
2. **Tarjeta de Filtros (Card):**
   - Formulario alineado en grid (Bootstrap `row g-3`) con método `GET`.
   - Input `<input type="date">` para **Fecha Inicio**.
   - Input `<input type="date">` para **Fecha Fin**.
   - Select `<select>` para **Tipo de Consulta**, poblado dinámicamente con `$tiposConsulta`.
   - Botón **Filtrar** (`type="submit"`, estilo primary).
   - Enlace/Botón **Limpiar Filtros** (redirige a `/reportes/visualizar` limpio).
3. **Tarjeta de Resultados (Card):**
   - Botón de **Exportar a CSV** en la cabecera de la tarjeta, que redirija a `/reportes/exportar` agregando los filtros actuales a la URL (`?fecha_inicio=...&fecha_fin=...`).
   - Tabla Responsive (`table-responsive`) con las columnas: `ID`, `Socio / Código`, `Nombres`, `Tipo`, `Fecha y Hora`, `Atendido Por` (si aplica).
   - Mostrar un mensaje de "No se encontraron consultas" si `$consultas` está vacío.
4. **Paginación:**
   - Componente de paginación de Bootstrap en la parte inferior, renderizado solo si `$totalPaginas > 1`.
   - Los enlaces de paginación deben conservar los filtros actuales en la URL (ej. `&p=2&fecha_inicio=...`).

### 3.2 Habilitar el Menú Lateral (`sidebar.php`)

- En `app/Views/layouts/partials/sidebar.php`, localizar el enlace "Visualizar Reportes" (probablemente actualmente comentado o con `href="#"`).
- Cambiar el enlace a `href="/reportes/visualizar"`.
- Implementar la lógica para que el elemento adquiera la clase `active` cuando la URI coincida con `/reportes`.

---

## Verificación

```bash
docker exec cosmol_app bash -c "php -l app/Views/reportes/visualizar.php"
```
El archivo debe pasar la validación sin errores de sintaxis. Posteriormente, recargar la UI y verificar que el menú lateral funciona y la vista carga correctamente (aún si la tabla está vacía).
