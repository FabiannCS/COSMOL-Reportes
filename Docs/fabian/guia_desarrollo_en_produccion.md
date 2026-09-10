# Guía de Desarrollo, Modificación y Mantenimiento del Proyecto en Producción

> **COSMOL-Reportes**  
> Documento técnico y operativo para desarrolladores y administradores sobre cómo programar, probar, versionar y desplegar nuevas vistas, módulos o correcciones cuando el sistema ya se encuentra en funcionamiento en el servidor de producción.

---

## 1. Principio Fundamental: Ciclo de Vida del Software

Para mantener la estabilidad, seguridad y alta disponibilidad del sistema, **nunca se debe editar código directamente en el servidor de producción**. El flujo estándar es:

```
┌─────────────────────────┐       ┌─────────────────────────┐       ┌─────────────────────────┐
│   1. DESARROLLO LOCAL   │ ────> │       2. GIT HUB        │ ────> │ 3. SERVIDOR PRODUCCIÓN  │
│  (Docker en tu máquina) │       │ (Push a rama dev/main)  │       │   (Pull + Build + Run)  │
│  - Nuevas vistas/módulos│       │                         │       │  - Zero Downtime        │
│  - Pruebas en vivo      │       │                         │       │  - OPcache precompilado │
└─────────────────────────┘       └─────────────────────────┘       └─────────────────────────┘
```

### ¿Por qué en Producción se requiere Reconstruir (`--build`)?
A diferencia de desarrollo (donde el código se monta como volumen en tiempo real), en producción:
1. El código fuente se copia directamente **dentro de la imagen Docker** (`COPY . /var/www/html` en `Dockerfile.prod`).
2. **OPcache** tiene la validación de timestamps desactivada (`opcache.validate_timestamps=0`) para máxima velocidad de respuesta en RAM.
3. El autoloader de Composer se compila optimizado (`--optimize-autoloader --no-dev`).

Por tanto, cualquier cambio de código requiere reconstruir el contenedor con `docker-compose -f docker-compose.prod.yml up -d --build`.

---

## 2. Desarrollo de Nuevas Vistas y Módulos (Entorno Local)

