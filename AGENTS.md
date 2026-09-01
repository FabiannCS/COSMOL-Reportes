# AGENTS.md — Sistema de Visualización de Reportes y Gestión de Trabajos de Operadores (COSMOL)

> Este archivo es la fuente de verdad para cualquier agente de IA que trabaje en este repositorio. Léelo completo antes de generar, modificar o sugerir código. Si una instrucción del usuario entra en conflicto con lo aquí definido (ej. cambiar la versión de PHP), señálalo antes de proceder.

## 1. Descripción del Proyecto

Sistema que centraliza los reportes de las consultas generadas por los socios a través del chatbot de COSMOL, e integra un módulo operativo para que los operadores visualicen y concluyan sus trabajos pendientes (reconexiones y reclamos) consumiendo datos desde APIs externas.

**Objetivo general:** desarrollar un sistema de gestión que centralice los reportes de las consultas generadas por los socios mediante el chatbot de COSMOL, integrando un módulo operativo con el fin de facilitar a los operadores la visualización y conclusión de sus trabajos pendientes y concluidos.

**Objetivos específicos:**
- Diagnosticar la situación actual de los operadores y determinar los requisitos funcionales y no funcionales.
- Identificar los actores y casos de uso a partir de los requisitos identificados.
- Analizar los actores y casos de uso para definir los módulos a implementar.
- Diseñar la arquitectura del sistema, el modelado de la base de datos local y la integración con APIs externas.
- Implementar los módulos definidos en el análisis de casos de uso.
- Validar el funcionamiento del sistema mediante pruebas locales y en producción.

### 1.1 Arquitectura de Datos — Principio Fundamental

**El sistema de Reportes NO almacena trabajos localmente.** Los trabajos (reconexiones y reclamos) viven en una base de datos externa (servidor de pruebas PostgreSQL) y se acceden exclusivamente vía APIs REST (ver §11 para detalles de integración). La BD local (`cosmol_reportes`) solo almacena: usuarios, roles, permisos, especialidades y consultas de reportes. Las especialidades de operador se detallan en §8.1.

## 2. Stack Tecnológico — Restricciones Estrictas

| Componente | Tecnología | Notas |
|---|---|---|
| Backend | **PHP 7.3** | Versión indispensable. NUNCA sugerir ni usar sintaxis, funciones o dependencias exclusivas de PHP 7.4+/8.x (ej. constructor property promotion, union types, `match`, named arguments, `str_contains`, etc.). |
| Frontend | PHP server-side + HTML | No usar frameworks JS (React, Vue, etc.). JS plano solo si es estrictamente necesario. |
| Diseño UI | Bootstrap | Plantilla de dashboard. Priorizar componentes y clases utilitarias de Bootstrap antes de escribir CSS propio. |
| Entorno de desarrollo | **Docker / docker-compose** | Todo el entorno (PHP + Apache, PostgreSQL) corre en contenedores. No instalar PHP, Apache ni PostgreSQL localmente (nada de XAMPP/WAMP). |
| Servidor web | Apache (dentro del contenedor PHP) | Imagen base `php:7.3-apache`. El document root del contenedor apunta a `public/`. |
| Base de datos | PostgreSQL (contenedor separado) | Acceso desde PHP vía PDO (`pdo_pgsql`). No usar extensiones abandonadas como `pg_connect` directo salvo justificación. |
| Autoload | Composer (PSR-4) | Solo para autoload de clases propias, no para instalar frameworks pesados (Laravel, Symfony, etc.). |

**Regla de oro:** antes de proponer una librería, sintaxis o función, verificar que sea compatible con PHP 7.3. En caso de duda, preferir la solución más simple y compatible.

## 3. Arquitectura: MVC (mini-framework propio)

No se usa un framework completo. Se implementa un MVC ligero hecho a medida, con las piezas mínimas necesarias.

