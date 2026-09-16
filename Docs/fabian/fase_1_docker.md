# Fase 1 — Infraestructura Docker

> **Objetivo:** Levantar el entorno de desarrollo con `docker-compose up -d` y ver una página PHP funcionando en `localhost:8080`.

**Prerequisitos:**
- Docker Desktop instalado y corriendo
- pgAdmin4 instalado (para administrar la base de datos visualmente)

---

## Paso 1.1 — Configurar variables de entorno

**Archivos a crear/modificar:** `.env.example` y `.env`

Primero definir las variables en `.env.example` (este archivo SÍ va al repositorio como referencia):

```env
# Base de datos PostgreSQL
DB_HOST=db
DB_PORT=5432
DB_NAME=cosmol_reportes
DB_USER=cosmol_user
DB_PASSWORD=cosmol_password

# Aplicación
APP_PORT=8080
APP_ENV=development
APP_DEBUG=true
```

Luego copiar `.env.example` a `.env` y ajustar los valores si es necesario:

```bash
cp .env.example .env
```

> **Importante:** `DB_HOST=db` porque es el nombre del servicio en `docker-compose.yml`, NO `localhost`. Dentro de la red de Docker, los contenedores se comunican por nombre de servicio.

### Verificación

- [x] `.env.example` tiene todas las variables documentadas
- [x] `.env` existe con valores de desarrollo
- [x] `.env` está en `.gitignore`

---

## Paso 1.2 — Crear el Dockerfile

**Archivo:** `Dockerfile` (raíz del proyecto)

Este archivo define cómo construir la imagen del contenedor de la aplicación PHP+Apache.

**Qué debe hacer:**

1. Partir de la imagen base `php:7.3-apache`
2. Configurar los repositorios de Debian Bullseye por HTTPS en `/etc/apt/sources.list` (evitando bloqueos de HTTP/puerto 80 en redes corporativas)
3. Instalar `libpq-dev` mediante `apt-get`
4. Instalar y habilitar extensiones PHP:
   - `pdo` (base para acceso a datos)
   - `pdo_pgsql` (driver de PostgreSQL para PDO)
5. Habilitar `mod_rewrite` de Apache (necesario para las URLs amigables del `.htaccess`)
6. Configurar el `DocumentRoot` de Apache apuntando a `/var/www/html/public`
   - Solo la carpeta `public/` debe ser accesible desde el navegador
   - La carpeta `app/` queda fuera del document root por seguridad
7. Habilitar `AllowOverride All` en `/var/www/html/public` para que el `.htaccess` funcione
8. Instalar Composer dentro de la imagen (copiar binario desde la imagen oficial `composer:latest`)
9. Establecer el directorio de trabajo en `/var/www/html`

**Estructura esperada dentro del contenedor:**

```
/var/www/html/              ← directorio de trabajo
├── public/                 ← DocumentRoot de Apache
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── app/                    ← NO accesible por URL
├── composer.json
├── .env
└── ...
```

### Verificación

- [x] El Dockerfile existe en la raíz del proyecto
- [x] Usa la imagen `php:7.3-apache` (no otra versión)
- [x] Instala `pdo` y `pdo_pgsql`
- [x] Habilita `mod_rewrite`
- [x] El DocumentRoot apunta a `public/`
- [x] `AllowOverride All` habilitado en `public/`

---

## Paso 1.3 — Crear docker-compose.yml

**Archivo:** `docker-compose.yml` (raíz del proyecto)

Define los servicios que componen el entorno de desarrollo.

### Servicio `app` (PHP + Apache)

| Propiedad | Valor | Notas |
|---|---|---|
| Build | `context: .` / `dockerfile: Dockerfile` | Construye la imagen desde la raíz |
| Container name | `cosmol_app` | Nombre fijo para identificarlo |
| Puertos | `8080:80` | Acceso vía `localhost:8080` |
| Volúmenes | `.:/var/www/html` | Monta el código para desarrollo (hot-reload) |
| Dependencias | `db` | Espera a que el servicio PostgreSQL esté disponible |
| Variables de entorno | Desde `.env` | Usa `env_file: .env` |

### Servicio `db` (PostgreSQL)

| Propiedad | Valor | Notas |
|---|---|---|
| Imagen | `postgres:16` | Imagen oficial LTS, sin Dockerfile custom |
| Container name | `cosmol_db` | Nombre fijo para identificarlo |
| Puertos | `5432:5432` | Expuesto para conectar pgAdmin4 desde el host |
| Variables de entorno | `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` | Tomadas del `.env` vía `${VAR}` |
| Volúmenes | `pgdata:/var/lib/postgresql/data` | Volumen nombrado para persistir datos |

### Red interna

Docker Compose crea automáticamente una red interna. Los contenedores se comunican por nombre de servicio:
- Desde `app` → la base de datos está en el host `db` (no `localhost`)
- Desde tu máquina (pgAdmin4) → la base de datos está en `localhost:5432`

### Verificación

- [x] `docker-compose.yml` define los servicios `app` y `db`
- [x] El puerto del servicio `app` es `8080:80`
- [x] El servicio `db` usa `postgres:16`
- [x] El servicio `db` expone el puerto `5432` para pgAdmin4
- [x] Las credenciales vienen del `.env` con sintaxis `${VAR}`
- [x] Existe un volumen nombrado `pgdata`

---

## Paso 1.4 — Crear página de prueba

**Archivo:** `public/index.php` (temporal, se reemplazará en la Fase 2)

Crear un archivo PHP mínimo que:
1. Muestre "COSMOL Reportes - Entorno funcionando"
2. Pruebe la conexión PDO a PostgreSQL
3. Muestre la versión de PHP para confirmar que es 7.3

Esto sirve únicamente para verificar que Docker está bien configurado. Se reemplazará por el front controller real en la Fase 2.

### Verificación

- [x] `public/index.php` existe con contenido de prueba

---

## Verificación Final de la Fase 1

### Levantar el entorno

```bash
# Construir y levantar los contenedores
docker-compose up -d --build

# Verificar que ambos contenedores estén corriendo
docker-compose ps
```

**Resultado esperado:** ambos servicios (`app` y `db`) en estado `Up`.

### Probar la aplicación

```bash
# Acceder en el navegador a:
# http://localhost:8080
# Debe mostrar la página de prueba con la versión de PHP (7.3.x)
# y la conexión exitosa a PostgreSQL
```

### Conectar pgAdmin4

Abrir pgAdmin4 y crear una nueva conexión a PostgreSQL:

| Campo | Valor |
|---|---|
| Host | `localhost` |
| Puerto | `5432` |
| Base de datos | `cosmol_reportes` (según `DB_NAME` en `.env`) |
| Usuario | `cosmol_user` (según `DB_USER` en `.env`) |
| Contraseña | `cosmol_password` (según `DB_PASSWORD` en `.env`) |

**Resultado esperado:** pgAdmin4 conecta exitosamente a la base de datos local y queda lista para crear y gestionar las tablas directamente desde su interfaz gráfica o herramienta de consultas.

---

## Checklist Final

- [x] `.env.example` y `.env` configurados
- [x] `Dockerfile` creado y funcional
- [x] `docker-compose.yml` con servicios `app` y `db`
- [x] `public/index.php` con página de prueba
- [x] `docker-compose up -d` levanta ambos contenedores sin errores
- [x] `localhost:8080` muestra la página de prueba
- [x] pgAdmin4 conecta a la base de datos en `localhost:5432`
- [x] La versión de PHP es 7.3

**Siguiente paso:** [Fase 2 — Estructura MVC Core](fase_2_mvc_core.md)
