# Hoja de Ruta — Proyecto COSMOL Reportes

> Flujo completo de desarrollo, desde los cimientos hasta el sistema terminado.  
> Cada fase tendrá su propio **plan de implementación** en [`Docs/fabian/`](file:///c:/Proyectos/Cosmol_reportes/Docs/fabian).

---

## Vista General

El desarrollo se divide en **2 etapas** con **8 fases** en total:

```
 ETAPA A — Cimientos Técnicos              ETAPA B — Módulos Funcionales
 (transversal, ya documentada)              (vertical, por caso de uso)
┌─────────────────────────────┐     ┌──────────────────────────────────────┐
│ Fase 1 │ Docker             │     │ Fase 5 │ Seguridad (completar)       │
│ Fase 2 │ MVC Core           │ ──► │ Fase 6 │ Módulo Operador             │
│ Fase 3 │ Layout Bootstrap   │     │ Fase 7 │ Módulo Administrador        │
│ Fase 4 │ Auth + Middlewares │     │ Fase 8 │ Módulo Reportes             │
└─────────────────────────────┘     └──────────────────────────────────────┘
```

---

## Etapa A — Cimientos Técnicos (Fases 1–4)

> **Estado:** Ya documentadas en `Docs/fabian/`. Son prerequisito para todo lo demás.

| Fase | Plan de Implementación | Qué entrega |
|---|---|---|
| **Fase 1** | [fase_1_docker.md](file:///c:/Proyectos/Cosmol_reportes/Docs/fabian/fase_1_docker.md) | Contenedores Docker funcionando, BD con esquema inicial, pgAdmin4 conectado |
| **Fase 2** | [fase_2_mvc_core.md](file:///c:/Proyectos/Cosmol_reportes/Docs/fabian/fase_2_mvc_core.md) | Mini-framework MVC: Router, Controller, Model, Database, front controller |
| **Fase 3** | [fase_3_layout.md](file:///c:/Proyectos/Cosmol_reportes/Docs/fabian/fase_3_layout.md) | Dashboard con Bootstrap: navbar, sidebar por rol, footer, responsive |
| **Fase 4** | [fase_4_autenticacion.md](file:///c:/Proyectos/Cosmol_reportes/Docs/fabian/fase_4_autenticacion.md) | Login/logout, sesiones, AuthMiddleware, RoleMiddleware |

**Resultado de la Etapa A:** Un sistema que arranca, autentica usuarios y controla acceso por rol, pero sin funcionalidad de negocio.

---

## Etapa B — Módulos Funcionales (Fases 5–8)

> **Estado:** Por documentar. Cada fase se planificará con su propio plan de implementación antes de ejecutarla.

### Fase 5 — Módulo Seguridad (completar)

> Completa lo que la Fase 4 dejó pendiente. Al terminar, el administrador puede gestionar usuarios, roles y permisos desde la interfaz.

| Caso de Uso | Controllers | Views | Models |
|---|---|---|---|
| Gestionar Usuario (CRUD) | `UsuarioController` | `seguridad/usuarios.php` | `Usuario.php` (ampliar) |
| Gestionar Rol (CRUD) | `RolController` | `seguridad/roles.php` | `Rol.php` |
| Asignar Permiso | `RolController` o dedicado | `seguridad/permisos.php` | `Permiso.php` |

**Entregable:** El administrador puede crear, editar, desactivar usuarios y asignar roles desde el sistema.  
**Archivo:** `Docs/fabian/fase_5_seguridad.md` *(por crear)*

---

### Fase 6 — Módulo Operador

> El módulo central del sistema. Los operadores gestionan sus propios trabajos.

| Caso de Uso | Controllers | Views | Models |
|---|---|---|---|
| Listar Trabajos | `OperadorController` | `operador/trabajos.php` | `Trabajo.php` |
| Registrar Trabajo Concluido | `OperadorController` | (dentro de trabajos) | `Trabajo.php`, `Estado.php` |
| Registrar Observaciones | `OperadorController` | `operador/observaciones.php` | (Model observaciones) |
| Historial de Trabajo | `OperadorController` | `operador/historial.php` | `Trabajo.php` |

**Entregable:** Un operador inicia sesión, ve sus trabajos pendientes, los marca como concluidos, agrega observaciones y consulta su historial.  
**Archivo:** `Docs/fabian/fase_6_operador.md` *(por crear)*

---

### Fase 7 — Módulo Administrador

> El administrador gestiona trabajos, usuarios y estados desde un panel centralizado.

| Caso de Uso | Controllers | Views | Models |
|---|---|---|---|
| Gestionar Usuario | `AdministradorController` | `administrador/usuarios.php` | Reutiliza `Usuario.php` |
| Gestionar Trabajo | `AdministradorController` | `administrador/trabajos.php` | `Trabajo.php` |
| Registrar Trabajo Pendiente | `AdministradorController` | `administrador/trabajo_pendiente.php` | `Trabajo.php` |
| Gestionar Estado | `AdministradorController` | `administrador/estados.php` | `Estado.php` |

**Entregable:** El administrador crea trabajos pendientes, los asigna a operadores, gestiona estados y supervisa todo el sistema.  
**Archivo:** `Docs/fabian/fase_7_administrador.md` *(por crear)*

---

### Fase 8 — Módulo Reportes

> Visualización y exportación de datos de consultas de socios y trabajos.

| Caso de Uso | Controllers | Views | Models |
|---|---|---|---|
| Filtrar Reporte | `ReporteController` | `reportes/filtrar.php` | `Reporte.php` |
| Visualizar Reporte | `ReporteController` | `reportes/visualizar.php` | `Reporte.php` |
| Exportar Reporte | `ReporteController` | `reportes/exportar.php` | `Reporte.php` |

**Entregable:** Se pueden filtrar consultas por fecha/operador/estado, visualizar los resultados y exportarlos.  
**Archivo:** `Docs/fabian/fase_8_reportes.md` *(por crear)*

---

## Diagrama de Dependencias

```mermaid
graph TD
    F1["Fase 1: Docker"] --> F2["Fase 2: MVC Core"]
    F2 --> F3["Fase 3: Layout Bootstrap"]
    F3 --> F4["Fase 4: Auth + Middlewares"]
    
    F4 --> F5["Fase 5: Seguridad<br/>(CRUD usuarios, roles, permisos)"]
    
    F5 --> F6["Fase 6: Operador<br/>(trabajos, observaciones, historial)"]
    F5 --> F7["Fase 7: Administrador<br/>(gestión trabajos, estados, usuarios)"]
    
    F6 --> F8["Fase 8: Reportes<br/>(filtrar, visualizar, exportar)"]
    F7 --> F8

    style F1 fill:#3b82f6,color:#fff
    style F2 fill:#3b82f6,color:#fff
    style F3 fill:#3b82f6,color:#fff
    style F4 fill:#3b82f6,color:#fff
    style F5 fill:#f59e0b,color:#000
    style F6 fill:#10b981,color:#fff
    style F7 fill:#10b981,color:#fff
    style F8 fill:#8b5cf6,color:#fff
```

**Leyenda:**
- 🔵 Azul = Etapa A (Cimientos) — ya documentada
- 🟡 Amarillo = Seguridad — puente entre cimientos y módulos
- 🟢 Verde = Módulos funcionales principales
- 🟣 Morado = Reportes — depende de que existan datos de los otros módulos

---

## Flujo de Trabajo por Fase

Cada fase seguirá este ciclo:

```
  ┌─────────────────────────────────────────────────┐
  │  1. PLANIFICAR                                   │
  │     Crear plan en Docs/fabian/fase_N_nombre.md   │
  │     Revisar y aprobar antes de codificar         │
  ├─────────────────────────────────────────────────┤
  │  2. IMPLEMENTAR                                  │
  │     Ejecutar paso a paso según el plan           │
  │     Verificar cada paso antes de avanzar         │
  ├─────────────────────────────────────────────────┤
  │  3. VERIFICAR                                    │
  │     Cumplir el checklist final del plan          │
  │     Probar en el navegador y en pgAdmin4         │
  ├─────────────────────────────────────────────────┤
  │  4. SIGUIENTE FASE                               │
  │     Solo avanzar si la verificación pasa         │
  └─────────────────────────────────────────────────┘
```

---

## Complejidad Estimada por Fase

| Fase | Complejidad | Archivos nuevos aprox. | Notas |
|---|---|---|---|
| Fase 1 | 🟢 Baja | 5 | Configuración, no código de app |
| Fase 2 | 🟡 Media | 8 | Framework core, requiere cuidado en Router |
| Fase 3 | 🟢 Baja | 6 | Maquetación HTML/CSS con Bootstrap |
| Fase 4 | 🟡 Media | 6 | Autenticación, middlewares, seguridad |
| Fase 5 | 🟡 Media | 5-7 | CRUD completo + permisos |
| Fase 6 | 🟡 Media | 6-8 | Lógica de trabajos y estados |
| Fase 7 | 🟡 Media | 6-8 | Reutiliza lógica pero agrega gestión |
| Fase 8 | 🔴 Media-Alta | 5-7 | Filtros, visualización, exportación |

---

## Estructura Final de Planes en Docs/fabian/

```
Docs/fabian/
├── implementacion_inicial.md          ← índice general (existente)
├── fase_1_docker.md                   ← ✅ existente
├── fase_2_mvc_core.md                 ← ✅ existente
├── fase_3_layout.md                   ← ✅ existente
├── fase_4_autenticacion.md            ← ✅ existente
├── fase_5_seguridad.md                ← 📝 por crear
├── fase_6_operador.md                 ← 📝 por crear
├── fase_7_administrador.md            ← 📝 por crear
└── fase_8_reportes.md                 ← 📝 por crear
```

---

## ¿Cómo avanzamos?

> [!IMPORTANT]
> **Pregunta para decidir:** ¿Las fases 1-4 ya están implementadas en código o solo están documentadas?  
> Esto determina si empezamos ejecutando la Fase 1 o si saltamos directo a planificar la Fase 5.
