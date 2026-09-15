# Refactorización del Control de Acceso (RBAC a PBAC)

El sistema actualmente cuenta con un módulo de **Roles y Permisos**, donde se le pueden asignar permisos dinámicos a cualquier rol (ej. darle al Supervisor permiso para ver usuarios). Sin embargo, el código del sistema (rutas, barra lateral y controladores) actualmente sólo verifica el **nombre del rol** (ej. "Administrador" o "Supervisor"), ignorando los permisos asignados en la base de datos. 

Para solucionar esto y hacer que el sistema respete los permisos asignados dinámicamente desde la interfaz, debemos migrar el sistema de autorización para basarlo en permisos.

## Cambios Propuestos

### 1. Modelo `Permiso` y `AuthController`
- **[MODIFICAR] [Permiso.php](file:///c:/Proyectos/Cosmol_reportes/app/Models/Permiso.php):**
  Añadir un método `getClavesByRol($idRol)` que retorne un arreglo con los nombres clave de los permisos (ej. `['usuarios.ver', 'trabajos.concluir']`) asignados a un rol.
- **[MODIFICAR] [AuthController.php](file:///c:/Proyectos/Cosmol_reportes/app/Controllers/AuthController.php):**
  Al momento de hacer login, obtener los permisos del usuario e inyectarlos en `$_SESSION['usuario']['permisos']`.

### 2. Middleware de Permisos
- **[NUEVO] [PermissionMiddleware.php](file:///c:/Proyectos/Cosmol_reportes/app/Middlewares/PermissionMiddleware.php):**
  Crear un middleware que reciba el nombre del permiso requerido y verifique si existe en `$_SESSION['usuario']['permisos']`. Si el usuario no tiene el permiso, se bloqueará el acceso (error 403).

### 3. Rutas
- **[MODIFICAR] [routes.php](file:///c:/Proyectos/Cosmol_reportes/app/Config/routes.php):**
  Cambiar la protección de las rutas de administrador/supervisor para usar el nuevo middleware de permisos en lugar del de roles duros.
  Por ejemplo:
  De `['auth', 'role:Administrador']` a `['auth', 'permission:usuarios.ver']`.

### 4. Menú Lateral (Sidebar)
- **[MODIFICAR] [sidebar.php](file:///c:/Proyectos/Cosmol_reportes/app/Views/layouts/partials/sidebar.php):**
  Reemplazar las condiciones rígidas como `if ($rolActual === 'Administrador')` por verificaciones dinámicas usando una pequeña función de ayuda (`in_array('usuarios.ver', $_SESSION['usuario']['permisos'])`). Esto hará que las opciones del menú aparezcan automáticamente cuando se asigne el permiso al rol.

### 5. Controladores
- **[MODIFICAR] [AdministradorController.php](file:///c:/Proyectos/Cosmol_reportes/app/Controllers/AdministradorController.php), [UsuarioController.php](file:///c:/Proyectos/Cosmol_reportes/app/Controllers/UsuarioController.php), etc:**
  Eliminar el método rígido `verificarAdmin()` y, si es necesario verificar permisos dentro de los controladores, hacerlo revisando la sesión de permisos.

> [!WARNING]
> **Cierre de sesión automático:**
> Una vez aplicados estos cambios, **todos los usuarios activos necesitarán cerrar e iniciar sesión nuevamente** para que el sistema cargue sus permisos actualizados en memoria (sesión).

## Plan de Verificación

### Pruebas Manuales
- **Login:** Verificar que al iniciar sesión, el arreglo `$_SESSION['usuario']['permisos']` se carga correctamente con las claves correspondientes al rol en la base de datos.
- **Renderizado del menú:** Iniciar sesión como Supervisor y verificar que, si se le asigna el permiso `usuarios.ver`, la pestaña de "Gestión de Personal" aparece mágicamente en el menú (tras volver a iniciar sesión).
- **Bloqueo por URL:** Intentar ingresar a una ruta sin el permiso correspondiente modificando la URL directamente (ej. `/administrador/usuarios`) y verificar que salte la pantalla de Acceso Denegado (403).