**Flujo de una petición (datos locales — ej. usuarios, roles, reportes):**
1. El navegador solicita una URL, ej. `/seguridad/usuarios`, contra el puerto expuesto por el contenedor PHP/Apache (ej. `localhost:8080`).
2. Apache, dentro del contenedor, reescribe la petición hacia `public/index.php` (front controller) mediante `.htaccess`. `public/` es el único document root expuesto; `app/` nunca es accesible directamente por URL.
3. `index.php` inicializa `Core/Router.php`, que resuelve la ruta contra `Config/routes.php`.
4. La petición pasa por los middlewares correspondientes: `AuthMiddleware` (¿hay sesión activa?) y `RoleMiddleware` (¿el rol del usuario tiene permiso sobre esa ruta?).
5. El router invoca el método correspondiente del Controller.
6. El Controller llama al Model correspondiente, que ejecuta la consulta SQL vía PDO (`Core/Database.php`), conectando al contenedor de PostgreSQL por el nombre del servicio definido en `docker-compose.yml` (ej. `db`), no por `localhost`.
7. El Controller pasa los datos a la Vista correspondiente en `app/Views/`, que se renderiza dentro de `layouts/main.php` (navbar + sidebar + footer comunes, estilo Bootstrap).
8. Se devuelve el HTML final al navegador.

**Flujo de una petición (datos externos — ej. trabajos del operador):**
Idéntico al flujo local en pasos 1-5, pero en el paso 6 el Controller consulta la especialidad del operador en sesión y hace una petición HTTP (cURL) a la API externa correspondiente en lugar de consultar la BD local. El flujo completo se detalla en §11.2.

## 4. Estructura de Carpetas

```
cosmol-reportes/
├── Dockerfile
├── docker-compose.yml
├── database/
│   └── init.sql
├── composer.json
├── .env
├── .env.example
├── .gitignore
│
├── public/
│   ├── index.php
│   ├── .htaccess
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
    │   ├── api.php              ← URLs base de las APIs externas
    │   └── routes.php
    │
    ├── Core/
    │   ├── Router.php
    │   ├── Controller.php
    │   ├── Model.php
    │   └── Database.php
    │
    ├── Services/
    │   └── ApiClient.php        ← Cliente HTTP centralizado (cURL) para consumir APIs externas
    │
    ├── Controllers/
    │   ├── AuthController.php
    │   ├── UsuarioController.php
    │   ├── RolController.php
    │   ├── OperadorController.php
    │   ├── AdministradorController.php
    │   └── ReporteController.php
    │
    ├── Models/
    │   ├── Usuario.php
    │   ├── Rol.php
    │   ├── Permiso.php
    │   ├── Especialidad.php
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
        │   ├── reconexiones.php          ← Lista de reconexiones pendientes (API)
        │   ├── reconexion_detalle.php     ← Formulario de conclusión de reconexión
        │   ├── reclamos.php              ← Lista de reclamos pendientes (API)
        │   └── reclamo_detalle.php        ← Formulario de conclusión de reclamo
        │
        ├── administrador/
        │   └── usuarios.php
        │
        └── reportes/
            ├── filtrar.php
            ├── visualizar.php
            └── exportar.php
```

**Regla:** cualquier archivo nuevo debe ubicarse siguiendo esta estructura. Un Controller nuevo implica su Model correspondiente (si aplica para datos locales) o su Service (si consume API externa), y su carpeta de Views. No crear controladores "genéricos" que mezclen módulos distintos.

## 5. Alcance del Sistema (Módulos y Casos de Uso)

Cada módulo se corresponde directamente con Controllers/Views del punto 4.

### 5.1 Seguridad
- Gestionar Usuario (incluyendo asignación de especialidad para operadores)
- Gestionar Rol
- Asignar Permiso
- Validar Accesos

→ `AuthController`, `UsuarioController`, `RolController` · `app/Views/seguridad/` · `app/Views/auth/`

### 5.2 Módulo Operador
- Listar trabajos pendientes según especialidad del operador (datos vía API externa)
- Visualizar detalle del trabajo en formulario (campos de API en solo lectura)
- Concluir trabajo (enviar datos editables vía API externa)

