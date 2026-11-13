---
name: COSMOL Reportes
description: Sistema de Visualización de Reportes y Gestión Operativa para COSMOL
colors:
  primary: "#0284c7"
  primary-hover: "#0369a1"
  primary-light: "#e0f2fe"
  sidebar-bg: "#0f172a"
  sidebar-hover: "#1e293b"
  sidebar-active: "#0284c7"
  sidebar-text: "#94a3b8"
  sidebar-text-active: "#ffffff"
  neutral-bg: "#f8fafc"
  surface-card: "#ffffff"
  surface-header: "#ffffff"
  border-color: "#e2e8f0"
  border-dark: "#334155"
  text-main: "#1e293b"
  text-muted: "#64748b"
  text-light: "#94a3b8"
  success: "#10b981"
  success-light: "#d1fae5"
  warning: "#f59e0b"
  warning-light: "#fef3c7"
  danger: "#ef4444"
  danger-light: "#fee2e2"
  info: "#0ea5e9"
  info-light: "#e0f2fe"
typography:
  fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif"
  fontFamilyMono: "SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace"
  display:
    fontSize: "1.75rem"
    fontWeight: "700"
    lineHeight: "1.2"
  h1:
    fontSize: "1.5rem"
    fontWeight: "700"
    lineHeight: "1.25"
  h2:
    fontSize: "1.25rem"
    fontWeight: "600"
    lineHeight: "1.3"
  h3:
    fontSize: "1.1rem"
    fontWeight: "600"
    lineHeight: "1.4"
  body:
    fontSize: "0.9375rem"
    fontWeight: "400"
    lineHeight: "1.5"
  small:
    fontSize: "0.8125rem"
    fontWeight: "400"
    lineHeight: "1.4"
  label:
    fontSize: "0.75rem"
    fontWeight: "600"
    letterSpacing: "0.05em"
rounded:
  sm: "4px"
  md: "6px"
  lg: "8px"
  xl: "12px"
  full: "9999px"
spacing:
  xs: "0.25rem"
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2rem"
  navbar-height: "60px"
  sidebar-width: "260px"
  sidebar-collapsed-width: "70px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.md}"
    padding: "0.5rem 1rem"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  card:
    backgroundColor: "{colors.surface-card}"
    rounded: "{rounded.lg}"
    padding: "1.25rem"
---

# Design System — COSMOL Reportes

## Overview

El sistema visual de **COSMOL Reportes** está concebido bajo el modo **Operate**, priorizando la eficiencia, la legibilidad técnica y la ergonomía tanto en pantallas de escritorio (administración y supervisión) como en dispositivos móviles (cuadrillas de operadores en terreno).

La estética combina el azul corporativo institucional (asociado a los servicios de agua potable de Montero) con una estructura visual limpia, geométrica y libre de distracciones o clichés de inteligencia artificial ("AI slop"). Se utiliza **Bootstrap 5.3** con componentes utilitarios, asegurando máxima compatibilidad, rendimiento y velocidad de renderizado en PHP 7.3.

---

## Colors

La paleta cromática se estructura en tres roles fundamentales:

### 1. Colores Primarios e Institucionales
- **Primary (`#0284c7`):** Color principal de marca. Usado en botones primarios, enlaces activos, iconos destacados e indicador de navegación activa en el sidebar.
- **Primary Hover (`#0369a1`):** Estado interactivo oscuro para botones y enlaces.
- **Primary Light (`#e0f2fe`):** Fondos sutiles de acento para insignias de reconexión y contenedores informativos.

