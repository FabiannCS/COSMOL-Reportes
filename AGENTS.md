# AGENTS.md — Sistema de Visualización de Reportes y Gestión de Trabajos de Operadores (COSMOL)

> Este archivo es la fuente de verdad para cualquier agente de IA que trabaje en este repositorio. Léelo completo antes de generar, modificar o sugerir código. Si una instrucción del usuario entra en conflicto con lo aquí definido (ej. cambiar la versión de PHP), señálalo antes de proceder.

## 1. Descripción del Proyecto

Sistema que centraliza los reportes de las consultas generadas por los socios a través del chatbot de COSMOL, e integra un módulo operativo para que los operadores controlen y registren sus trabajos pendientes y concluidos.

**Objetivo general:** desarrollar un sistema de gestión que centralice los reportes de las consultas generadas por los socios mediante el chatbot de COSMOL, integrando un módulo operativo con el fin de facilitar a los operadores el control y registro de sus trabajos pendientes y concluidos.

**Objetivos específicos:**
- Diagnosticar la situación actual de los operadores y determinar los requisitos funcionales y no funcionales.
- Identificar los actores y casos de uso a partir de los requisitos identificados.
- Analizar los actores y casos de uso para definir los módulos a implementar.
- Diseñar la arquitectura del sistema y el modelado de la base de datos relacional.
- Implementar los módulos definidos en el análisis de casos de uso.
- Validar el funcionamiento del sistema mediante pruebas locales y en producción.

## 2. Stack Tecnológico — Restricciones Estrictas

| Componente | Tecnología | Notas |
|---|---|---|
| Backend | **PHP 7.3** | ⚠️ Versión indispensable. NUNCA sugerir ni usar sintaxis, funciones o dependencias exclusivas de PHP 7.4+/8.x (ej. constructor property promotion, union types, `match`, named arguments, `str_contains`, etc.). |
| Frontend | PHP server-side + HTML | No usar frameworks JS (React, Vue, etc.). JS plano solo si es estrictamente necesario. |
| Diseño UI | Bootstrap | Plantilla de dashboard. Priorizar componentes y clases utilitarias de Bootstrap antes de escribir CSS propio. |
| Contenerización | Docker / docker-compose | Todo el entorno (PHP, servidor web, PostgreSQL) corre en contenedores. No asumir instalación local de PHP/Postgres. |
| Base de datos | PostgreSQL | Acceso vía PDO (`pdo_pgsql`). No usar extensiones abandonadas como `pg_connect` directo salvo justificación. |
| Autoload | Composer (PSR-4) | Solo para autoload de clases propias, no para instalar frameworks pesados (Laravel, Symfony, etc.). |

**Regla de oro:** antes de proponer una librería, sintaxis o función, verificar que sea compatible con PHP 7.3. En caso de duda, preferir la solución más simple y compatible.

## 3. Arquitectura: MVC (mini-framework propio)

No se usa un framework completo. Se implementa un MVC ligero hecho a medida, con las piezas mínimas necesarias.

**Flujo de una petición:**
1. El navegador solicita una URL, ej. `/operador/trabajos`.
2. Nginx/Apache reescribe la petición hacia `public/index.php` (front controller). `public/` es el único document root expuesto; `app/` nunca es accesible directamente por URL.
3. `index.php` inicializa `Core/Router.php`, que resuelve la ruta contra `Config/routes.php`.
4. La petición pasa por los middlewares correspondientes: `AuthMiddleware` (¿hay sesión activa?) y `RoleMiddleware` (¿el rol del usuario tiene permiso sobre esa ruta?).
5. El router invoca el método correspondiente del Controller.
6. El Controller llama al Model correspondiente, que ejecuta la consulta SQL vía PDO (`Core/Database.php`).
7. El Controller pasa los datos a la Vista correspondiente en `app/Views/`, que se renderiza dentro de `layouts/main.php` (navbar + sidebar + footer comunes, estilo Bootstrap).
8. Se devuelve el HTML final al navegador.

## 4. Estructura de Carpetas

```
cosmol-reportes/
├── docker/
│   ├── php/
│   │   └── Dockerfile
│   ├── nginx/
│   │   └── default.conf
│   └── postgres/
│       └── init.sql
├── docker-compose.yml
├── composer.json
├── .env
├── .env.example
├── .gitignore
│
├── public/
│   ├── index.php
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── img/
│       └── vendor/
│           └── bootstrap/
│
└── app/
    ├── Config/
    │   ├── database.php
    │   └── routes.php
    │
    ├── Core/
    │   ├── Router.php
    │   ├── Controller.php
    │   ├── Model.php
    │   └── Database.php
    │
    ├── Controllers/
    │   ├── AuthController.php
    │   ├── UsuarioController.php
    │   ├── RolController.php
    │   ├── TrabajoController.php
    │   ├── OperadorController.php
    │   ├── AdministradorController.php
    │   └── ReporteController.php
    │
    ├── Models/
    │   ├── Usuario.php
    │   ├── Rol.php
    │   ├── Permiso.php
    │   ├── Trabajo.php
    │   ├── Estado.php
    │   └── Reporte.php
    │
    ├── Middlewares/
    │   ├── AuthMiddleware.php
    │   └── RoleMiddleware.php
    │
    └── Views/
        ├── layouts/
        │   ├── main.php
        │   └── partials/
        │       ├── navbar.php
        │       ├── sidebar.php
        │       └── footer.php
        │
        ├── auth/
        │   └── login.php
        │
        ├── seguridad/
        │   ├── usuarios.php
        │   ├── roles.php
        │   └── permisos.php
        │
        ├── operador/
        │   ├── trabajos.php
        │   ├── registrar_trabajo.php
        │   ├── observaciones.php
        │   └── historial.php
        │
        ├── administrador/
        │   ├── usuarios.php
        │   ├── trabajos.php
        │   ├── trabajo_pendiente.php
        │   └── estados.php
        │
        └── reportes/
            ├── filtrar.php
            ├── visualizar.php
            └── exportar.php
```

