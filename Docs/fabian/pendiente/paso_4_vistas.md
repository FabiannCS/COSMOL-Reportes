# Paso 4: Vistas Bootstrap — Módulo Operador (Fase 6)

> **Proyecto:** Sistema de Reportes y Gestión de Trabajos de Operadores (COSMOL)  
> **Fase:** 6 — Módulo Operador  
> **Paso:** 4 de 6 — Interfaz de Usuario (Views & UI)  
> **Estado:** 🟢 Completado  

---

## 1. Objetivo del Paso
Crear las interfaces gráficas responsivas para que cada tipo de operador visualice sus trabajos pendientes y pueda concluirlos mediante formularios que consumen datos de APIs externas.

## 2. Vistas a Crear

### 2.1 `app/Views/operador/reconexiones.php` — Lista de reconexiones pendientes
- Tabla responsiva con los datos recibidos de la API de Reconexiones.
- Columnas sugeridas: ID, Ubicación, Zona, Ruta, Descripción, Fecha, Acciones.
- Botón **"Ver Detalle"** que dirige a `reconexion_detalle.php?id=X`.
- Mensaje de estado cuando no hay reconexiones pendientes o cuando la API falla.

### 2.2 `app/Views/operador/reconexion_detalle.php` — Formulario de conclusión
- **Campos de solo lectura** (datos de la API): ubicación, zona, ruta, descripción, glosa, coordenadas GPS, tipo.
- **Campos editables** (que el operador completa): observaciones de conclusión, estado final, etc. *(Campos exactos por definir según la API de guardado.)*
- Botón **"Concluir"** con modal de confirmación antes del envío.
- Botón **"Volver"** para regresar a la lista.

### 2.3 `app/Views/operador/reclamos.php` — Lista de reclamos pendientes
- Tabla responsiva con los datos recibidos de la API de Reclamos.
- Columnas sugeridas: ID, Tipo de Reclamo, Descripción, Ubicación, Fecha, Acciones.
- Botón **"Ver Detalle"** que dirige a `reclamo_detalle.php?id=X`.
- Mensaje de estado cuando no hay reclamos pendientes o cuando la API falla.

### 2.4 `app/Views/operador/reclamo_detalle.php` — Formulario de conclusión
- **Campos de solo lectura** (datos de la API): tipo de reclamo, descripción, glosa, coordenadas GPS, ubicación.
- **Campos editables** (que el operador completa): observaciones de conclusión, estado final, etc. *(Campos exactos por definir.)*
- Botón **"Concluir"** con modal de confirmación.
- Botón **"Volver"** para regresar a la lista.

## 3. Elementos UI Obligatorios
- Mostrar mensajes flash (`$mensaje`, `$error`) usando `alert-success` y `alert-danger`.
- Emplear `badges` para estados y tipos de trabajo.
- Los campos `readonly`/`disabled` deben tener estilo visual diferenciado (fondo gris claro) para que el operador sepa que no son editables.
- Modal de confirmación antes de enviar la conclusión (evitar envíos accidentales).

## 4. Vistas que NO se crean
- ~~`operador/trabajos.php`~~ — Reemplazada por `reconexiones.php` y `reclamos.php`.
- ~~`operador/observaciones.php`~~ — Las observaciones no se manejan localmente.
- ~~`operador/historial.php`~~ — No aplica con el flujo actual basado en API.
