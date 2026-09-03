# Fase 8 — Paso 5: Verificación, RBAC y Pruebas Finales

> **Dependencias:** Pasos 1, 2, 3 y 4 completados.
> **Archivos afectados:** Ninguno nuevo. Este paso es de validación y corrección de lo implementado en los pasos anteriores.

---

## Objetivo

Garantizar que la **Fase 8 — Módulo Reportes** cumple con:
1. Compatibilidad estricta con **PHP 7.3** (sin sintaxis de PHP 7.4+/8.x).
2. Control de acceso RBAC correcto: solo roles `Administrador` y `Supervisor` acceden a `/reportes/*`.
3. Funcionamiento de la tabla paginada, filtros combinados y correcta exportación CSV.

---

## Checklist de Verificación

### 5.1 Validación Sintáctica PHP 7.3

```bash
docker exec cosmol_app bash -c "
php -l app/Models/Reporte.php &&
php -l app/Controllers/ReporteController.php &&
php -l app/Views/reportes/visualizar.php
"
```

Todos deben responder: `No syntax errors detected`.

### 5.2 Control de Acceso RBAC

| Usuario | Rol | Ruta Probada | Esperado |
|---|---|---|---|
| `admin` | Administrador | `/reportes/visualizar` | ✅ HTTP 200 |
| `supervisor` | Supervisor | `/reportes/visualizar` | ✅ HTTP 200 |
| `franco` | Operador | `/reportes/visualizar` | 🔴 Redirección a `/operador/trabajos` |

### 5.3 Pruebas Funcionales de Interfaz (Visualizar y Filtrar)

- [ ] Ingresar a `/reportes/visualizar`. La tabla muestra las consultas predeterminadas (si las hay).
- [ ] Filtrar por un **rango de fechas** específico y verificar que los resultados corresponden.
- [ ] Filtrar adicionalmente por un **Tipo de Consulta** y verificar que la intersección funciona (AND).
- [ ] Navegar a la página 2 de la paginación y verificar que los filtros continúan aplicados.
- [ ] Clic en **Limpiar Filtros**; debe restaurar la vista y limpiar la URL.

### 5.4 Pruebas de Exportación CSV

- [ ] Con la vista limpia (sin filtros), hacer clic en **Exportar a CSV**.
- [ ] Verificar que se descarga el archivo `reporte_consultas_*.csv`.
- [ ] Abrir el archivo en un software de hojas de cálculo (ej. Excel) y verificar que los acentos y las "ñ" se muestren correctamente (gracias al BOM UTF-8).
- [ ] Aplicar filtros en la UI, hacer clic en Exportar nuevamente, y verificar que el archivo descargado *solo* contiene los registros filtrados.

---

## Criterio de Aceptación Final

La Fase 8 se considera **completada** cuando todos los pasos del checklist sean superados y el Módulo de Reportes esté listo para ser usado por el equipo administrativo y gerencial, marcando la completitud del desarrollo base de las especificaciones iniciales.