### 2. Superficies y Neutros
- **Sidebar Background (`#0f172a`):** Azul marino pizarra oscuro. Confiere seriedad institucional y reduce el cansancio visual en el menú lateral.
- **Sidebar Hover (`#1e293b`):** Estado hover de los ítems de navegación lateral.
- **Sidebar Text (`#94a3b8`):** Color secundario de texto en el sidebar, garantizando un ratio de contraste superior a 4.5:1.
- **Neutral Background (`#f8fafc`):** Fondo global del cuerpo de la aplicación; un tono gris azulado suave que evita el encandilamiento del blanco puro.
- **Surface Card (`#ffffff`):** Blanco puro para tarjetas de trabajo, formularios modales y contenedores de tablas.
- **Borders (`#e2e8f0`):** Líneas de división limpias y sutiles entre secciones y celdas de tablas.

### 3. Estados Operativos (Semáforo de Gestión)
- **Success (`#10b981` / Fondo: `#d1fae5`):** Trabajos concluidos exitosamente y usuarios activos.
- **Warning (`#f59e0b` / Fondo: `#fef3c7`):** Trabajos con estado "PENDIENTE" que requieren atención de cuadrilla.
- **Danger (`#ef4444` / Fondo: `#fee2e2`):** Trabajos con estado "NO CONCLUIDO" o "NO PROCEDENTE", alertas de error y usuarios inactivos.
- **Info (`#0ea5e9` / Fondo: `#e0f2fe`):** Métricas auxiliares de la App Móvil y detalles de consultas de socios.

---

## Typography

Se utiliza una pila tipográfica de sistema (*system font stack*), eliminando descargas de fuentes web externas y garantizando carga instantánea:

`-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`

### Jerarquía de Escala
- **Display / Títulos de Módulo (`1.5rem – 1.75rem`, `font-weight: 700`):** Encabezados principales de página (ej. "Supervisión de Trabajos", "Consultas de WhatsApp").
- **Títulos de Sección / Tarjetas (`1.1rem – 1.25rem`, `font-weight: 600`):** Nombres de socios, encabezados de tarjetas de trabajo y títulos de modales.
- **Cuerpo / Textos Operativos (`0.9375rem`, `font-weight: 400`, `line-height: 1.5`):** Textos descriptivos, glosas, direcciones y campos de formularios.
- **Datos Técnicos y Metadatos (`0.8125rem`, `font-weight: 500`):** Números de medidor, teléfonos, coordenadas GPS y fecha/hora.
- **Etiquetas y Subtítulos de Sidebar (`0.72rem – 0.78rem`, `font-weight: 700`, `letter-spacing: 0.05em`, `text-transform: uppercase`):** Agrupadores de navegación (ej. "OPERACIONES", "AUDITORÍA").

---

## Layout

El layout implementa un patrón de dashboard de 2 paneles con adaptación fluida según el dispositivo:

### Estructura de Capas
1. **Header Fijo Superior (`height: 60px`):** Fondo blanco, borde inferior sutil, logotipo de COSMOL, botón toggle de sidebar y selector de perfil de usuario.
2. **Sidebar Lateral Fijo (`width: 260px`):**
   - **En Escritorio (≥ 992px):** Posición fija a la izquierda con soporte para estado colapsado (`width: 70px`), mostrando únicamente iconos con tooltips explicativos.
   - **En Móvil (< 992px):** Menú flotante off-canvas con telón de fondo oscuro (`.sidebar-backdrop`), deslizable con transición fluida de 0.3s.
3. **Área Principal de Contenido (`.app-main`):**
   - Relleno de `1.75rem` en escritorio y `0.85rem` en móviles pequeños para aprovechar al máximo la pantalla táctil.

---

## Elevation & Depth

El diseño opta por una **estética predominantemente tonal y plana**, evitando sombras pesadas o efectos volumétricos artificiales:

- **Tarjetas y Paneles (`.card`):** Fondo `#ffffff`, borde perimetral `1px solid #e2e8f0` y sombra ultra-sutil `box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05)`.
- **Menú Lateral Móvil:** Sombra pronunciada al desplegarse sobre el contenido: `box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3)`.
- **Modales y Diálogos:** `box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)`.

---

## Shapes

