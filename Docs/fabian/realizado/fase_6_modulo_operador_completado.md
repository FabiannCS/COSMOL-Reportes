# Módulo Operador (Reconexiones y Reclamos) — Completado ✅

> Registro consolidado y unificado de la implementación completa del **Módulo Operador (Fase 6)**, alineado estrictamente con [AGENTS.md](file:///c:/Proyectos/Cosmol_reportes/AGENTS.md) (§1.1, §5.2, §8.1 y §11).

---

## 1. Arquitectura de Datos y Catálogo de Especialidades

- **Principio Fundamental:** El sistema **no almacena trabajos localmente**. Todas las reconexiones y reclamos se consultan y concluyen en tiempo real contra las APIs REST externas de COSMOL (`http://api.cosmol.com.bo/api-consultas`).
- **Tabla `especialidad`:** Catálogo fijo en la base de datos local PostgreSQL con 3 especialidades:
  1. **Reconexión** (consume API de Reconexiones)
  2. **Maestro de alcantarillado** (consume API de Reclamos filtrando `id_tipo_reclamo = 3`)
  3. **Agua Potable** (consume API de Reclamos filtrando `id_tipo_reclamo = 2`)
- **Gestión de Especialidad en Usuarios:** Se actualizó `app/Models/Usuario.php` y `app/Controllers/UsuarioController.php` para asociar y persistir `id_especialidad` en la creación y edición de operadores, mostrando su insignia correspondiente en la tabla de usuarios.

---

## 2. Conexión HTTP Centralizada (ApiClient)

- En [app/Services/ApiClient.php](file:///c:/Proyectos/Cosmol_reportes/app/Services/ApiClient.php):
  - Soporte completo para métodos `GET`, `POST` y `PUT`.
  - Envío automático de payload JSON (`CURLOPT_POSTFIELDS`) tanto en `POST` como en `PUT` para permitir la conclusión de trabajos.
  - Manejo de respuestas y sanitización de datos.

---

## 3. Controlador de Operaciones (OperadorController)

- En [app/Controllers/OperadorController.php](file:///c:/Proyectos/Cosmol_reportes/app/Controllers/OperadorController.php):
  - **`trabajos()`:** Detecta la especialidad en sesión. Si es Reconexión, consulta `/reconexiones?estado=PENDIENTE`. Si es Alcantarillado o Agua Potable, consulta `/reclamos?estado=PENDIENTE` y filtra en memoria según el tipo de reclamo.
  - **`detalle()`:** Recupera el trabajo específico buscándolo por ID dentro de la lista de pendientes de la API. Inyecta los datos de solo lectura, la URL base de fotos y el estado de error/sesión.
  - **`concluir()`:** Envía la conclusión al servidor externo:
    - Reconexiones: `PUT /reconexiones/{id}` con `{ "usuario_reconexion": (int), "lectura_reconexion": (num), "glosa": (string) }`.
    - Reclamos: `POST /reclamos/concluir` con `id_reclamo`, `estado` y `observacion_conclusion`.

---

## 4. Vistas Operativas e Interfaz de Usuario

### 4.1 Vistas de Listado (`reconexiones.php` y `reclamos.php`)
- **Filas Clickeables:** Al pulsar cualquier fila de la tabla se abre directamente el formulario de conclusión del trabajo (`/operador/detalle?id=...`).
- **Navegación GPS Integrada:**
  - Enlace en la ubicación para abrir Google Maps centrado en el predio.
  - Botón verde **Ruta** (`https://www.google.com/maps/dir/?api=1&destination=...`) para iniciar la navegación paso a paso (*Cómo llegar*) hacia las coordenadas GPS.
- **Indicador de Fotografía:** Insignia visual `Foto` que avisa al operador si el socio adjuntó una imagen.
- **Diseño Responsive Móvil:** Adaptado para teléfonos inteligentes (`< 576px`), ocultando columnas secundarias extensas (Glosa del Cliente, Origen) y permitiendo el flujo vertical de zona/ruta sin desbordes horizontales.

### 4.2 Vistas de Detalle (`reconexion_detalle.php` y `reclamo_detalle.php`)
- **Información del Trabajo (API — Solo Lectura):**
  - Identificación del Socio/Solicitante (`cod_socio` y `nombre_socio`, ej: `589 - OSINAGA ROSA FRANCO VDA. DE`).
  - Descripción técnica, ubicación (U-Z-R), dirección física del predio y glosa del cliente.
  - **Fotografía Adjunta:** Carga de imágenes reales alojadas en `https://chatbot.cosmol.com.bo/uploads/...` con vista previa responsiva y botón para abrir en tamaño completo.
  - **Coordenadas GPS y Navegación:** Botones independientes para *Ver Ubicación* y *Trazar Ruta (Cómo llegar)*.
- **Formulario de Conclusión (Editable):**
  - Campos requeridos según tipo de trabajo (lecturación, informe técnico, estado).
  - Modal de confirmación antes de enviar el formulario para evitar conclusiones accidentales.

---

## 5. Pruebas y Validación Final

- **Compatibilidad:** 100% compatible con **PHP 7.3** ejecutado en contenedores Docker (`cosmol_app`, `cosmol_db`).
- **Pruebas de Red y APIs en Vivo:**
  - Reconexiones pendientes obtenidas y renderizadas con éxito.
  - Reclamos de Alcantarillado y Agua Potable filtrados correctamente.
  - Imágenes JPEG descargadas con `HTTP 200` desde `https://chatbot.cosmol.com.bo`.
- **Navegación y UX:** Verificado sin errores de Notices/Warnings de PHP ni scroll horizontal en resoluciones móviles.
