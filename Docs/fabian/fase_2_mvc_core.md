# Fase 2 — Estructura MVC Core

> **Objetivo:** Tener el mini-framework MVC funcionando: que una URL como `/login` llegue al Controller correcto y renderice una vista.

**Prerequisitos:**
- Fase 1 completada (Docker levantado, base de datos conectada)

---
## Paso 2.1 — Configurar Composer (autoload PSR-4)

**Archivo:** `composer.json` (raíz del proyecto)

```json
{
    "name": "cosmol/reportes",
    "description": "Sistema de Visualización de Reportes y Gestión de Trabajos - COSMOL",
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

Esto permite que PHP encuentre automáticamente las clases por su namespace. Por ejemplo:
- `App\Core\Router` → busca en `app/Core/Router.php`
- `App\Controllers\AuthController` → busca en `app/Controllers/AuthController.php`

**Generación del autoloader:**
Ejecutar `composer install` (o `composer dump-autoload`) para generar la carpeta `vendor/` con el archivo `autoload.php`. Agregar `vendor/` al `.gitignore`.

### Verificación

- [x] `composer.json` existe con el autoload PSR-4 configurado
- [x] La carpeta `vendor/` fue generada con `autoload.php`
- [x] `vendor/` está en `.gitignore`

---

## Paso 2.2 — Crear el Front Controller

**Archivo:** `public/index.php` (reemplaza la página de prueba de la Fase 1)

Este es el **punto de entrada único** de la aplicación. Todas las peticiones HTTP pasan por aquí.

**Qué debe hacer (en orden):**

1. Definir una constante `BASE_PATH` que apunte a la raíz del proyecto (un nivel arriba de `public/`)
2. Cargar el autoload de Composer: `require BASE_PATH . '/vendor/autoload.php'`
3. Cargar las variables de entorno del archivo `.env` (leer el archivo y poblar las variables si no están seteadas)
4. Iniciar la sesión: `session_start()`
5. Instanciar `App\Core\Router`
6. Cargar las rutas desde `app/Config/routes.php`
7. Despachar la petición actual (el Router determina qué Controller::método ejecutar)

> **Nota sobre variables de entorno:** Como no usamos librerías externas pesadas, se implementa una función/helper simple para parsear `.env` línea por línea y registrar con `putenv()` / `$_ENV`.

### Verificación

- [x] `public/index.php` existe como front controller
- [x] Carga el autoload de Composer
- [x] Lee las variables de entorno del `.env`
- [x] Inicia sesión PHP
- [x] Instancia el Router y despacha la petición

---

## Paso 2.3 — Configurar Apache (rewrite)

**Archivo:** `public/.htaccess`

Este archivo le dice a Apache que redirija todas las peticiones hacia `index.php`, excepto archivos que ya existan físicamente (CSS, JS, imágenes).

**Contenido:**

```apache
RewriteEngine On

# Si el archivo o directorio solicitado existe físicamente, servirlo directamente
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Todo lo demás va a index.php
RewriteRule ^(.*)$ index.php [QSA,L]
```

**Resultado:**
- `localhost:8080/assets/css/app.css` → sirve el archivo CSS directamente
- `localhost:8080/login` → pasa por `index.php` → Router → AuthController
- `localhost:8080/operador/trabajos` → pasa por `index.php` → Router → OperadorController

### Verificación

- [x] `public/.htaccess` existe con las reglas de rewrite
- [x] `mod_rewrite` está habilitado en Apache
- [x] Las URLs amigables funcionan (no muestran `index.php` en la URL)

---

## Paso 2.4 — Crear las clases Core

Estas son las clases base del mini-framework MVC. Todas van en `app/Core/`.

### 2.4.1 — `app/Core/Database.php`

**Responsabilidad:** Singleton de conexión PDO a PostgreSQL.

**Qué debe hacer:**
- Leer la configuración de conexión desde `app/Config/database.php` (respaldada por las variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`)
- Crear una única instancia PDO (patrón Singleton) que se reutiliza en toda la aplicación
- Configurar PDO en modo de errores con excepciones (`PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`)
- Configurar el fetch mode por defecto como array asociativo (`PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`)

**Importante:** El host de conexión es `db` (nombre del servicio en la red de Docker), NO `localhost`.

### 2.4.2 — `app/Core/Model.php`

**Responsabilidad:** Clase base para todos los Models.

**Qué debe hacer:**
- Proporcionar un método protegido `db()` que retorna la instancia PDO desde `Database.php`
- Todos los Models del proyecto heredan de esta clase

