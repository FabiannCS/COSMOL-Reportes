# Implementación Inicial — COSMOL Reportes

> Registro completo y unificado de la infraestructura, framework MVC, interfaz visual y módulo de autenticación del sistema de gestión de reportes de COSMOL.

---

## 1. Resumen de Estado del Proyecto

| Componente | Descripción | Estado |
|---|---|---|
| **Infraestructura Docker** | Contenedores `cosmol_app` (PHP 7.3 + Apache) y `cosmol_db` (PostgreSQL 16) | ✅ Completado |
| **Base de Datos** | Esquema SQL ANSI PostgreSQL 16 e inserción de semillas iniciales (`database/init.sql`) | ✅ Completado |
| **MVC Core** | Mini-framework propio en PHP 7.3 con Router, Controller, Model y Database Singleton | ✅ Completado |
| **Diseño UI / Layout** | Dashboard responsivo con Bootstrap 5.3, Bootstrap Icons y layout principal (`main.php`) | ✅ Completado |
| **Autenticación y RBAC** | Login/logout, manejo de sesiones PHP, `AuthMiddleware` y `RoleMiddleware` | ✅ Completado |

---

## 2. Detalle de Fases Implementadas

### Fase 1 — Infraestructura Docker y Base de Datos

- **`Dockerfile`**: Basado en `php:7.3-apache`. Instala extensiones `pdo` y `pdo_pgsql`, configura Apache (`mod_rewrite`, DocumentRoot en `public/`) e integra Composer.
- **`docker-compose.yml`**:
  - Servicio `app`: expone puerto `8080` mapeado al puerto `80` interno.
  - Servicio `db`: utiliza imagen `postgres:16`, expone puerto `5434:5432` y monta `./database/init.sql:/docker-entrypoint-initdb.d/init.sql:ro`.
- **`database/init.sql`**:
  - Estructura de tablas: `rol`, `usuario`, `tipo_consulta`, `consulta`.
  - Semilla de datos iniciales:
    - Roles: `Administrador`, `Supervisor`, `Operador`.
    - Usuario administrador por defecto: `admin` / `admin123` (contraseña hasheada con BCRYPT vía `password_hash()`).

---

### Fase 2 — Estructura MVC Core

- **Front Controller (`public/index.php`)**: Reacciona a todas las peticiones vía `.htaccess` y despacha mediante `Core\Router`.
- **Database Singleton (`app/Core/Database.php`)**: Gestiona la conexión única PDO a PostgreSQL usando variables del archivo `.env`.
- **Model Base (`app/Core/Model.php`)**: Proporciona el método `$this->db()` para consultas preparadas seguras.
- **Controller Base (`app/Core/Controller.php`)**: Maneja renderizado de vistas con layouts (`view()`), redirecciones (`redirect()`) y respuestas JSON (`json()`).
- **Router (`app/Core/Router.php`)**: Despacha la URI solicitada, resuelve controladores/métodos e invoca los middlewares asignados.

---

### Fase 3 — Layout Base y Assets

- **Assets locales**: Bootstrap 5.3 CSS/JS y Bootstrap Icons alojados en `public/assets/vendor/`.
- **Estilos y Scripts**: `public/assets/css/app.css` y `public/assets/js/app.js`.
- **Layouts (`app/Views/layouts/`)**:
  - `main.php`: plantilla principal que integra navbar, sidebar y footer.
  - `partials/navbar.php`: barra superior dinámica con info del usuario conectado y botón de logout.
  - `partials/sidebar.php`: menú lateral de navegación con enlaces a módulos según el rol.
  - `partials/footer.php`: pie de página del sistema.

---

### Fase 4 — Autenticación y Control de Acceso por Rol (RBAC)

- **Modelo `Usuario` (`app/Models/Usuario.php`)**:
  - `findByUsername($username)`: Busca usuarios activos con `JOIN` a la tabla `rol`.
  - `findById($id)`: Carga información del usuario en sesión.
- **Controlador `AuthController` (`app/Controllers/AuthController.php`)**:
  - `showLogin()`: Muestra el formulario de login o redirige al dashboard si ya hay sesión activa.
  - `login()`: Autentica credenciales usando `password_verify()`, guarda `$_SESSION['usuario']` sin la contraseña y redirige a `/dashboard`.
  - `logout()`: Destruye sesión y cookies de forma limpia.
  - `dashboard()`: Carga la vista principal del sistema.
- **Vista de Login (`app/Views/auth/login.php`)**: Formulario centrado responsivo con soporte para mensajes de error de autenticación.
- **Middlewares (`app/Middlewares/`)**:
  - `AuthMiddleware.php`: Bloquea accesos no autenticados y redirige a `/login`.
  - `RoleMiddleware.php`: Restringe vistas y acciones según el rol asignado (`Administrador`, `Supervisor`, `Operador`).
- **Configuración de Rutas (`app/Config/routes.php`)**: Definición de rutas públicas (`/login`) y protegidas (`/dashboard`, `/logout`).

---

## 3. Datos de Conexión y Pruebas del Entorno

### Conexión a Base de Datos (pgAdmin 4 / Clientes locales)
- **Host:** `localhost`
- **Puerto:** `5434`
- **Base de Datos:** `cosmol_reportes`
- **Usuario:** `admin`
- **Contraseña:** `cosmol_12345`

### Credenciales de Usuario por Defecto
- **Usuario:** `admin`
- **Contraseña:** `admin123`
- **Rol:** `Administrador`

---

## 4. Próxima Etapa: Desarrollo de Módulos Funcionales

Finalizadas las 4 fases iniciales, el desarrollo continúa con los módulos de negocio definidos en `AGENTS.md`:
1. **Módulo Operador** (Listar trabajos, registrar trabajo concluido, observaciones, historial).
2. **Módulo Administrador** (Gestión de usuarios, asignación de trabajos, gestión de estados).
3. **Módulo Reportes** (Filtrado de consultas del chatbot, visualización y exportación).