- **Bordes Redondeados Generales:**
  - Botones estándar, inputs de formularios y selects: `border-radius: 6px` (`rounded.md`).
  - Tarjetas de contenido, paneles de estadísticas y modales: `border-radius: 8px` a `12px` (`rounded.lg`).
  - Badges e insignias de estado: `border-radius: 4px` o formato píldora (`rounded-pill`).
  - Avatares de usuario: `border-radius: 50%` (`rounded.full`).

---

## Components

### 1. Tarjetas de Trabajo para Operadores (`.trabajo-card`)
Diseñadas para lectura rápida en campo:
- Encabezado con número de orden, tipo de servicio y badge de estado de alto contraste.
- Cuerpo con datos del socio en negrita, dirección, zona y ruta claramente separadas.
- Botón de acceso directo a Google Maps con icono de GPS (`bi-geo-alt-fill`).
- Botón de acción principal grande (`btn-primary` de al menos 44px de altura táctil) para abrir la ficha de conclusión.

### 2. Tarjetas de Estadísticas del Dashboard (`.stat-card`)
- Fondo blanco con borde sutil.
- Icono temático dentro de un círculo con fondo de color suave (`bg-primary-subtle`, `bg-warning-subtle`, etc.).
- Cifra principal en tamaño display (`1.75rem`) en negrita, acompañada de una etiqueta descriptiva en texto gris secundario.

### 3. Tablas de Auditoría y Reportes
- Encabezado con fondo gris muy suave (`.table-light`) y texto en mayúsculas pequeñas.
- Celdas con relleno compacto (`py-2.5 px-3`) para maximizar la cantidad de registros visibles por pantalla.
- Insignias de canal: Verde WhatsApp (`#25D366`) para consultas de chatbot, y Azul Corporativo para App Móvil.

### 4. Formularios de Conclusión de Trabajo
- Campos de solo lectura (datos de la API) destacados con fondo ligeramente grisáceo (`bg-light`) y etiquetas descriptivas.
- Área editable (lectura física del medidor y glosa técnica) con campos de entrada amplios y textos de ayuda explícitos.
- Selector de estado claro: "CONCLUIDO" (acción primaria verde o azul) y "NO CONCLUIDO / NO PROCEDENTE" (acción secundaria roja/amarilla con justificación obligatoria).

---

## Do's and Don'ts

### Do's (Prácticas Recomendadas)
- ✅ **Diseñar pensando en el sol y la calle:** Usa contrastes altos y botones táctiles generosos para las pantallas de operador.
- ✅ **Reutilizar clases utilitarias de Bootstrap 5.3:** Prioriza `d-flex`, `gap-2`, `badge`, `card`, `table-responsive`, etc., antes de escribir CSS propio.
- ✅ **Mantener el código en PHP 7.3:** No uses sintaxis moderna no soportada en el servidor de producción.
- ✅ **Aprovechar los iconos de Bootstrap (`bi bi-*`):** Aportan claridad inmediata sin necesidad de imágenes pesadas.
- ✅ **Validar estados vacíos (Empty States):** Muestra un mensaje amigable cuando no haya trabajos pendientes ("No tienes trabajos pendientes asignados para hoy").

### Don'ts (Prácticas Prohibidas)
- ❌ **No inventar gradientes púrpuras, violetas o neón:** El proyecto es un sistema de servicio público de agua potable; la paleta es azul corporativa, pizarra y blanco.
- ❌ **No usar botones diminutos en vistas móviles:** Cualquier acción interactiva para operadores debe tener al menos 44px de altura.
- ❌ **No sobrecargar con animaciones lentas:** El sistema debe sentirse inmediato; las transiciones deben ser de micro-interacción (0.15s – 0.25s).
- ❌ **No ocultar la información técnica vital:** Zona, ruta, código de socio y medidor nunca deben truncarse ni esconderse en acordeones innecesarios.
- ❌ **No introducir librerías JS externas de frontend:** Prohibido React, Vue, jQuery innecesario o transpiladores.