Cuando necesites agregar una nueva pantalla o funcionalidad, sigue la arquitectura **MVC casera (PHP 7.3)** definida en [AGENTS.md](file:///c:/Proyectos/Cosmol_reportes/AGENTS.md):

### 2.1 Checklist de Desarrollo Paso a Paso

1. **Rutas ([app/Config/routes.php](file:///c:/Proyectos/Cosmol_reportes/app/Config/routes.php)):**
   - Define la URL, el método HTTP (`get`, `post`) y los middlewares de protección (`auth`, `role:...`, y `csrf` para peticiones POST):
   ```php
   $router->get('/modulo/nuevo', 'NuevoController@index', ['auth', 'role:Administrador']);
   $router->post('/modulo/guardar', 'NuevoController@guardar', ['auth', 'role:Administrador', 'csrf']);
   ```

2. **Controlador (`app/Controllers/`):**
   - Crea `NuevoController.php` extendiendo de `App\Core\Controller`.
   - Procesa parámetros de entrada y valida permisos.
   - Si consume **datos locales**: instancia el Modelo correspondiente.
   - Si consume **datos externos**: utiliza `App\Services\ApiClient`.
   - Renderiza la vista: `$this->view('modulo/index', $datos, 'main');`.

3. **Modelo (`app/Models/`) o Servicio (`app/Services/`):**
   - **Para PostgreSQL Local:** Crear clase en `app/Models/` extendiendo de `App\Core\Model` y usando consultas preparadas con PDO (`$this->db->prepare(...)`).
   - **Para APIs de Trabajos/Fotos:** Usar `ApiClient` (nunca crear tablas locales para reconexiones o reclamos).

4. **Vista (`app/Views/modulo/`):**
   - Crear el archivo `.php` (ej. `index.php`).
   - Usar clases de **Bootstrap 5** y escapar variables siempre con `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
   - **Importante:** En todos los formularios `<form method="POST">`, incluir obligatoriamente el campo CSRF:
     ```html
     <form action="/modulo/guardar" method="POST">
         <?= csrfField(); ?>
         <!-- Campos del formulario -->
     </form>
     ```

5. **Navegación ([app/Views/layouts/partials/sidebar.php](file:///c:/Proyectos/Cosmol_reportes/app/Views/layouts/partials/sidebar.php)):**
   - Agrega el enlace en el menú lateral filtrando por el rol autorizado.

---

## 3. Flujo de Despliegue de Cambios a Producción

Una vez que tus cambios fueron probados y funcionan correctamente en tu entorno local:

### Paso 1: Confirmar y Enviar Cambios a Git (Máquina de Desarrollo)
```bash
# 1. Verificar estado de archivos
git status

# 2. Agregar archivos modificados y nuevos
git add .

# 3. Crear commit descriptivo
git commit -m "feat: implementacion de nuevo modulo de reportes estadisticos"

# 4. Enviar a GitHub
git push origin devFabian
```

---

### Paso 2: Conectarse al Servidor de Producción
Accede por SSH al servidor de producción (`10.129.1.105` o dominio corporativo):
```bash
ssh usuario@10.129.1.105
cd /ruta/del/proyecto/cosmol-reportes
```

---

### Paso 3: Realizar Copia de Seguridad Preventiva de la BD (Recomendado)
Antes de actualizar código o estructura de base de datos en producción, genera un respaldo:
```bash
docker exec -t cosmol_reportes_db pg_dump -U admin -d cosmol_reportes > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

### Paso 4: Descargar los Nuevos Cambios
```bash
git pull origin devFabian
```

---

### Paso 5: Aplicar Cambios en la Base de Datos (Si Aplica)
> [!NOTE]
> Recuerda que `database/init.sql` solo se ejecuta la primera vez que se crea el volumen de PostgreSQL. Si tu cambio incluye una nueva tabla o columna, debes ejecutarla directamente en el contenedor sin reiniciar los datos:

Ejemplo para ejecutar una sentencia o archivo SQL nuevo:
```bash
docker exec -i cosmol_reportes_db psql -U admin -d cosmol_reportes -c "ALTER TABLE usuario ADD COLUMN telefono VARCHAR(20);"
```
O si tienes un script de migración:
```bash
cat database/migracion_2026_09.sql | docker exec -i cosmol_reportes_db psql -U admin -d cosmol_reportes
```

---

### Paso 6: Reconstruir y Reiniciar los Contenedores
Ejecuta la reconstrucción de la imagen para empaquetar el nuevo código PHP, compilar el autoloader y reiniciar OPcache:
```bash
docker-compose -f docker-compose.prod.yml up -d --build
```

---

### Paso 7: Verificación Inmediata Post-Despliegue
1. **Verificar que los contenedores estén sanos:**
   ```bash
   docker-compose -f docker-compose.prod.yml ps
   ```
   *Ambos deben mostrar estado `Up` y `(healthy)`.*

2. **Monitorear los registros de errores:**
   ```bash
   docker-compose -f docker-compose.prod.yml logs -f --tail=50 app
   ```

3. **Prueba funcional en el navegador:**
   - Iniciar sesión en `https://chatbot.cosmol.com.bo:8081` (con certificado SSL gestionado por Caddy).
   - Navegar al módulo nuevo/modificado.
   - Probar el envío de un formulario para verificar que el token CSRF y la sesión respondan correctamente.

---

## 4. Gestión de Base de Datos y Datos en Producción

### 4.1 Restaurar una Copia de Seguridad
En caso de requerir volver a un estado anterior:
```bash
cat backup_archivo.sql | docker exec -i cosmol_reportes_db psql -U admin -d cosmol_reportes
```

### 4.2 Inspeccionar la Base de Datos desde la Terminal del Servidor
Para hacer consultas directas sin exponer puertos externos:
```bash
docker exec -it cosmol_reportes_db psql -U admin -d cosmol_reportes
```
*(Para salir de psql escribir `\q` y presionar Enter)*.

---

## 5. Diagnóstico de Problemas Comunes en Producción

| Síntoma | Causa Probable | Solución |
|---|---|---|
| **Error 403 Forbidden al enviar un formulario** | Falta el token CSRF o la sesión expiró. | Asegurarse de que la vista incluya `<?= csrfField(); ?>` dentro del `<form>`. |
| **Los cambios de código PHP no se reflejan** | OPcache tiene la versión previa en memoria. | Ejecutar `docker-compose -f docker-compose.prod.yml up -d --build`. |
| **Error de conexión con la BD (`Connection refused`)** | El servicio `db` aún está iniciando o el healthcheck falló. | Revisar con `docker-compose -f docker-compose.prod.yml logs db`. |
| **Error 500 en blanco sin detalles** | En producción `display_errors` está apagado por seguridad. | Ver el registro real con `docker-compose -f docker-compose.prod.yml logs app`. |
| **Clase PHP no encontrada (`Class not found`)** | Falta actualizar el mapa de clases de Composer. | Correr `--build` en el docker-compose o ejecutar `docker exec -it cosmol_reportes_app composer dump-autoload -o`. |

---

## 6. Reglas de Oro para el Mantenimiento

1. 🚫 **No cambiar la versión de PHP:** El sistema debe mantenerse estrictamente en **PHP 7.3**.
2. 🚫 **No modificar `docker-compose.prod.yml` para exponer el puerto 5432:** La base de datos debe permanecer aislada dentro de la red interna de Docker.
3. 🚫 **No guardar datos de trabajos en PostgreSQL local:** Todos los reclamos y reconexiones se gestionan exclusivamente a través de las APIs externas.
4. ✅ **Siempre probar localmente antes de hacer `git push` a la rama de producción.**
5. ✅ **Siempre realizar un backup de la BD antes de alterar tablas existentes.**
