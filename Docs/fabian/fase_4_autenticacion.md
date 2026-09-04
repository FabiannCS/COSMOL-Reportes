# Fase 4 — Autenticación y Middlewares

> **Objetivo:** Implementar login, manejo de sesiones y control de acceso por rol. Al terminar esta fase, solo usuarios autenticados podrán acceder al sistema.

**Prerequisitos:**
- Fase 3 completada (Layout de dashboard funcionando)
- Base de datos con tablas de usuarios y roles con datos iniciales

---

## Paso 4.1 — Crear el Model de Usuario

**Archivo:** `app/Models/Usuario.php`

**Responsabilidad:** Acceso a datos de la tabla de usuarios.

**Métodos necesarios:**

| Método | Qué hace |
|---|---|
| `findByEmail($email)` | Busca un usuario por email. Retorna el registro completo (incluyendo password hash y nombre del rol). |
| `findById($id)` | Busca un usuario por ID. Útil para cargar datos del usuario en sesión. |

**Reglas:**
- Hereda de `App\Core\Model`
- Todas las consultas usan **prepared statements** (nunca concatenar variables en SQL)
- Hace JOIN con la tabla de roles para traer el nombre del rol directamente

**Ejemplo de consulta preparada (PHP 7.3):**

```php
$stmt = $this->db()->prepare(
    "SELECT u.*, r.nombre as rol 
     FROM usuarios u 
     INNER JOIN roles r ON u.rol_id = r.id 
     WHERE u.email = :email AND u.activo = true"
);
$stmt->execute(['email' => $email]);
return $stmt->fetch();
```

### Verificación

- [ ] `app/Models/Usuario.php` existe y hereda de `Model`
- [ ] `findByEmail()` busca usuario con su rol
- [ ] Usa prepared statements en todas las consultas

---

## Paso 4.2 — Crear el Controller de Autenticación

**Archivo:** `app/Controllers/AuthController.php`

**Responsabilidad:** Manejar login, logout y la página inicial post-login.

**Métodos:**

### `showLogin()`
- Si el usuario **ya tiene sesión activa**, redirigir al dashboard
- Si no, renderizar la vista `auth/login.php`

### `login()`
- Recibir `$_POST['email']` y `$_POST['password']`
- Validar que los campos no estén vacíos
- Buscar al usuario con `Usuario::findByEmail($email)`
- Verificar la contraseña con `password_verify($password, $user['password'])`
- Si las credenciales son correctas:
  - Guardar datos del usuario en `$_SESSION['usuario']` (id, nombre, email, rol)
  - **No guardar el password en sesión**
  - Redirigir al dashboard según el rol
- Si las credenciales son incorrectas:
  - Volver al login con un mensaje de error
  - **No revelar si el email existe o no** (mensaje genérico: "Credenciales incorrectas")

### `logout()`
- Destruir la sesión: `session_destroy()`
- Redirigir a `/login`

**Reglas:**
- Hereda de `App\Core\Controller`
- Las contraseñas se almacenan con `password_hash($password, PASSWORD_DEFAULT)` en la base de datos
- Se verifican con `password_verify()` (nunca comparar hashes directamente)

### Verificación

- [ ] `app/Controllers/AuthController.php` existe con los 3 métodos
- [ ] `showLogin()` renderiza el formulario
- [ ] `login()` valida credenciales y crea sesión
- [ ] `logout()` destruye la sesión
- [ ] No se almacena el password en sesión

---

## Paso 4.3 — Crear la vista de Login

**Archivo:** `app/Views/auth/login.php`

**Responsabilidad:** Formulario de login.

**Diseño:**
- Página centrada (sin sidebar ni navbar)
- No usa el layout `main.php` (el login tiene su propio layout limpio)
- Formulario con Bootstrap:
  - Campo de email
  - Campo de password
  - Botón "Iniciar Sesión"
  - Mensaje de error condicional (si las credenciales fallaron)

**Estructura visual:**

```
┌──────────────────────────────────────┐
│                                      │
│                                      │
│         ┌──────────────────┐         │
│         │   COSMOL          │         │
│         │   Reportes        │         │
│         │                  │         │
│         │  Email:          │         │
│         │  [____________]  │         │
│         │                  │         │
│         │  Contraseña:     │         │
│         │  [____________]  │         │
│         │                  │         │
│         │  [Iniciar Sesión]│         │
│         │                  │         │
│         └──────────────────┘         │
│                                      │
└──────────────────────────────────────┘
```

**Componentes Bootstrap sugeridos:**
- `container d-flex justify-content-center align-items-center` (centrado vertical y horizontal)
- `card` para el formulario
- `form-group`, `form-control` para los campos
- `btn btn-primary` para el botón
- `alert alert-danger` para mensajes de error

### Verificación

- [ ] `app/Views/auth/login.php` existe con el formulario
- [ ] El formulario envía por POST a `/login`
- [ ] Muestra mensajes de error cuando las credenciales fallan
- [ ] No usa el layout principal (tiene su propio diseño)
- [ ] Es responsivo

---

## Paso 4.4 — Crear los Middlewares

### 4.4.1 — `app/Middlewares/AuthMiddleware.php`

**Responsabilidad:** Verificar que el usuario tenga una sesión activa.

**Lógica:**
1. Verificar si existe `$_SESSION['usuario']`
2. Si **no existe**: redirigir a `/login` y detener la ejecución (`exit`)
3. Si **existe**: permitir que la petición continúe

**Uso:** Se aplica a **todas las rutas protegidas** (todo excepto `/login`).

### 4.4.2 — `app/Middlewares/RoleMiddleware.php`

**Responsabilidad:** Verificar que el rol del usuario tenga permiso para acceder a la ruta solicitada.