**Flujo por especialidad:**
- **Reconexión:** Consulta API de Reconexiones → muestra lista → formulario de conclusión → envía a API.
- **Maestro de alcantarillado:** Consulta API de Reclamos (filtrado por tipo alcantarillado) → muestra lista → formulario de conclusión → envía a API.
- **Agua Potable:** Consulta API de Reclamos (filtrado por tipo agua potable) → muestra lista → formulario de conclusión → envía a API. *(Tipos de reclamo por definir.)*

→ `OperadorController` · `app/Services/ApiClient.php` · `app/Views/operador/`

### 5.3 Módulo Administrador
- Gestionar Usuario (CRUD + asignación de especialidad)

→ `AdministradorController` · `app/Views/administrador/`

### 5.4 Módulo Reportes
- Filtrar Reporte
- Visualizar Reporte
- Exportar Reporte

→ `ReporteController` · `app/Views/reportes/`

## 6. Requerimientos Funcionales

| Nro. | Requerimiento | Descripción |
|---|---|---|
| R1 | Gestionar Roles de Usuarios | Autenticar usuarios, permitir cierre de sesión y controlar acceso según roles fijos. |
| R2 | Gestionar reportes de socios | Mostrar las consultas realizadas por los socios a través del chatbot. |
| R3 | Visualizar trabajos pendientes | Mostrar al operador los trabajos pendientes según su especialidad, consumiendo datos de APIs externas (API de Reconexiones y API de Reclamos). |
| R4 | Concluir trabajos | Permitir al operador visualizar el detalle de un trabajo en un formulario (datos de API en solo lectura), completar campos adicionales y enviar la conclusión vía API externa. |
| R5 | Filtrar trabajos por especialidad | Cada operador visualiza únicamente los trabajos que corresponden a su especialidad asignada (Reconexión, Maestro de alcantarillado, Agua Potable). |

## 7. Requerimientos No Funcionales

| Nro. | Requerimiento | Descripción |
|---|---|---|
| R1 | Plataforma web | Funcional en navegador moderno, escritorio y móvil. |
| R2 | Seguridad y autenticación | Autenticación/autorización basada en roles fijos: Administrador, Supervisor, Operador. |
| R3 | Actualización rápida de reportes | Datos de reportes siempre actualizados. |
| R4 | Actualización rápida de trabajos | Trabajos de operadores actualizados en tiempo real. |
| R5 | Entorno de ejecución | Contenerizado mediante Docker / docker-compose (PHP+Apache y PostgreSQL), para garantizar una configuración uniforme entre todos los desarrolladores y el servidor de producción. |
| R6 | Integridad y consistencia | Registro de trabajos ejecutado de forma consistente (transaccional donde aplique). |

## 8. Roles del Sistema

Tres roles fijos, validados en cada petición vía `RoleMiddleware`:

- **Administrador** — gestión total: usuarios (con asignación de especialidad), reportes.
- **Supervisor** — mencionado en requerimientos no funcionales (R2); su alcance específico de permisos debe confirmarse con el usuario si no está claro en un caso de uso dado.
- **Operador** — visualiza y concluye trabajos pendientes de su especialidad. Cada operador tiene exactamente una especialidad: Reconexión, Maestro de alcantarillado o Agua Potable. Los datos de los trabajos provienen de APIs externas, no de la BD local.

### 8.1 Especialidades de Operador

La tabla `especialidad` es un catálogo fijo con tres valores. Cada usuario con rol Operador debe tener un `id_especialidad` asignado (columna `id_especialidad` en la tabla `usuario`, FK a `especialidad`). Relación: `usuario (N) → especialidad (1)`. La especialidad determina:
- Qué API se consulta al listar trabajos.
- Qué filtros se aplican sobre los datos recibidos.

| id | Especialidad | API consumida | Filtro |
|---|---|---|---|
| 1 | Reconexión | API de Reconexiones | Todas las reconexiones pendientes |
| 2 | Maestro de alcantarillado | API de Reclamos | Reclamos de tipo alcantarillado (tipos por definir) |
| 3 | Agua Potable | API de Reclamos | Reclamos de tipo agua potable (tipos por definir) |

