# Paso 1: Base de Datos y Configuración — Módulo Operador (Fase 6)

> **Proyecto:** Sistema de Reportes y Gestión de Trabajos de Operadores (COSMOL)  
> **Fase:** 6 — Módulo Operador  
> **Paso:** 1 de 6 — Esquema de Base de Datos y Configuración de APIs  
> **Estado:** 🟢 Completado  

---

## 1. Objetivo del Paso
Preparar la base de datos local y la configuración de APIs externas para soportar el módulo operador. **No se crean tablas de trabajos** — los trabajos (reconexiones y reclamos) se consultan vía API desde una BD externa (ver AGENTS.md §1.1).

## 2. Cambios en Base de Datos (`database/init.sql`)

### 2.1 Tabla `especialidad` (catálogo)
La tabla `especialidad` es un catálogo puro de 3 valores. Se crea **antes** que `usuario` porque `usuario` la referencia:

```sql
CREATE TABLE IF NOT EXISTS especialidad(
    id_especialidad SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2.2 Modificar tabla `usuario`
Agregar la columna `id_especialidad` como FK nullable a `especialidad`. Un usuario tiene cero o una especialidad; una especialidad puede tener muchos usuarios:

```sql
-- En la definición de usuario, agregar:
id_especialidad INT REFERENCES especialidad(id_especialidad) ON DELETE SET NULL
```

**Relación:** `usuario (N) → especialidad (1)` — Solo los usuarios con rol Operador tendrán este campo asignado; para Administradores y Supervisores será `NULL`.

### 2.3 Insertar semillas de especialidades
Agregar los 3 valores fijos del catálogo:

```sql
INSERT INTO especialidad (nombre) VALUES
('Reconexión'),
('Maestro de alcantarillado'),
('Agua Potable')
ON CONFLICT DO NOTHING;
```

## 3. Configuración de APIs Externas

### 3.1 Crear `app/Config/api.php`
Archivo de configuración con las URLs base de las dos APIs externas:

```php
<?php
return [
    'reconexiones' => [
        'base_url' => getenv('API_RECONEXIONES_URL') ?: 'http://localhost:8000/api',
    ],
    'reclamos' => [
        'base_url' => getenv('API_RECLAMOS_URL') ?: 'http://localhost:8000/api',
    ],
];
```

### 3.2 Agregar variables al `.env`
```
API_RECONEXIONES_URL=http://host_del_servidor:puerto/api
API_RECLAMOS_URL=http://host_del_servidor:puerto/api
```

## 4. Acciones a realizar
1. Verificar/corregir la tabla `especialidad` en `database/init.sql`.
2. Agregar las semillas de especialidades.
3. Crear `app/Config/api.php`.
4. Agregar las variables de API al `.env` y `.env.example`.
5. Recrear el volumen de BD si es necesario (`docker-compose down -v && docker-compose up -d`).

## 5. Tablas que NO se crean
- ~~`trabajo`~~ — Los trabajos viven en la BD externa, se acceden vía API.
- ~~`observacion_trabajo`~~ — Las observaciones no se manejan localmente.
