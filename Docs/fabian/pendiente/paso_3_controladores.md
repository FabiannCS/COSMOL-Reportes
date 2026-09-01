# Paso 3: Controladores — Módulo Operador (Fase 6)

> **Proyecto:** Sistema de Reportes y Gestión de Trabajos de Operadores (COSMOL)  
> **Fase:** 6 — Módulo Operador  
> **Paso:** 3 de 6 — Lógica de Negocio y Controladores (Controllers)  
> **Estado:** 🟢 Completado  

---

## 1. Objetivo del Paso
Desarrollar la lógica principal de negocio dentro de `OperadorController`, que consulta la especialidad del operador en sesión y consume las APIs externas correspondientes para listar y concluir trabajos.

## 2. Métodos de `OperadorController.php`

### 2.1 `trabajos()` (GET) — Listado de trabajos pendientes
1. Obtiene el `id_usuario` de `$_SESSION`.
2. Consulta `Especialidad::getByUsuario($idUsuario)` para obtener la especialidad del operador.
3. Según la especialidad:
   - **Reconexión** → instancia `ApiClient` con URL de API de Reconexiones → `GET /socios/23807/reconexiones`.
   - **Maestro de alcantarillado** → instancia `ApiClient` con URL de API de Reclamos → `GET /reclamos/pendientes?tipo=alcantarillado` *(filtro por definir)*.
   - **Agua Potable** → instancia `ApiClient` con URL de API de Reclamos → `GET /reclamos/pendientes?tipo=agua_potable` *(filtro por definir)*.
4. Si la API devuelve error → renderiza vista con mensaje de error.
5. Renderiza `operador/reconexiones.php` o `operador/reclamos.php` según especialidad, pasando los datos recibidos.

### 2.2 `detalle($id)` (GET) — Formulario de detalle/conclusión
1. Obtiene la especialidad del operador en sesión.
2. Según la especialidad, consulta la API correspondiente por ID del trabajo.
3. Renderiza `operador/reconexion_detalle.php` o `operador/reclamo_detalle.php`.
4. Los campos traídos de la API se muestran como **solo lectura** (`readonly` / `disabled`).
5. Los campos editables (que el operador debe completar) se muestran como inputs activos.

### 2.3 `concluir()` (POST) — Enviar conclusión del trabajo
1. Recibe los datos del formulario (`$_POST`).
2. Sanitiza y valida los campos editables.
3. Según la especialidad, envía los datos vía `ApiClient::post()` a la API correspondiente.
4. Si la API responde exitosamente → redirige a `trabajos` con mensaje flash de éxito.
5. Si la API responde con error → redirige con mensaje flash de error.

## 3. Lógica de Filtrado por Especialidad

El filtrado se implementa a nivel del Controller, **no** a nivel de base de datos local:

```
Operador inicia sesión
  → Controller lee especialidad de BD local
  → Según especialidad, elige API + endpoint + filtros
  → Datos de API se pasan a la vista correspondiente
```

Cada especialidad tiene su propia vista para poder personalizar las columnas y campos del formulario según el tipo de trabajo.

## 4. Reglas Críticas de Seguridad
- Validar que el usuario en sesión tenga rol `Operador` y especialidad asignada antes de cualquier acción.
- Si el operador no tiene especialidad asignada, mostrar mensaje indicando que debe contactar al administrador.
- Manejar errores de la API de forma controlada (no exponer detalles técnicos al usuario).