## 9. Convenciones de Código para el Agente

- **Nombres de clases:** PascalCase (`TrabajoController`, `UsuarioModel`).
- **Nombres de métodos/variables:** camelCase.
- **Nombres de tablas SQL:** snake_case, en español, singular o plural consistente con el resto del esquema (confirmar convención existente antes de crear tablas nuevas).
- **Acceso a datos:** siempre vía PDO con consultas preparadas (prepared statements). Nunca concatenar variables directamente en SQL — riesgo de inyección.
- **Sesiones/autenticación:** manejar vía `$_SESSION`, validadas en `AuthMiddleware` antes de llegar a cualquier Controller protegido.
- **Vistas:** no incluir lógica de negocio ni consultas SQL en archivos de `Views/`. Las vistas solo reciben datos ya procesados del Controller.
- **Bootstrap:** reutilizar componentes del layout (`layouts/main.php`, `partials/`) en vez de duplicar markup de navbar/sidebar en cada vista.

## 10. Configuración del Entorno con Docker

- **`Dockerfile`** (raíz del proyecto)**:** basado en `php:7.3-apache`. Debe instalar y habilitar la extensión `pdo_pgsql` (no viene por defecto en la imagen oficial), típicamente vía `docker-php-ext-install pdo pdo_pgsql`. También instala Composer dentro de la imagen (o se copia el binario) para poder correr `composer install`.
- **`docker-compose.yml`:** define al menos dos servicios:
  - `app` (o `web`) — construido desde el `Dockerfile` en la raíz del proyecto, monta el código del proyecto como volumen, expone un puerto (ej. `8080:80`).
  - `db` — imagen oficial `postgres`, con variables de entorno para usuario/contraseña/nombre de base (tomadas de `.env`), y un volumen para persistir los datos entre reinicios.
- **`database/init.sql`:** se monta en el volumen `/docker-entrypoint-initdb.d/` del contenedor `db`. PostgreSQL lo ejecuta automáticamente **solo la primera vez** que se crea el volumen de datos (si el volumen ya existe, hay que borrarlo para que se re-ejecute).
- **Conexión entre contenedores:** desde `app/Config/database.php`, el host de conexión a PostgreSQL es el **nombre del servicio** en `docker-compose.yml` (ej. `db`), no `localhost` ni `127.0.0.1` — cada contenedor es su propia red interna.
- **`public/.htaccess`:** sigue siendo necesario porque Apache corre *dentro* del contenedor PHP; reescribe todas las peticiones hacia `index.php` (front controller).
- **Variables de entorno:** `.env` alimenta tanto a PHP (vía `app/Config/database.php`) como a `docker-compose.yml` (para las credenciales del servicio `db`), evitando duplicar valores sensibles en dos lugares.
- **Levantar el entorno:** `docker-compose up -d` levanta ambos servicios; `docker-compose down` los detiene. `composer install` se corre dentro del contenedor `app` (ej. `docker-compose exec app composer install`).

### 10.1 Conexión con pgAdmin 4 local e Inicialización de la Base de Datos

