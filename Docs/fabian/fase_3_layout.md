# Fase 3 — Layout Base y Assets

> **Objetivo:** Tener el layout de dashboard con Bootstrap 5 listo, seguro y modular para recibir el contenido de todas las vistas del sistema.

**Prerequisitos:**
- Fase 2 completada (MVC Core funcionando, Front Controller y Router operativos)

---

## Paso 3.1 — Descargar Bootstrap (Assets locales)

**Directorio:** `public/assets/vendor/bootstrap/`

Colocar Bootstrap **compilado** (CSS + JS) directamente en la carpeta de assets vendor. No se utiliza CDN para garantizar funcionamiento sin conexión a internet y consistencia en el entorno.

### Estructura de archivos Bootstrap

```
public/assets/vendor/bootstrap/
├── css/
│   └── bootstrap.min.css
└── js/
    └── bootstrap.bundle.min.js        ← incluye Popper.js integrado
```

> **Nota:** Usar `bootstrap.bundle.min.js` asegura compatibilidad con dropdowns, tooltips, popovers y el componente Offcanvas sin requerir dependencias externas.

### Verificación

- [x] Los archivos de Bootstrap están en `public/assets/vendor/bootstrap/`
- [x] Se descargó la versión compilada (minificada)
- [x] Se incluye `bootstrap.bundle.min.js` (con Popper.js)

---

## Paso 3.2 — Crear el layout principal y partials

El layout principal es la estructura HTML base que envuelve las vistas del sistema, organizada con **HTML5 semántico** (`<header>`, `<nav>`, `<aside>`, `<main>`, `<footer>`) y aplicando **buenas prácticas de seguridad (escape de salida contra XSS)**.

### 3.2.1 — `app/Views/layouts/main.php`

**Responsabilidad:** Estructura HTML base del dashboard.

**Estructura y seguridad:**
- Escape estricto de variables en `<head>`: `htmlspecialchars($title ?? 'COSMOL Reportes', ENT_QUOTES, 'UTF-8')`.
- Inclusión modular de partials (`navbar.php`, `sidebar.php`, `footer.php`).
- Inyección del contenido de la vista en el área principal `<main class="app-content">`.

```
┌─────────────────────────────────────────────────────────┐
│              HEADER / NAVBAR (<header>)                 │
│  Logo COSMOL    |    Nombre usuario (seguro) | Logout   │
├──────────────┬──────────────────────────────────────────┤
│              │                                          │
│  ASIDE       │         MAIN CONTENT (<main>)            │
│  (Sidebar)   │         <?= $content ?>                  │
│  Opciones    │         (Inyección de la vista activa)   │
│  por Rol     │                                          │
│              │                                          │
├──────────────┴──────────────────────────────────────────┤
│                    FOOTER (<footer>)                    │
└─────────────────────────────────────────────────────────┘
```

### 3.2.2 — `app/Views/layouts/partials/navbar.php`

**Responsabilidad:** Barra de navegación superior.

**Buenas prácticas de seguridad y accesibilidad:**
- Logo y nombre del sistema "COSMOL Reportes".
- Botón toggle accesible para colapsar/expandir el sidebar en dispositivos móviles.
- Renderizado seguro de los datos de usuario mediante `htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8')`.
- Enlace al cierre de sesión apuntando a la ruta `/logout`.

### 3.2.3 — `app/Views/layouts/partials/sidebar.php`

**Responsabilidad:** Menú lateral de navegación con control por roles y estado activo.

**Buenas prácticas de programación:**
- **Validación defensiva de sesión:** Verificar la existencia de la sesión antes de consultar el rol:
  ```php
  <?php 
  $rolActual = isset($_SESSION['usuario']['rol']) ? $_SESSION['usuario']['rol'] : ''; 
  $currentUri = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';
  ?>
  ```
- **Resaltado de ruta activa:** Aplicar la clase `.active` de Bootstrap comparando `$currentUri`.
- **Opciones por rol:**

