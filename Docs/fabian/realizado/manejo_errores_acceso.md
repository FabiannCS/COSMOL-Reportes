# Manejo de Errores de Acceso y Permisos (Error 403 / 404)

**Fecha:** 2026-09-10  
**Autor:** Fabián  
**Estado:** ✅ Realizado

---

## 1. Contexto y Problema Anterior

- **Bucles de redirección:** `PermissionMiddleware.php` redirigía a los operadores sin permisos de vuelta a `/operador/trabajos` o a la URL previa (`$referer`), provocando ciclos de redirección infinitos (`ERR_TOO_MANY_REDIRECTS`) o recargas silenciosas sin mensaje visible.
- **Falta de integración visual:** `RoleMiddleware.php` respondía con texto HTML plano sin diseño ni estilos Bootstrap, sacando al usuario de la interfaz del sistema.
- **Inexistencia de vistas de error:** No existía una carpeta `app/Views/errors/` para presentar mensajes estructurados y amigables.

---

## 2. Componentes Implementados

### A. Helper Global `renderErrorView()` (`app/Core/helpers.php`)

Centraliza la entrega de respuestas de error en todo el sistema:

- **Soporte API/AJAX:** Si la petición solicita JSON (`Accept: application/json`, encabezado `X-Requested-With` o ruta `/api/...`), responde automáticamente con código HTTP y estructura JSON:
  ```json
  {
    "estado": "error",
    "codigo": 403,
    "mensaje": "..."
  }
  ```
- **Integración con Layout:** Si el usuario tiene sesión activa, renderiza la plantilla de error dentro del layout principal (`app/Views/layouts/main.php`), manteniendo la barra superior y el menú lateral disponibles.
- **Modo Autónomo:** Si no hay sesión activa, renderiza la vista de forma independiente sin romper el diseño.

### B. Vista de Permisos Insuficientes (`app/Views/errors/403.php`)

Diseñada siguiendo el mismo estándar visual de `sin_especialidad.php`:

- Tarjeta centrada con distintivo de seguridad en color rojo (`bi-shield-slash`).
- Mensaje claro indicando el motivo de la restricción.
- Caja de información detallada con: **Usuario actual**, **Rol asignado** y **Ruta solicitada**.
- Botones de acción rápida:
  - **"Volver atrás":** invoca `javascript:history.back()`.
  - **"Ir al Panel Principal":** redirige automáticamente a `/operador/trabajos` si el rol es *Operador*, o a `/dashboard` si es *Administrador/Supervisor*.

### C. Vista de Ruta No Encontrada (`app/Views/errors/404.php`)

- Mantiene la misma estética que la vista 403 para rutas inexistentes, indicando la URL que no pudo localizarse y permitiendo regresar al panel.

---

## 3. Actualización en los Middlewares y el Router

| Archivo | Comportamiento Anterior | Comportamiento Actual |
| :--- | :--- | :--- |
| `PermissionMiddleware.php` | Redirigía a `/operador/trabajos`, `/dashboard` o `$referer`. | Invoca `renderErrorView(403, 'Acceso Denegado', ...)`, detallando los permisos faltantes. |
| `RoleMiddleware.php` | Imprimía texto HTML plano sin formato (`<h1>403</h1>`). | Invoca `renderErrorView(403, 'Rol Insuficiente', ...)`, indicando el rol requerido vs. el rol actual. |
| `Router.php` | Renderizaba un mensaje básico 404 fuera de la sesión. | Invoca `renderErrorView(404, 'Página No Encontrada', ...)`, integrado al dashboard si el usuario inició sesión. |

---

## 4. Archivos Modificados / Creados

| Archivo | Acción |
| :--- | :--- |
| `app/Core/helpers.php` | **Creado** — función `renderErrorView()` |
| `app/Views/errors/403.php` | **Creado** — vista de error 403 |
| `app/Views/errors/404.php` | **Creado** — vista de error 404 |
| `app/Middlewares/PermissionMiddleware.php` | **Modificado** — usa `renderErrorView()` en lugar de redirecciones |
| `app/Middlewares/RoleMiddleware.php` | **Modificado** — usa `renderErrorView()` en lugar de HTML plano |
| `app/Core/Router.php` | **Modificado** — usa `renderErrorView()` para rutas no encontradas |
