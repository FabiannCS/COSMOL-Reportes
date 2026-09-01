# Paso 5: Rutas y Navegación — Módulo Operador (Fase 6)

> **Proyecto:** Sistema de Reportes y Gestión de Trabajos de Operadores (COSMOL)  
> **Fase:** 6 — Módulo Operador  
> **Paso:** 5 de 6 — Enrutamiento y Roles  
> **Estado:** 🟢 Completado  

---

## 1. Objetivo del Paso
Registrar los nuevos endpoints en el Router del sistema, protegiéndolos bajo el rol de `Operador`, y actualizar la navegación del sidebar.

## 2. Modificación de `app/Config/routes.php`

Añadir las siguientes rutas:

- `GET /operador/trabajos` → `OperadorController@trabajos` *(lista según especialidad)*
- `GET /operador/detalle` → `OperadorController@detalle` *(formulario detalle/conclusión)*
- `POST /operador/concluir` → `OperadorController@concluir` *(envío de conclusión vía API)*

Todas estas rutas deben estar cubiertas por el middleware `['auth', 'role:Operador']`.

## 3. Actualización del Sidebar (`app/Views/layouts/partials/sidebar.php`)

Actualizar los enlaces del menú de operador para que apunten a las nuevas rutas:

- **"Mis Trabajos"** → `/operador/trabajos` (el Controller decide qué vista mostrar según especialidad)

**Nota:** Ya no se necesitan enlaces separados para "Observaciones" ni "Historial" — el flujo es: lista de trabajos → detalle/conclusión → de vuelta a la lista.

## 4. Consideraciones
- El Controller `trabajos()` es el punto de entrada único. Internamente redirige a la vista correcta (`reconexiones.php` o `reclamos.php`) según la especialidad del operador en sesión.
- Las URLs de detalle reciben el ID del trabajo como query parameter: `/operador/detalle?id=X`.
