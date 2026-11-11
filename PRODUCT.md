# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

1. **Operadores de Terreno (Técnicos de COSMOL):**
   - **Situación:** Trabajo diario en calles y domicilios de Montero (Bolivia), bajo intensa luz solar, polvo, y con teléfonos móviles o tablets en redes móviles 3G/4G.
   - **Misión:** Visualizar rápidamente su lista de reconexiones o reclamos pendientes asignados según su especialidad (Reconexión, Maestro de alcantarillado, Agua Potable), navegar a la ubicación mediante coordenadas GPS, verificar evidencias fotográficas y concluir los trabajos registrando lecturas de medidor y glosas técnicas. En caso de impedimentos (predio cerrado, socio ausente), reportar la contingencia como "NO CONCLUIDO" o "NO PROCEDENTE".

2. **Supervisores de Cuadrilla y Operaciones:**
   - **Situación:** En oficina técnica o terminales de supervisión, monitoreando el avance de las cuadrillas en tiempo real.
   - **Misión:** Auditar reclamos y reconexiones pendientes, identificar cuellos de botella en zonas/rutas específicas, revisar trabajos no concluidos y supervisar la atención brindada por el personal operativo.

3. **Administradores del Sistema y Gerencia:**
   - **Situación:** En estaciones de trabajo de escritorio, gestionando cuentas de usuario, asignación de roles y permisos (RBAC), y evaluando el volumen de consultas de socios.
   - **Misión:** Administrar el personal y sus especialidades, consultar métricas de interacción ciudadana provenientes del Chatbot de WhatsApp y la App Móvil, y exportar reportes ejecutivos en formato CSV compatibles con Microsoft Excel.

---

## Product Purpose

COSMOL Reportes es la plataforma unificada de operaciones y auditoría de la **Cooperativa de Servicios Públicos Montero Limitada (COSMOL)**. Su propósito es doble:
1. **Centralizar la auditoría de atención al socio:** Recibir y estructurar en tiempo real las consultas generadas a través de canales digitales de autoservicio (Chatbot de WhatsApp y App Móvil), facilitando el análisis de demanda de trámites, reclamos y pagos.
2. **Optimizar la operación de campo:** Proveer a las cuadrillas de una herramienta móvil ligera, rápida y libre de fricciones para ejecutar y concluir órdenes de trabajo (reconexiones de agua y atención de reclamos de agua potable y alcantarillado), conectada de forma transparente a las bases de datos centrales de la cooperativa mediante APIs REST.

El éxito significa: cero pérdida de órdenes de trabajo en campo, conclusión ágil de trabajos sin errores de transcripción, y visibilidad inmediata para la gerencia sobre qué zonas y servicios requieren mayor atención.

---

## Positioning

A diferencia de sistemas comerciales genéricos de mesa de ayuda (Helpdesk) o ERPs sobredimensionados:
- **Especialización técnica hidrosanitaria:** Diseñado a la medida de la estructura operativa de COSMOL (zonas, rutas, números de cuenta, lectura física de medidores, glosas de reconexión).
- **Arquitectura desacoplada en tiempo real:** No almacena ni duplica trabajos localmente; consume directamente las APIs del servidor central de COSMOL con llamadas paralelas de alto rendimiento (multi-cURL) y gestiona contingencias locales con la tabla defensiva `trabajo_seguimiento`.
- **Ultra-ligereza y resiliencia en terreno:** Cero sobrecarga de frameworks JavaScript pesados; interfaces server-rendered en PHP 7.3 y Bootstrap 5.3 que cargan al instante incluso en conexiones móviles de baja velocidad.

---

## Operating Context

- **Entorno de Campo (Móvil):** Uso con una sola mano, botones de acción grandes, enlaces táctiles de un solo toque para abrir ubicaciones en Google Maps, visualización clara de fotografías de evidencia enviadas por los socios y formularios de conclusión simplificados.
- **Entorno Administrativo (Escritorio):** Densidad de datos optimizada en tablas con paginación, filtros rápidos por fecha y tipo, barra lateral retráctil para maximizar el área de trabajo y tarjetas de estadísticas (Stat Cards) para visualización inmediata de indicadores clave.
- **Seguridad Corporativa:** Autenticación por sesión, control de acceso basado en roles (RBAC) con permisos granulares, protección CSRF en formularios de mutación y tokens de API protegidos (`X-Reportes-Token`) para la ingesta de datos desde WhatsApp y la App Móvil.

---

## Capabilities and Constraints

