
CREATE TABLE IF NOT EXISTS rol (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS especialidad(
    id_especialidad SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuario (
    id_usuario SERIAL PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    estado SMALLINT NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_rol INT REFERENCES rol(id_rol) ON DELETE SET NULL,
    id_especialidad INT REFERENCES especialidad(id_especialidad) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS tipo_consulta (
    id_tipo SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS consulta (
    id_consulta SERIAL PRIMARY KEY,
    codigo_socio INT NOT NULL,
    nombres VARCHAR(200) NOT NULL,
    fecha_consulta DATE NOT NULL DEFAULT CURRENT_DATE,
    hora_consulta TIME NOT NULL DEFAULT CURRENT_TIME,
    id_usuario INT REFERENCES usuario(id_usuario) ON DELETE SET NULL,
    id_tipo INT REFERENCES tipo_consulta(id_tipo) ON DELETE SET NULL
);

-- Datos iniciales de roles
INSERT INTO rol (nombre_rol, descripcion) VALUES
('Administrador', 'Gestión total del sistema, usuarios y reportes'),
('Supervisor', 'Supervisión de trabajos y reportes de socios'),
('Operador', 'Gestión y registro de trabajos asignados')
ON CONFLICT (nombre_rol) DO NOTHING;

-- Datos iniciales de especialidades (catálogo fijo para operadores)
INSERT INTO especialidad (nombre) VALUES
('Reconexión'),
('Maestro de alcantarillado'),
('Agua Potable')
ON CONFLICT DO NOTHING;

-- Tablas de Permisos y Rol-Permiso
CREATE TABLE IF NOT EXISTS permiso (
    id_permiso SERIAL PRIMARY KEY,
    clave_permiso VARCHAR(100) NOT NULL UNIQUE,
    nombre_permiso VARCHAR(150) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rol_permiso (
    id_rol INT NOT NULL REFERENCES rol(id_rol) ON DELETE CASCADE,
    id_permiso INT NOT NULL REFERENCES permiso(id_permiso) ON DELETE CASCADE,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_rol, id_permiso)
);

-- Semillas iniciales de permisos del sistema
INSERT INTO permiso (clave_permiso, nombre_permiso, descripcion, modulo) VALUES
-- Módulo Seguridad
('usuarios.ver', 'Ver Usuarios', 'Permite visualizar la lista de usuarios', 'Seguridad'),
('usuarios.crear', 'Crear Usuario', 'Permite registrar nuevos usuarios', 'Seguridad'),
('usuarios.editar', 'Editar Usuario', 'Permite modificar datos y contraseñas de usuarios', 'Seguridad'),
('usuarios.estado', 'Cambiar Estado Usuario', 'Permite activar o desactivar usuarios', 'Seguridad'),
('roles.ver', 'Ver Roles', 'Permite visualizar el catálogo de roles', 'Seguridad'),
('roles.crear', 'Crear Rol', 'Permite crear nuevos roles', 'Seguridad'),
('roles.editar', 'Editar Rol', 'Permite modificar información de roles', 'Seguridad'),
('roles.permisos', 'Asignar Permisos', 'Permite asignar permisos a roles', 'Seguridad'),

-- Módulo Operaciones
('trabajos.ver', 'Ver Trabajos', 'Permite consultar lista de trabajos', 'Operaciones'),
('trabajos.crear', 'Crear Trabajo', 'Permite registrar nuevos trabajos pendientes', 'Operaciones'),
('trabajos.concluir', 'Concluir Trabajo', 'Permite a operadores registrar trabajos concluidos', 'Operaciones'),

-- Módulo Administración
('estados.gestionar', 'Gestionar Estados', 'Permite administrar estados de trabajos', 'Administración'),

-- Módulo Reportes
('reportes.ver', 'Ver Reportes', 'Permite visualizar reportes de consultas de socios', 'Reportes'),
('reportes.exportar', 'Exportar Reportes', 'Permite exportar datos a formatos externos', 'Reportes')
ON CONFLICT (clave_permiso) DO NOTHING;

-- Asignación de todos los permisos iniciales al rol Administrador (id_rol = 1)
INSERT INTO rol_permiso (id_rol, id_permiso)
SELECT 1, id_permiso FROM permiso
ON CONFLICT DO NOTHING;

-- Índices de alto rendimiento para acelerar reportes, filtros y consultas del dashboard
CREATE INDEX IF NOT EXISTS idx_consulta_fecha ON consulta(fecha_consulta);
CREATE INDEX IF NOT EXISTS idx_consulta_id_tipo ON consulta(id_tipo);
CREATE INDEX IF NOT EXISTS idx_consulta_codigo_socio ON consulta(codigo_socio);
CREATE INDEX IF NOT EXISTS idx_consulta_id_usuario ON consulta(id_usuario);
CREATE INDEX IF NOT EXISTS idx_usuario_id_rol ON usuario(id_rol);
CREATE INDEX IF NOT EXISTS idx_usuario_id_especialidad ON usuario(id_especialidad);
CREATE INDEX IF NOT EXISTS idx_rol_permiso_id_permiso ON rol_permiso(id_permiso);

