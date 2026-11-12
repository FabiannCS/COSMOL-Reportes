# AGENTS.md — Sistema de Visualización de Reportes y Gestión de Trabajos (COSMOL)

> **Fuente de verdad técnica y operativa para agentes de IA.** Léelo antes de generar, modificar o sugerir código. Si una instrucción entra en conflicto con lo aquí definido, señálalo antes de proceder.

---

## 1. Visión General y Arquitectura de Datos

El sistema centraliza las métricas y auditorías de consultas ciudadanas (**Chatbot WhatsApp** y **App Móvil**) y provee un módulo operativo y de supervisión para trabajos de **Reconexión** y **Reclamos Técnicos** de COSMOL.

### Principio Fundamental de Datos
1. **Trabajos de Terreno (Reconexiones y Reclamos) = 100% Externos:** No se almacenan en la BD local. Residen en el servidor externo de COSMOL (PostgreSQL/SAI) y se consultan/concluyen en tiempo real vía APIs REST (`ApiClient`).
2. **BD Local (`cosmol_reportes`):**
   - **Seguridad (RBAC):** `usuario`, `rol`, `permiso`, `rol_permiso`.
   - **Catálogo de Especialidades:** `especialidad` (1: Reconexión, 2: Maestro de alcantarillado, 3: Agua Potable).
   - **Auditoría de Consultas:** `consulta` y `tipo_consulta` (eventos de WhatsApp y App Móvil).
   - **Contingencias Locales:** `trabajo_seguimiento` (registra únicamente trabajos con contingencia: `'NO CONCLUIDO'` o `'NO PROCEDENTE'`, sin duplicar los externos).

---

## 2. Stack Tecnológico y Reglas Estrictas

| Componente | Tecnología | Regla Estricta |
|---|---|---|
| **Backend** | **PHP 7.3** | **Estricto.** NUNCA usar sintaxis de PHP 7.4+/8.x (no arrow functions `fn()`, no union types, no `str_contains`, no constructor property promotion). |
| **Frontend** | PHP + HTML server-side | **Sin frameworks JS** (React, Vue, etc.). JS plano/nativo mínimo solo para interactividad DOM. |
| **Diseño** | Bootstrap 5.3 + Icons | Assets locales en `public/assets/vendor/` (sin CDN externas). Paleta corporativa en `public/assets/css/app.css`. |
| **Entorno** | Docker / docker-compose | `php:7.3-apache` y `postgres:16`. DB Host: `db` (NUNCA `localhost`). |
| **Arquitectura**| MVC Artesanal Ligero | Sin frameworks pesados. Enrutamiento centralizado en `app/Config/routes.php` y Front Controller `public/index.php`. |

---

## 3. Mapeo de Especialidades Técnicas y APIs Externas

Los operadores y supervisores interactúan con las APIs externas según su especialidad:

| ID | Especialidad | API / Endpoint Consumido | Filtro Tipo | Conclusión (PUT) | Campos Payload Conclusión |
|:---:|---|---|---|---|---|
| **1** | **Reconexión** | `GET /reconexiones?estado=PENDIENTE` | Todas | `PUT /reconexiones/{id}` | `usuario_reconexion`, `lectura_reconexion`, `glosa` |
| **2** | **Maestro de alcantarillado** | `GET /reclamos?estado=PENDIENTE` | `id_tipo_reclamo === 3` | `PUT /reclamos/{id}` | `usuario_conclucion`, `usuario_reclamo`, `glosa` |
| **3** | **Agua Potable** | `GET /reclamos?estado=PENDIENTE` | `id_tipo_reclamo === 2` | `PUT /reclamos/{id}` | `usuario_conclucion`, `usuario_reclamo`, `glosa` |

*Para peticiones concurrentes en administración/dashboard, se utiliza `ApiClient::getMultiFromClients()` (multi-cURL en paralelo).*

---

## 4. Ingesta de Auditoría Externa (Chatbot y App Móvil)