### Capacidades Confirmadas:
- Ingesta automatizada de eventos de auditoría vía `POST /api/consultas` (12 tipos de consulta homologados entre Chatbot y App Móvil).
- Módulo de filtrado y exportación de reportes a CSV con codificación UTF-8 y BOM para compatibilidad nativa con Excel.
- Detección automática de especialidad técnica del operador en sesión (Reconexión = 1, Maestro de alcantarillado = 2 [tipo 3], Agua Potable = 3 [tipo 2]).
- Conclusión en tiempo real vía `PUT` a las APIs REST externas de Reconexiones y Reclamos.
- Trazabilidad de contingencias en terreno mediante registro local en `trabajo_seguimiento` (`NO CONCLUIDO` / `NO PROCEDENTE`).
- Gestión integral de usuarios, asignación de roles y matriz de permisos granulares por módulo.

### Restricciones Técnicas Inquebrantables:
- **Backend:** Exclusivamente **PHP 7.3**. Ninguna sintaxis ni dependencia de PHP 7.4 o PHP 8.
- **Frontend:** HTML server-side renderizado con componentes utilitarios de **Bootstrap 5.3**. Prohibido introducir frameworks JS de cliente (React, Vue, etc.).
- **Despliegue:** Todo el stack corre en **Docker** (`php:7.3-apache` y `postgres:15`).
- **Base de Datos Local:** Host `db` en red Docker. Nunca almacenar trabajos concluidos ni pendientes en tablas locales; los datos operativos provienen de las APIs externas.

---

## Brand Commitments

- **Identidad Institucional:** Cooperativa de Servicios Públicos Montero Limitada (COSMOL R.L.).
- **Colores Corporativos:**
  - Azul Primario Institucional: `#0284c7` (Azul Agua / Sky Blue) — evoca agua limpia, servicio y solidez institucional.
  - Azul Primario Oscuro (Hover / Enfoque): `#0369a1`.
  - Fondo de Sidebar: `#0f172a` (Azul Marino Profundo / Slate 900) — seriedad, estructura y contraste profesional.
  - Fondo de Aplicación: `#f8fafc` (Gris Claro / Slate 50) — descanso visual para jornadas de oficina prolongadas.
- **Tono y Voz:** Profesional, claro, técnico y directo. En mensajes a operadores se prioriza la claridad ejecutiva ("Lectura registrada", "Trabajo pendiente", "Zona 7 / Ruta 12"). En reportes se prioriza la precisión estadística.

---

## Evidence on Hand

- **Catálogo de Consultas Homologado:** 12 tipos de consulta documentados en `database/init.sql` y `database/migracion_app_movil.sql`.
- **Esquema de Base de Datos Verificado:** Tablas `usuario`, `rol`, `permiso`, `rol_permiso`, `especialidad`, `consulta`, `tipo_consulta`, `trabajo_seguimiento`.
- **Integración con Chatbot Documentada:** Archivo `Integracion_COSMOL_Reportes.md`.
- **Hojas de Ruta y Planes de Ejecución:** Directorio `Docs/fabian/` con todas las fases completadas y verificadas.
- **Activos UI Locales:** Archivos en `public/assets/vendor/` con Bootstrap 5.3 y Bootstrap Icons locales.

---

## Product Principles

1. **La verdad de los trabajos está en la red central:** El sistema web actúa como un puente ligero y fiel a las APIs de COSMOL; no duplica datos innecesarios y refleja la realidad del terreno en tiempo real.
2. **Ergonomía en campo primero:** Un operador con guantes o bajo el sol debe poder ver la dirección, abrir el GPS, ver la foto del problema y enviar la lectura en menos de 3 toques.
3. **Resiliencia ante imprevistos:** Si un trabajo no puede realizarse por causas de fuerza mayor, el operador cuenta con una vía clara para reportar "NO CONCLUIDO" o "NO PROCEDENTE" sin perder la trazabilidad.
4. **Claridad sobre adorno:** Tablas legibles, badges de estado con alto contraste y componentes estándar de Bootstrap que nunca confundan al usuario.

---

## Accessibility & Inclusion

- **Contraste de Color:** Razón de contraste WCAG AA (mínimo 4.5:1) en todos los textos de navegación, insignias (badges) y botones de acción.
- **Compatibilidad Táctil:** Altura mínima de objetivos táctiles en botones móviles de 44px para facilitar la pulsación en teléfonos.
- **Semántica HTML:** Formularios con etiquetas explícitas (`<label for="...">`), atributos `aria-*` en componentes colapsables y modales de Bootstrap, y mensajes de retroalimentación claros tras cada operación.