Para conectar un cliente local de PostgreSQL (como **pgAdmin 4**, **DBeaver** o **TablePlus**) al contenedor y ejecutar el script [`database/init.sql`](file:///c:/Proyectos/Cosmol_reportes/database/init.sql):

1. **Parámetros de Conexión en pgAdmin 4:**
   - **Host name / address:** `localhost` (o `127.0.0.1`)
   - **Port:** `5434` *(puerto externo expuesto en `docker-compose.yml`)*
   - **Maintenance database:** `cosmol_reportes` *(o el valor de `DB_NAME` en `.env`)*
   - **Username:** `admin` *(o el valor de `DB_USER` en `.env`)*
   - **Password:** `cosmol_12345` *(o el valor de `DB_PASSWORD` en `.env`)*

2. **Ejecución Manual del Script `init.sql` mediante pgAdmin 4:**
   - En pgAdmin 4, conectarse al servidor registrado y seleccionar la base de datos `cosmol_reportes`.
   - Abrir la herramienta **Query Tool** (Herramienta de Consultas).
   - Abrir o copiar el contenido del archivo [`database/init.sql`](file:///c:/Proyectos/Cosmol_reportes/database/init.sql).
   - Presionar **Execute / Refresh (F5)** para crear las tablas (`usuario`, `rol`, `consulta`, `tipo_consulta`) y sus restricciones.

3. **Inicialización Automática en Docker:**
   - El archivo `database/init.sql` se encuentra montado en el volumen `/docker-entrypoint-initdb.d/init.sql` del contenedor `db`.
   - Al ejecutar `docker-compose up -d` por primera vez (con volumen de datos limpio), PostgreSQL ejecutará automáticamente este script.

## 11. Integración con APIs Externas

### 11.1 Arquitectura de Consumo de APIs

El sistema de Reportes actúa como **cliente** de dos APIs externas (API de Reconexiones y API de Reclamos) que exponen los datos de trabajos. Los datos provienen de una base de datos PostgreSQL en un servidor de pruebas separado. El mapeo especialidad → API se detalla en §8.1.

**Formato de datos de entrada (ejemplo de reconexión recibida vía API):**
```json
{
    "usuario_registro": 2,
    "id_tipo_reclamo": 2,
    "descripcion": "Fuga de agua en el medidor",
    "ubicacion": "7.7.0.1",
    "zona": 7,
    "ruta": 7,
    "glosa": "El cliente reporta que el medidor gotea constantemente desde ayer.",
    "coordenadas_gps": "-17.3392,-63.2811"
}
```

### 11.2 Flujo de Conclusión de Trabajo

1. **Listado (GET):** `OperadorController` detecta la especialidad del operador en sesión → hace petición HTTP GET a la API correspondiente → recibe JSON → pasa datos a la vista de lista.
2. **Detalle/Formulario (GET):** El operador selecciona un trabajo → `OperadorController` consulta la API por ID → muestra formulario con campos de solo lectura (datos de la API) + campos editables (datos que el operador debe completar).
3. **Conclusión (POST):** El operador envía el formulario → `OperadorController` envía los datos editables vía HTTP POST a la API de guardado → la API guarda en la BD externa.

### 11.3 Implementación Técnica (PHP 7.3)

- Todas las peticiones HTTP a APIs externas se realizan con **cURL** (`curl_init`, `curl_setopt`, `curl_exec`).
- Se centraliza en `app/Services/ApiClient.php` para evitar duplicar código cURL.
- Las URLs base de las APIs se configuran en `app/Config/api.php` o en `.env`.
- En caso de error de la API (timeout, 500, etc.), el Controller debe manejar el error y mostrar un mensaje adecuado al usuario.

## 12. Reglas Explícitas para el Agente de IA

- ❌ No actualizar ni sugerir actualizar la versión de PHP.
- ❌ No introducir frameworks JS de frontend.
- ❌ No introducir frameworks PHP completos (Laravel, Symfony, CodeIgniter) — el proyecto usa MVC casero.
- ❌ No mezclar responsabilidades de módulos distintos en un mismo Controller/Model.
- ❌ No sugerir instalar PHP, Apache o PostgreSQL directamente en el sistema operativo (XAMPP, WAMP, etc.) — todo el entorno corre vía Docker.
- ❌ No usar `localhost`/`127.0.0.1` como host de base de datos en el código — debe ser el nombre del servicio de `docker-compose.yml`.
- ❌ No crear tablas locales para almacenar trabajos, reclamos o reconexiones — estos datos viven en la BD externa y se acceden vía API.
- ✅ Mantener toda lógica de acceso a datos **locales** dentro de `app/Models/`.
- ✅ Mantener toda lógica de consumo de **APIs externas** dentro de `app/Services/`.
- ✅ Validar rol, sesión y especialidad en cada ruta protegida del módulo operador, sin excepción.
- ✅ Si un requerimiento no está claro o falta información para implementarlo (ej. tipos de reclamo por especialidad, endpoints exactos de las APIs), preguntar antes de asumir.