**Lógica:**
1. Recibir la lista de roles permitidos para la ruta actual
2. Verificar si `$_SESSION['usuario']['rol']` está en esa lista
3. Si **no tiene permiso**: mostrar error 403 (Acceso denegado) o redirigir
4. Si **tiene permiso**: permitir que la petición continúe

**Ejemplo de uso en rutas:**

```php
// Config/routes.php
'/administrador/usuarios' => ['AdministradorController', 'usuarios', ['auth', 'role:Administrador']],
'/operador/trabajos' => ['OperadorController', 'trabajos', ['auth', 'role:Operador,Administrador']],
```

### Verificación

- [ ] `AuthMiddleware.php` redirige a `/login` si no hay sesión
- [ ] `RoleMiddleware.php` valida el rol del usuario contra los roles permitidos
- [ ] Los middlewares se ejecutan **antes** del Controller

---

## Paso 4.5 — Integrar middlewares en el Router

**Archivo a modificar:** `app/Core/Router.php`

El Router debe procesar los middlewares definidos en cada ruta **antes** de ejecutar el Controller.

**Flujo actualizado del Router:**

```
URL solicitada
     │
     ▼
Buscar ruta en Config/routes.php
     │
     ▼
¿Tiene middlewares?
     │
  Sí ─────────────────────────┐
     │                         │
     ▼                         ▼
Ejecutar 'auth'           Ejecutar 'role:X'
(AuthMiddleware)          (RoleMiddleware)
     │                         │
     │◄────────────────────────┘
     ▼
Instanciar Controller
     │
     ▼
Ejecutar método
```

**Rutas públicas vs protegidas:**

| Ruta | Middlewares | Resultado |
|---|---|---|
| `/login` (GET/POST) | `[]` (ninguno) | Acceso libre |
| `/logout` | `['auth']` | Solo usuarios con sesión |
| `/dashboard` | `['auth']` | Solo usuarios con sesión |
| `/administrador/*` | `['auth', 'role:Administrador']` | Solo administradores |
| `/operador/*` | `['auth', 'role:Operador,Administrador']` | Operadores y administradores |

### Verificación

- [ ] El Router ejecuta middlewares antes del Controller
- [ ] Las rutas sin middleware son accesibles libremente
- [ ] Las rutas con `auth` requieren sesión activa
- [ ] Las rutas con `role:X` requieren el rol específico

---

## Paso 4.6 — Actualizar las rutas iniciales

**Archivo:** `app/Config/routes.php`

Agregar las rutas de autenticación completas:

```php
return [
    'GET' => [
        '/' => ['AuthController', 'showLogin', []],
        '/login' => ['AuthController', 'showLogin', []],
        '/logout' => ['AuthController', 'logout', ['auth']],
        '/dashboard' => ['DashboardController', 'index', ['auth']],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login', []],
    ],
];
```

> **Nota:** El `DashboardController` se puede crear como una vista simple que muestre "Bienvenido, [nombre del usuario]" para verificar que el login funciona.

### Verificación

- [ ] Rutas de login/logout definidas
- [ ] Ruta raíz (`/`) redirige al login
- [ ] Ruta `/dashboard` requiere autenticación

---

## Verificación Final de la Fase 4

### Flujo completo de prueba

```
1. Abrir http://localhost:8080/
   → Debe redirigir a /login (o mostrar el formulario)

2. Intentar acceder a http://localhost:8080/dashboard sin sesión
   → Debe redirigir a /login (middleware AuthMiddleware)

3. Hacer login con credenciales incorrectas
   → Debe volver al login con mensaje "Credenciales incorrectas"

4. Hacer login con el usuario administrador de init.sql
   → Debe redirigir al dashboard
   → La sesión debe contener los datos del usuario

5. Verificar que el navbar muestra el nombre del usuario

6. Hacer logout
   → Debe destruir la sesión y redirigir a /login

7. Intentar acceder a /dashboard después del logout
   → Debe redirigir a /login
```

### Verificar en pgAdmin4

Conectar a la base de datos y verificar:
- La tabla de usuarios tiene al menos un registro (el admin de `init.sql`)
- El campo de password contiene un hash (no texto plano)
- El campo `activo` es `true` para el admin

---

## Checklist Final

- [ ] `app/Models/Usuario.php` — Model con `findByEmail()` y `findById()`
- [ ] `app/Controllers/AuthController.php` — Login, logout funcionales
- [ ] `app/Views/auth/login.php` — Formulario de login con Bootstrap
- [ ] `app/Middlewares/AuthMiddleware.php` — Valida sesión activa
- [ ] `app/Middlewares/RoleMiddleware.php` — Valida rol del usuario
- [ ] `app/Core/Router.php` — Ejecuta middlewares antes del Controller
- [ ] `app/Config/routes.php` — Rutas de auth definidas
- [ ] El flujo login → dashboard → logout funciona correctamente
- [ ] Usuarios sin sesión no pueden acceder a rutas protegidas
- [ ] Las contraseñas se almacenan hasheadas y se verifican con `password_verify()`

---

## ¿Qué sigue?

Con las 4 fases completadas, el sistema tiene:
- Entorno Docker reproducible
- MVC funcional con routing
- Layout de dashboard con Bootstrap
- Autenticación y control de acceso por rol
- Base de datos con esquema inicial

**Siguiente etapa:** Implementar los módulos funcionales según el `AGENTS.md`:
1. Módulo Operador (trabajos, observaciones, historial)
2. Módulo Administrador (gestión de usuarios, trabajos, estados)
3. Módulo Reportes (filtrar, visualizar, exportar)

**Anterior:** [Fase 3 — Layout Base y Assets](fase_3_layout.md)
