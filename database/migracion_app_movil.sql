-- ==============================================================================
-- MIGRACIÓN IDEMPOTENTE: MÓDULO APP MÓVIL (COSMOL-REPORTES)
-- Aplica los catálogos y usuarios de sistema necesarios sin borrar datos previos.
-- ==============================================================================

-- 1. Asegurar columnas en tabla consulta
ALTER TABLE consulta ADD COLUMN IF NOT EXISTS telefono VARCHAR(30);
ALTER TABLE consulta ADD COLUMN IF NOT EXISTS tipo_ubicacion VARCHAR(20);

-- 2. Asegurar existencia de usuarios de sistema por nombre único (id_rol = 2: Supervisor)
INSERT INTO usuario (username, password_hash, id_rol, estado) VALUES
('chatbot_whatsapp', 'SISTEMA_NO_LOGIN', 2, 1),
('app_movil', 'SISTEMA_NO_LOGIN', 2, 1)
ON CONFLICT (username) DO NOTHING;

-- 3. Sembrar o actualizar catálogo de tipos de consulta
INSERT INTO tipo_consulta (id_tipo, nombre, descripcion) VALUES
(1, 'Autenticación / Acceso', 'Socio valida su identidad en el chatbot o inicia sesión en la app'),
(2, 'Consulta de Deuda', 'Consulta de facturas pendientes y saldo'),
(3, 'Historial de Consumo', 'Consulta de histórico de consumo en m³ y facturas pagadas'),
(4, 'Registro de Reclamo', 'Ticket de reclamo por agua o alcantarillado registrado'),
(5, 'Solicitud de Reconexión', 'Ticket de trámite de reconexión registrado'),
(6, 'Información de Oficinas', 'Consulta de ubicación de oficina central y horarios de atención'),
(7, 'Derivación a Agente', 'Solicitud de atención con un operador humano'),
(8, 'Estado de Solicitudes', 'Consulta de estado de reclamos y reconexiones'),
(9, 'Descarga de Factura PDF', 'Descarga o visualización de factura en PDF desde la app móvil'),
(10, 'Pago: Multipago', 'Intento de pago o redirección a pasarela Multipago desde la app'),
(11, 'Pago: Pago al Paso', 'Intento de pago o redirección a pasarela Pago al Paso desde la app'),
(12, 'Pago: Código QR', 'Generación de código QR interbancario para pago desde la app')
ON CONFLICT (id_tipo) DO UPDATE SET
    nombre = EXCLUDED.nombre,
    descripcion = EXCLUDED.descripcion;

-- 4. Reajustar la secuencia de tipo_consulta al valor máximo actual
SELECT setval('tipo_consulta_id_tipo_seq', COALESCE((SELECT MAX(id_tipo) FROM tipo_consulta), 12));
