# Fase 8 — Paso 1: Modelo de Reporte y Datos Base

> **Dependencias:** Ninguna (inicio de la Fase 8).
> **Archivos afectados:** `app/Models/Reporte.php` (nuevo).
> **Principio Fundamental:** La tabla `consulta` almacena el histórico de atenciones del chatbot y se relaciona con `tipo_consulta` y `usuario`.

---

## Objetivo

Establecer la capa de acceso a datos (Model) que permita obtener las consultas del chatbot de forma estructurada, paginada y permitiendo filtrado por fechas y tipos.

---

## Tareas

### 1.1 Crear el Modelo `Reporte.php`

Crear la clase `app/Models/Reporte.php` extendiendo de `App\Core\Model`.

**Métodos necesarios:**

1. `getTiposConsulta()`
   - `SELECT id_tipo, nombre FROM tipo_consulta ORDER BY nombre ASC`
   - Retorna la lista de tipos para poblar el `<select>` del filtro en la vista.

2. `getConsultasPaginadas($filtros, $limit, $offset)`
   - Debe hacer un `SELECT` con `JOIN` a `tipo_consulta` (t) y `usuario` (u).
   - Campos: `c.id_consulta, c.codigo_socio, c.nombres, c.fecha_consulta, c.hora_consulta, t.nombre as tipo, u.username`.
   - **Filtros dinámicos:** 
     - Si `$filtros['fecha_inicio']` existe: `AND c.fecha_consulta >= :fecha_inicio`
     - Si `$filtros['fecha_fin']` existe: `AND c.fecha_consulta <= :fecha_fin`
     - Si `$filtros['id_tipo']` existe: `AND c.id_tipo = :id_tipo`
   - Orden: descendente por fecha y hora (`ORDER BY c.fecha_consulta DESC, c.hora_consulta DESC`).

3. `getTotalConsultas($filtros)`
   - Ejecuta exactamente la misma lógica de filtrado del método anterior pero con `SELECT COUNT(*)`.
   - Retorna un entero. Servirá para la paginación de Bootstrap.

4. `getAllConsultasExport($filtros)`
   - Ejecuta la misma lógica de `getConsultasPaginadas` pero sin `$limit` ni `$offset`.
   - Diseñado para extraer la sábana completa de datos para su exportación a CSV.

### 1.2 Datos de Prueba (Semilla)

Verificar si las tablas `tipo_consulta` y `consulta` tienen registros locales. Si están vacías, será necesario generar un script SQL para insertar 2 o 3 tipos de consulta base y consultas falsas de prueba para poder evaluar los filtros y la interfaz de manera efectiva.

---

## Verificación

```bash
docker exec cosmol_app bash -c "php -l app/Models/Reporte.php"
```
El archivo debe pasar la validación sin errores de sintaxis.