### 2.4.3 — `app/Core/Controller.php`

**Responsabilidad:** Clase base para todos los Controllers.

**Qué debe hacer:**
- Método `view($viewName, $data = [])`: renderiza una vista dentro del layout principal
  - Extrae el array `$data` como variables individuales (con `extract()`)
  - Permite vistas con layout o sin layout (útil para login o respuestas JSON/parciales)
  - Incluye el archivo de vista correspondiente en `app/Views/`
- Método `redirect($url)`: redirige a otra URL con `header('Location: ...')`

### 2.4.4 — `app/Core/Router.php`

**Responsabilidad:** Resolver la URL solicitada al Controller y método correspondiente.

**Qué debe hacer:**
- Recibir las rutas definidas en `Config/routes.php`
- Parsear la URL actual (usando `$_SERVER['REQUEST_URI']`) y el método HTTP (`$_SERVER['REQUEST_METHOD']`)
- Buscar la ruta que coincida
- Si la ruta tiene middlewares definidos, ejecutarlos antes del Controller
- Instanciar el Controller correspondiente y llamar al método indicado
- Si la ruta no existe, mostrar un error 404 o renderizar una vista de error

**Formato de rutas en `Config/routes.php`:**

```php
return [
    'GET' => [
        '/login' => ['AuthController', 'showLogin', []],
        '/dashboard' => ['AuthController', 'dashboard', ['auth']],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login', []],
    ],
];
```

### Verificación

- [x] Las 4 clases Core existen en `app/Core/`
- [x] `Database.php` conecta correctamente a PostgreSQL
- [x] `Controller.php` puede renderizar una vista
- [x] `Router.php` resuelve URLs a Controllers

---

## Paso 2.5 — Crear archivos de configuración

### 2.5.1 — `app/Config/database.php`

Lee las variables de entorno y retorna un array con la configuración de conexión:

```php
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: '5432',
    'dbname' => getenv('DB_NAME') ?: 'cosmol_reportes',
    'user' => getenv('DB_USER') ?: 'cosmol_user',
    'password' => getenv('DB_PASSWORD') ?: 'cosmol_password',
];
```

> **Nota:** Este archivo es consumido por `Core/Database.php` para crear la conexión PDO.

### 2.5.2 — `app/Config/routes.php`

Define las rutas iniciales del sistema. Para esta fase, definimos rutas base:

```php
return [
    'GET' => [
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login', []],
    ],
];
```

Las rutas de los módulos (operador, administrador, reportes) se agregarán en fases posteriores.

### Verificación

- [x] `app/Config/database.php` retorna la configuración de conexión
- [x] `app/Config/routes.php` define las rutas iniciales
- [x] El Router las lee correctamente

---

## Verificación Final de la Fase 2

### Probar el flujo completo

1. **Acceder en el navegador a:**
   `http://localhost:8080/` o `http://localhost:8080/login`
   - Debe ejecutar el Router y despachar hacia `AuthController::showLogin`.

2. **Probar una ruta inexistente:**
   `http://localhost:8080/ruta-que-no-existe`
   - Debe responder con código de estado HTTP 404 (Página no encontrada).

3. **Probar la conexión a la base de datos desde el código:**
   Verificar que `\App\Core\Database::getInstance()` establece conexión PDO exitosamente con PostgreSQL:
   ```php
   $db = \App\Core\Database::getInstance();
   $stmt = $db->query("SELECT 1 AS conectado");
   $resultado = $stmt->fetch();
   // $resultado['conectado'] === 1
   ```

---

## Checklist Final

- [x] `composer.json` configurado con PSR-4 (`App\` -> `app/`)
- [x] `vendor/autoload.php` generado y `vendor/` en `.gitignore`
- [x] `public/index.php` funciona como front controller
- [x] `public/.htaccess` redirige todas las peticiones a `index.php`
- [x] `app/Core/Database.php` — Singleton PDO funcional
- [x] `app/Core/Model.php` — Clase base para Models
- [x] `app/Core/Controller.php` — Clase base con método `view()` y `redirect()`
- [x] `app/Core/Router.php` — Resuelve URLs a Controllers y maneja 404
- [x] `app/Config/database.php` — Configuración de conexión
- [x] `app/Config/routes.php` — Rutas iniciales definidas
- [x] El Router despacha correctamente una petición de prueba
- [x] La conexión PDO a PostgreSQL funciona desde el código

**Anterior:** [Fase 1 — Infraestructura Docker](fase_1_docker.md)
**Siguiente:** [Fase 3 — Layout Base y Assets](fase_3_layout.md)
