# Fase 8 — Paso 2: Controlador de Reportes y Enrutamiento

> **Dependencias:** Paso 1 (Modelo `Reporte.php` creado).
> **Archivos afectados:** `app/Controllers/ReporteController.php` (nuevo), `app/Config/routes.php`.

---

## Objetivo

Implementar la lógica de negocio (Controller) que conecte el modelo de datos con la vista final, procese los filtros solicitados por el usuario y emita los datos listos para ser consumidos o exportados.

---

## Tareas

### 2.1 Crear el Controlador `ReporteController.php`

Crear la clase `app/Controllers/ReporteController.php` extendiendo de `App\Core\Controller`.

**Método `visualizar()` (o `index()`):**
1. Instanciar el modelo `Reporte`.
2. Capturar parámetros `GET`: `fecha_inicio`, `fecha_fin`, `id_tipo`, y `p` (página actual, por defecto 1).
3. Obtener el catálogo de tipos (`getTiposConsulta()`) para pasarlo a la vista.
4. Obtener el total de registros (`getTotalConsultas()`) aplicando los filtros para calcular el `$totalPaginas` (ej. 15 items por página).
5. Obtener los registros paginados (`getConsultasPaginadas()`).
6. Pasar todos los datos, incluyendo el arreglo de filtros actuales, hacia la vista `reportes/visualizar`.

### 2.2 Registrar las Rutas

En `app/Config/routes.php`, añadir dentro del grupo de peticiones `GET`:

```php
// Módulo de Reportes (Administrador y Supervisor)
'/reportes/visualizar' => ['ReporteController', 'visualizar', ['auth', 'role:Administrador,Supervisor']],
'/reportes/exportar'   => ['ReporteController', 'exportar',   ['auth', 'role:Administrador,Supervisor']],
```

*(Nota: El método `exportar()` se implementará a detalle en el Paso 4, pero la ruta ya queda declarada y asegurada con los roles correctos).*

---

## Verificación

```bash
docker exec cosmol_app bash -c "php -l app/Controllers/ReporteController.php && php -l app/Config/routes.php"
```
Ambos archivos deben pasar la validación sin errores de sintaxis.