| Administrador | Operador |
|---|---|
| Dashboard (`/dashboard`) | Dashboard (`/dashboard`) |
| Usuarios (`/seguridad/usuarios`) | Mis Trabajos (`/operador/trabajos`) |
| Trabajos (`/administrador/trabajos`) | Registrar Trabajo (`/operador/registrar`) |
| Registrar Trabajo Pendiente (`/administrador/trabajos/crear`) | Historial (`/operador/historial`) |
| Estados (`/administrador/estados`) | |
| Reportes (`/reportes/visualizar`) | |

### 3.2.4 — `app/Views/layouts/partials/footer.php`

**Responsabilidad:** Pie de página común.
- Texto: "© 2026 COSMOL — Sistema de Reportes y Gestión de Trabajos"
- Diseño limpio y semántico con `<footer>`.

### Verificación

- [x] `main.php` renderiza la estructura completa del dashboard con HTML5 semántico
- [x] `navbar.php` muestra el nombre del sistema y escapa de forma segura el usuario en sesión
- [x] `sidebar.php` valida el rol de forma defensiva sin generar notices en PHP 7.3
- [x] `footer.php` muestra el copyright
- [x] Los partials se incluyen correctamente dentro de `main.php`

---

## Paso 3.3 — Crear hojas de estilo y scripts propios

### 3.3.1 — `public/assets/css/app.css`

**Responsabilidad:** Estilos complementarios que no colisionan con Bootstrap.

**Qué debe incluir:**
1. **Variables CSS corporativas:** Colores principales de COSMOL (`--cosmol-primary`, `--cosmol-sidebar-bg`, etc.).
2. **Sidebar:**
   - Ancho definido (ej. `250px`).
   - Altura de viewport adaptable.
   - Transición suave para apertura/cierre en móvil.
3. **Área de contenido principal (`.app-content`):**
   - Ajuste automático de margen según el sidebar.
   - Espaciado interior adecuado (`padding: 1.5rem`).
4. **Responsive:**
   - En pantallas pequeñas (`< 992px`), el sidebar se comporta como offcanvas/drawer sin romper el flujo del contenido.

### 3.3.2 — `public/assets/js/app.js`

**Responsabilidad:** Script ligero para el toggle del sidebar en pantallas móviles y comportamiento dinámico básico.

### Verificación

- [x] `app.css` existe con variables corporativas y estilos del sidebar
- [x] `app.js` maneja la interacción del sidebar en móvil
- [x] El layout es responsive en resoluciones de escritorio y móvil
- [x] No existen colisiones de especificidad con clases de Bootstrap

---

## Verificación Final de la Fase 3

### Probar el layout en el navegador

1. **Acceder a la aplicación:**
   Abrir en el navegador `http://localhost:8080/` o una ruta que utilice el layout principal.

2. **Inspección visual:**
   - El navbar se renderiza en la parte superior con logo, usuario y botón de logout.
   - El sidebar se renderiza a la izquierda con los enlaces correspondientes al rol.
   - El contenido inyectado ocupa el cuerpo central de la página.
   - El footer se mantiene al pie de la página.

3. **Prueba responsive (Móvil / Tablet):**
   - Redimensionar la ventana o activar el modo dispositivo móvil en las DevTools del navegador.
   - Comprobar que el sidebar se oculta y se despliega correctamente al pulsar el botón toggle del navbar.

4. **Consola del navegador:**
   - Verificar que no existan errores 404 de assets (`bootstrap.min.css`, `bootstrap.bundle.min.js`, `app.css`, `app.js`).

---

## Checklist Final

- [x] Bootstrap descargado en `public/assets/vendor/bootstrap/`
- [x] `app/Views/layouts/main.php` — Layout principal con HTML5 semántico y prevención de XSS
- [x] `app/Views/layouts/partials/navbar.php` — Barra superior funcional y segura
- [x] `app/Views/layouts/partials/sidebar.php` — Menú lateral con filtrado defensivo por rol y marcado de ruta activa
- [x] `app/Views/layouts/partials/footer.php` — Pie de página
- [x] `public/assets/css/app.css` — Estilos complementarios y variables corporativas
- [x] `public/assets/js/app.js` — Script de soporte para el toggle responsive
- [x] Layout probado y funcional en vista de escritorio y móvil

**Anterior:** [Fase 2 — Estructura MVC Core](fase_2_mvc_core.md)
**Siguiente:** [Fase 4 — Autenticación y Middlewares](fase_4_autenticacion.md)