**Regla:** cualquier archivo nuevo debe ubicarse siguiendo esta estructura. Un Controller nuevo implica su Model correspondiente (si aplica) y su carpeta de Views. No crear controladores "genéricos" que mezclen módulos distintos.

## 5. Alcance del Sistema (Módulos y Casos de Uso)

Cada módulo se corresponde directamente con Controllers/Views del punto 4.

### 5.1 Seguridad
- Gestionar Usuario
- Gestionar Rol
- Asignar Permiso
- Validar Accesos

→ `AuthController`, `UsuarioController`, `RolController` · `app/Views/seguridad/` · `app/Views/auth/`

### 5.2 Módulo Operador
- Listar Trabajo
- Registrar Trabajo Concluido
- Registrar Observaciones
- Historial de Trabajo

→ `OperadorController`, `TrabajoController` · `app/Views/operador/`

### 5.3 Módulo Administrador
- Gestionar Usuario
- Gestionar Trabajo
- Registrar Trabajo Pendiente
- Gestionar Estado

→ `AdministradorController`, `TrabajoController` · `app/Views/administrador/`

### 5.4 Módulo Reportes
- Filtrar Reporte
- Visualizar Reporte
- Exportar Reporte

→ `ReporteController` · `app/Views/reportes/`

## 6. Requerimientos Funcionales

| Nro. | Requerimiento | Descripción |
|---|---|---|
| R1 | Gestionar Roles de Usuarios | Autenticar usuarios, permitir cierre de sesión y controlar acceso según roles fijos de Administrador. |
| R2 | Gestionar reportes de socios | Mostrar las consultas realizadas por los socios. |
| R3 | Concluir trabajos pendientes | El administrador registra los trabajos pendientes de los operadores. |
| R4 | Gestionar trabajos del operador | Mostrar todos los trabajos pendientes y realizados de los operadores. |
| R5 | Registrar trabajos concluidos | Registrar los trabajos realizados por los operadores. |

## 7. Requerimientos No Funcionales

| Nro. | Requerimiento | Descripción |
|---|---|---|
| R1 | Plataforma web | Funcional en navegador moderno, escritorio y móvil. |
| R2 | Seguridad y autenticación | Autenticación/autorización basada en roles fijos: Administrador, Supervisor, Operador. |
| R3 | Actualización rápida de reportes | Datos de reportes siempre actualizados. |
| R4 | Actualización rápida de trabajos | Trabajos de operadores actualizados en tiempo real. |
| R5 | Entorno de ejecución | Contenerizado con Docker (frontend, backend, BD uniformes). |
| R6 | Integridad y consistencia | Registro de trabajos ejecutado de forma consistente (transaccional donde aplique). |

## 8. Roles del Sistema

Tres roles fijos, validados en cada petición vía `RoleMiddleware`:

- **Administrador** — gestión total: usuarios, trabajos, estados, reportes.
- **Supervisor** — mencionado en requerimientos no funcionales (R2); su alcance específico de permisos debe confirmarse con el usuario si no está claro en un caso de uso dado.
- **Operador** — gestiona sus propios trabajos: listar, registrar como concluido, agregar observaciones, ver historial.

## 9. Convenciones de Código para el Agente

- **Nombres de clases:** PascalCase (`TrabajoController`, `UsuarioModel`).
- **Nombres de métodos/variables:** camelCase.
- **Nombres de tablas SQL:** snake_case, en español, singular o plural consistente con el resto del esquema (confirmar convención existente antes de crear tablas nuevas).
- **Acceso a datos:** siempre vía PDO con consultas preparadas (prepared statements). Nunca concatenar variables directamente en SQL — riesgo de inyección.
- **Sesiones/autenticación:** manejar vía `$_SESSION`, validadas en `AuthMiddleware` antes de llegar a cualquier Controller protegido.
- **Vistas:** no incluir lógica de negocio ni consultas SQL en archivos de `Views/`. Las vistas solo reciben datos ya procesados del Controller.
- **Bootstrap:** reutilizar componentes del layout (`layouts/main.php`, `partials/`) en vez de duplicar markup de navbar/sidebar en cada vista.

## 10. Reglas Explícitas para el Agente de IA

- ❌ No actualizar ni sugerir actualizar la versión de PHP.
- ❌ No introducir frameworks JS de frontend.
- ❌ No introducir frameworks PHP completos (Laravel, Symfony, CodeIgniter) — el proyecto usa MVC casero.
- ❌ No mezclar responsabilidades de módulos distintos en un mismo Controller/Model.
- ✅ Mantener toda lógica de acceso a datos dentro de `app/Models/`.
- ✅ Validar rol y sesión en cada ruta protegida, sin excepción.
- ✅ Si un requerimiento no está claro o falta información para implementarlo (ej. permisos exactos del rol Supervisor), preguntar antes de asumir.