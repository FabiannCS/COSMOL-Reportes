# Documentación de Preparación y Despliegue en Producción (COSMOL-Reportes)

Este documento detalla todas las modificaciones, componentes de seguridad, optimizaciones de rendimiento y configuraciones de infraestructura Docker implementadas para poner en marcha el sistema **COSMOL-Reportes** en el servidor de producción (`chatbot.cosmol.com.bo` / IP `10.129.1.105`), garantizando la máxima seguridad, estabilidad y compatibilidad con **PHP 7.3**.

---

## 1. Resumen Ejecutivo de Cambios

Para que el sistema pase de un entorno de desarrollo a un entorno empresarial de producción, se implementaron 6 pilares fundamentales:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       ARQUITECTURA DE PRODUCCIÓN                        │
├───────────────────┬───────────────────┬─────────────────────────────────┤
│    SEGURIDAD      │    RENDIMIENTO    │         INFRAESTRUCTURA         │
├───────────────────┼───────────────────┼─────────────────────────────────┤
│ • Protección CSRF │ • OPcache Prod    │ • Dockerfile.prod Autónomo      │
│ • Hardening Cookie│ • Multi-cURL IPv4 │ • docker-compose.prod.yml       │
│ • Cabeceras HTTP  │ • GZIP + Caché    │ • Base de Datos Aislada (No 5432│
│ • Ocultar Errores │ • Autoload Optim. │ • Healthchecks y Auto-Restart   │
└───────────────────┴───────────────────┴─────────────────────────────────┘
```

---

## 2. Componentes Clave y Funcionamiento Técnico

### 2.1 Protección Integral contra Ataques CSRF (Cross-Site Request Forgery)

* **Problema que resuelve:** Evita que un atacante engañe a un usuario autenticado para que ejecute acciones no deseadas (como cambiar contraseñas, modificar permisos o concluir trabajos) mediante enlaces o peticiones forjadas desde otros sitios web.
* **Archivos implementados:**
  * [`app/Core/helpers.php`](file:///c:/Proyectos/Cosmol_reportes/app/Core/helpers.php): Contiene `csrfToken()` (genera un hash criptográfico de 32 bytes con `random_bytes()`), `csrfField()` (genera el `<input type="hidden" name="csrf_token" ...>`) y `verifyCsrfToken()` (compara tokens con `hash_equals()` resistente a ataques de temporización).
  * [`app/Middlewares/CsrfMiddleware.php`](file:///c:/Proyectos/Cosmol_reportes/app/Middlewares/CsrfMiddleware.php): Intercepta peticiones `POST`, `PUT`, `DELETE` y `PATCH`. Si el token no coincide o la sesión expiró, detiene la ejecución inmediatamente con código `HTTP 403 Forbidden` y renderiza una vista de aviso amigable.
  * [`app/Config/routes.php`](file:///c:/Proyectos/Cosmol_reportes/app/Config/routes.php): Registra el middleware `csrf` en todas las rutas web que procesan formularios.
  * **Vistas:** Se integró `<?= csrfField(); ?>` en los 11 formularios POST del sistema (Login, Usuarios, Roles, Permisos, y Conclusión de Trabajos).

### 2.2 Endurecimiento de Sesiones HTTP (Session Hardening)

* **Problema que resuelve:** Protege las galletas de sesión del usuario contra secuestro (Session Hijacking), inyecciones de scripts (XSS) y fijación de sesión.
* **Archivos implementados:**
  * [`public/index.php`](file:///c:/Proyectos/Cosmol_reportes/public/index.php): Antes de invocar `session_start()`, se configuran directivas estrictas mediante `session_set_cookie_params()`:
    * `httponly = true`: Impide que JavaScript en el navegador acceda a la cookie `PHPSESSID`.
    * `secure = true`: Se activa automáticamente cuando la petición ingresa por HTTPS (o proxy SSL), impidiendo el envío de la cookie por canales inseguros.
    * `samesite = Lax`: Restringe el envío de la cookie en peticiones cruzadas (cross-site).
    * `session.use_strict_mode = 1`: Impide que un usuario utilice un ID de sesión no inicializado por el servidor.
    * `lifetime = 0`: La sesión caduca automáticamente al cerrar por completo el navegador.

### 2.3 Cabeceras de Seguridad HTTP en Apache

* **Problema que resuelve:** Previene ataques de Clickjacking (embeber el sistema en iframes maliciosos), MIME-sniffing y filtración de tecnologías del servidor.
* **Archivos implementados:**
  * [`public/.htaccess`](file:///c:/Proyectos/Cosmol_reportes/public/.htaccess): Bajo el módulo `mod_headers`, se configuran las siguientes directivas:
    * `Strict-Transport-Security (HSTS)`: Fuerza al navegador a comunicarse exclusivamente mediante HTTPS durante 1 año (`max-age=31536000`).
    * `X-Frame-Options "SAMEORIGIN"`: Bloquea el renderizado del sistema en iframes externos.
    * `X-Content-Type-Options "nosniff"`: Obliga al navegador a respetar los tipos MIME declarados.
    * `X-XSS-Protection "1; mode=block"`: Habilita el filtro de protección XSS en navegadores legados.
    * `Referrer-Policy "strict-origin-when-cross-origin"`: Controla la información de procedencia enviada en cabeceras HTTP.
    * `Header always unset X-Powered-By`: Elimina la etiqueta que revela la versión de PHP.

### 2.4 Manejo Seguro de Errores en APIs (`/api/consultas`)

* **Problema que resuelve:** En producción no deben mostrarse trazas de error de base de datos ni excepciones PHP en respuestas JSON, ya que exponen nombres de tablas, columnas o credenciales.
* **Archivos implementados:**
  * [`app/Controllers/ConsultaApiController.php`](file:///c:/Proyectos/Cosmol_reportes/app/Controllers/ConsultaApiController.php): Captura excepciones en bloques `try/catch`. Si ocurre un fallo en la base de datos, lo escribe en el log interno del servidor (`error_log`) y retorna una respuesta JSON limpia (`{"status":"error","message":"Error interno al guardar"}`), a menos que `APP_DEBUG=true` esté explícitamente activado en desarrollo.

### 2.5 Infraestructura Docker para Producción

Se crearon archivos dedicados para producción, manteniendo los archivos originales para desarrollo local:

1. **[`docker/php-prod.ini`](file:///c:/Proyectos/Cosmol_reportes/docker/php-prod.ini):**
   * Desactiva la salida visual de errores: `display_errors = Off`, `display_startup_errors = Off`.
   * Habilita registro en log: `log_errors = On`, `error_log = /var/log/apache2/php_error.log`.
   * **OPcache de Alto Rendimiento:** `opcache.validate_timestamps=0` (el servidor almacena el bytecode precompilado en memoria RAM permanentemente, logrando la máxima velocidad de ejecución y menor consumo de CPU/RAM).
   * Oculta firma de PHP: `expose_php = Off`.

2. **[`Dockerfile.prod`](file:///c:/Proyectos/Cosmol_reportes/Dockerfile.prod):**
   * Imagen base `php:7.3-apache`.
   * Empaqueta e incluye directamente el código fuente dentro de la imagen (`COPY . /var/www/html`), haciendo el contenedor totalmente independiente de volúmenes locales.
   * Ejecuta `composer install --no-dev --optimize-autoloader` para generar un mapa estático de clases PHP, acelerando la carga de archivos.
   * Asigna permisos de propiedad al usuario del servidor web (`chown -R www-data:www-data /var/www/html`).

3. **[`docker-compose.prod.yml`](file:///c:/Proyectos/Cosmol_reportes/docker-compose.prod.yml):**
   * **Aislamiento de Base de Datos:** El servicio `db` (PostgreSQL) **no tiene sección `ports` expuesta al host**. La base de datos solo es accesible dentro de la red interna privada de Docker por el servicio `app`.
   * **Healthcheck de PostgreSQL:** El servicio `app` espera a que PostgreSQL pase la prueba `pg_isready -U admin -d cosmol_reportes` antes de iniciar Apache, evitando errores de conexión al arrancar el servidor.
   * **Política de Reinicio:** `restart: always` garantiza que ante un reinicio del servidor físico o del daemon de Docker, los contenedores suban automáticamente.

4. **[`.env.example`](file:///c:/Proyectos/Cosmol_reportes/.env.example):**
   * Plantilla con valores por defecto seguros (`APP_ENV=production`, `APP_DEBUG=false`) y documentación para el personal de TI de COSMOL.

---

## 3. Comparativa: Entorno de Desarrollo vs. Entorno de Producción

| Característica | Desarrollo (`docker-compose.yml`) | Producción (`docker-compose.prod.yml`) |
|---|---|---|
| **Carga de Código** | Montaje de volumen local (`.:/var/www/html`) | Código empaquetado en imagen (`COPY . /var/www/html`) |
| **Puerto PostgreSQL** | Expuesto al host (`5434:5432`) para pgAdmin | **Cerrado al exterior** (solo red interna de Docker) |
| **OPcache** | `validate_timestamps=1` (recarga cambios en vivo) | `validate_timestamps=0` (máxima velocidad en RAM) |
| **Visualización de Errores** | `display_errors = On` | `display_errors = Off` (registrados en `php_error.log`) |
| **Composer Autoloader** | Estándar PSR-4 | Optimizado con `--optimize-autoloader --no-dev` |
| **Política de Reinicio** | Por defecto | `restart: always` (alta disponibilidad) |

---

## 4. Guía de Despliegue en el Servidor de Producción

### Paso 1: Conectarse al Servidor y Actualizar el Código
```bash
cd /ruta/al/proyecto/cosmol-reportes
git pull origin devFabian
```

### Paso 2: Configurar el Archivo de Entorno (`.env`)
Si es la primera vez que se despliega:
```bash
cp .env.example .env
nano .env
```
Verificar que contenga los valores de producción:
```ini
DB_HOST=db
DB_PORT=5432
DB_NAME=cosmol_reportes
DB_USER=admin
DB_PASSWORD=ContraseñaSeguraPostgres

APP_PORT=8080
APP_ENV=production
APP_DEBUG=false

API_RECONEXIONES_URL=http://api.cosmol.com.bo/api-consultas
API_RECLAMOS_URL=http://api.cosmol.com.bo/api-consultas
API_FOTOS_URL=https://chatbot.cosmol.com.bo

REPORTES_API_TOKEN=TokenGeneradoParaChatbot
```

### Paso 3: Construir y Levantar los Contenedores de Producción
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

### Paso 4: Verificar Estado del Despliegue
```bash
docker-compose -f docker-compose.prod.yml ps
```
Ambos contenedores (`cosmol_reportes_app` y `cosmol_reportes_db`) deben figurar en estado `Up (healthy)`.

### Comandos de Diagnóstico y Mantenimiento Útiles

* **Ver registros en vivo de la aplicación:**
  ```bash
  docker-compose -f docker-compose.prod.yml logs -f app
  ```
* **Ver registros de PostgreSQL:**
  ```bash
  docker-compose -f docker-compose.prod.yml logs -f db
  ```
* **Acceder a la consola de PostgreSQL dentro del contenedor (sin exponer puertos):**
  ```bash
  docker exec -it cosmol_reportes_db psql -U admin -d cosmol_reportes
  ```
* **Reiniciar los servicios:**
  ```bash
  docker-compose -f docker-compose.prod.yml restart
  ```
* **Detener los servicios:**
  ```bash
  docker-compose -f docker-compose.prod.yml down
  ```
