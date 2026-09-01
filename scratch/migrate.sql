CREATE TABLE IF NOT EXISTS especialidad(
    id_especialidad SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE usuario ADD COLUMN IF NOT EXISTS id_especialidad INT REFERENCES especialidad(id_especialidad) ON DELETE SET NULL;

INSERT INTO especialidad (nombre) VALUES
('Reconexión'),
('Maestro de alcantarillado'),
('Agua Potable')
ON CONFLICT DO NOTHING;
