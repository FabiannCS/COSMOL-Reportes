DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM tipo_consulta LIMIT 1) THEN
        INSERT INTO tipo_consulta (nombre, descripcion) VALUES
        ('Consulta Saldo', 'Consulta de saldo actual de deuda'),
        ('Requisito Reconexión', 'Consulta sobre requisitos para reconexión'),
        ('Horario Atención', 'Consulta sobre horarios de atención en oficinas');
    END IF;

    IF NOT EXISTS (SELECT 1 FROM consulta LIMIT 1) THEN
        INSERT INTO consulta (codigo_socio, nombres, fecha_consulta, hora_consulta, id_tipo) VALUES
        (1001, 'Juan Perez', '2023-10-01', '10:00:00', 1),
        (1002, 'Maria Gomez', '2023-10-02', '11:30:00', 2),
        (1003, 'Carlos Lopez', '2023-10-03', '09:15:00', 3),
        (1004, 'Ana Martinez', '2023-10-03', '14:45:00', 1);
    END IF;
END $$;