El endpoint receptor de consultas (`POST /api/consultas`) recibe eventos de canales externos:
- **Cabecera requerida:** `X-Reportes-Token: <REPORTES_API_TOKEN>`
- **Canales (`tipo_ubicacion`):**
  - `'WHATSAPP'` ➔ Asigna usuario de sistema `chatbot_whatsapp`.
  - `'APP_MOVIL'` ➔ Asigna usuario de sistema `app_movil`.
- **Catálogo de Tipos de Consulta Relevantes (`tipo_consulta`):**
  - `1`: Autenticación / Acceso
  - `2`: Consulta de Deuda
  - `3`: Historial de Consumo
  - `4`: Registro de Reclamo
  - `5`: Solicitud de Reconexión
  - `9`: Descarga de Factura PDF
  - `10`: Pago: Multipago | `11`: Pago: Pago al Paso | `12`: Pago: Código QR

---

## 5. Estructura Modular del Proyecto

```text
cosmol-reportes/
├── app/
│   ├── Config/          # routes.php, database.php, api.php
│   ├── Core/            # Controller.php, Router.php, Database.php (PDO Singleton), helpers.php
│   ├── Middlewares/     # AuthMiddleware, RoleMiddleware, PermissionMiddleware, CsrfMiddleware
│   ├── Services/        # ApiClient.php (cURL y multi-cURL para APIs de trabajos externos)
│   ├── Controllers/
│   │   ├── AuthController.php          # Login, logout y orquestación del Dashboard
│   │   ├── OperadorController.php      # Trabajos pendientes, detalle, conclusión e historial
│   │   ├── AdministradorController.php # Supervisión global, no concluidos e historial
│   │   ├── ReporteController.php       # Reportes Chatbot WhatsApp y App Móvil (HTML + CSV)
│   │   ├── UsuarioController.php       # Gestión de personal, roles y especialidades
│   │   ├── RolController.php           # CRUD roles y matriz RBAC de permisos
│   │   ├── PerfilController.php        # Perfil del usuario en sesión
│   │   └── ConsultaApiController.php   # Receptor REST M2M (POST /api/consultas)
│   ├── Models/          # Usuario, Rol, Permiso, Especialidad, Reporte, TrabajoSeguimiento
│   └── Views/
│       ├── layouts/     # main.php y partials/ (navbar, sidebar, footer)
│       ├── auth/        # login.php
│       ├── dashboard/   # dashboard.php y partials/ (filtros, kpis, graficos, tablas, accesos)
│       ├── operador/    # reconexiones, reclamos, detalles, historial y sin_especialidad
│       ├── administrador/# trabajos, trabajos_no_concluidos, usuarios, historial y partials/
│       ├── reportes/    # Reportes_chatbot.php, app_movil.php y partials/
│       └── seguridad/   # roles.php, permisos.php
├── database/            # init.sql, migracion_app_movil.sql
└── public/              # index.php, .htaccess, assets/ (css, js, vendor)
```

---

## 6. Convenciones y Reglas Mandatorias para el Agente

1. **Compatibilidad PHP 7.3 Estricta:** No usar funciones ni sintaxis de versiones superiores.
2. **Seguridad y RBAC:**
   - Proteger formularios POST con `<?= csrfField() ?>` y `CsrfMiddleware`.
   - Proteger rutas en `routes.php` con los middlewares correspondientes (`auth`, `role`, `permission`).
3. **Persistencia y Acceso a Datos:**
   - Base de datos local únicamente con consultas preparadas PDO (`$stmt->prepare`).
   - El Host de conexión en Docker siempre es `db` (nunca `localhost`).
   - NUNCA duplicar trabajos externos en la BD local. Solo registrar contingencias en `trabajo_seguimiento`.
4. **Vistas e Interfaz:**
   - Sanitizar salidas dinámicas HTML con `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` o el helper `e($val)`.
   - Mantener vistas modulares y segmentadas en partials organizados por carpeta.
   - Respetar el diseño e información consolidada del Dashboard y estilos corporativos en `public/assets/css/app.css`.
