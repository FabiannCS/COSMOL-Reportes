# Implementación Inicial — COSMOL Reportes

> Pasos base para levantar la infraestructura del proyecto desde cero. Cada fase debe completarse y verificarse antes de pasar a la siguiente.

## Estado Actual del Proyecto

| Elemento | Estado |
|---|---|
| Repositorio Git | Inicializado |
| `.gitignore` | Creado (excluye `.env`) |
| `.env` / `.env.example` | Creados pero vacíos |
| `AGENTS.md` | Definido con arquitectura completa |
| Código fuente | Sin crear |
| Docker | Sin crear |

---

## Fases de Implementación

| Fase | Documento | Objetivo |
|---|---|---|
| **Fase 1** | [Infraestructura Docker](fase_1_docker.md) | Levantar contenedores (PHP+Apache, PostgreSQL), configurar variables de entorno, crear esquema de base de datos |
| **Fase 2** | [Estructura MVC Core](fase_2_mvc_core.md) | Crear el mini-framework MVC: Router, Controller base, Model base, Database singleton, front controller |
| **Fase 3** | [Layout Base y Assets](fase_3_layout.md) | Integrar Bootstrap, crear layout de dashboard (navbar, sidebar, footer), estilos propios |
| **Fase 4** | [Autenticación y Middlewares](fase_4_autenticacion.md) | Implementar login/logout, manejo de sesiones, control de acceso por rol |

## Orden de Ejecución

```
Fase 1 (Docker)  →  Fase 2 (MVC Core)  →  Fase 3 (Layout)  →  Fase 4 (Auth)
     ↓                    ↓                     ↓                    ↓
  Entorno            Framework             Interfaz             Seguridad
  funcionando        funcionando            visual               base lista
```

## Resultado Final

Una vez completadas las 4 fases, el sistema tendrá:
- Entorno Docker reproducible
- MVC funcional con routing
- Layout de dashboard con Bootstrap
- Autenticación y control de acceso por rol
- Base de datos con esquema inicial

A partir de aquí se pueden implementar los módulos funcionales (Operador, Administrador, Reportes) de forma independiente.
