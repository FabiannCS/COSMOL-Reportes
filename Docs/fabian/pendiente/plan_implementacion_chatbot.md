# Plan de Implementación: Integración con Chatbot de WhatsApp

Basado en el documento `Integracion_COSMOL_Reportes.md`, los datos del chatbot no se obtienen consultando al chatbot (modelo pull), sino que el chatbot empuja los datos (modelo push) a nuestro sistema web cada vez que un socio realiza una consulta, mediante un **webhook** a través de una petición POST con formato JSON. 

Para que nuestro sistema pueda recibir y almacenar estos datos para los reportes de visualización, debemos implementar un endpoint que actúe como receptor.

A continuación, el plan de implementación paso a paso en nuestro sistema `COSMOL-Reportes`:

## 1. Crear el Controlador Receptor (API)
Se debe crear el controlador `app/Controllers/ConsultaApiController.php` que expondrá la lógica para recibir los datos del chatbot.

**Responsabilidades del Controlador:**
- Leer el header `X-Reportes-Token` y validarlo contra la variable de entorno `REPORTES_API_TOKEN`.
- Obtener y decodificar el cuerpo de la petición en formato JSON (`php://input`).
- Extraer los campos enviados: `codigo_socio`, `nombres`, `id_tipo`, `fecha_consulta`, `hora_consulta`.
- Validar que los campos obligatorios vengan en la petición.
- Insertar los datos en la tabla `consulta` de la base de datos local `cosmol_reportes`.
- Devolver un código de respuesta HTTP `201 Created` en formato JSON confirmando el registro exitoso.

## 2. Registrar la Ruta de la API
En el archivo `app/Config/routes.php`, se debe agregar el endpoint para que apunte al controlador creado.
- **Método:** POST
- **Ruta:** `/api/consultas`
- **Destino:** `ConsultaApiController::registrar`

## 3. Preparar la Base de Datos (Semillas)
Asegurar que la tabla local `tipo_consulta` tenga los 7 tipos de consulta iniciales (Autenticación, Consulta de Deuda, Historial, Reclamo, Reconexión, Oficinas y Agente) con los mismos IDs que maneja el chatbot. 
- Ejecutar el script SQL de inserción `INSERT INTO tipo_consulta ...` tal cual se indica en el documento.

## 4. Configurar Variables de Entorno
En el archivo `.env` del sistema `COSMOL-Reportes`, se debe añadir el token de seguridad para que coincida con el que envía el chatbot.
- `REPORTES_API_TOKEN=cosmol_secret_token_reportes_2026` (o el valor que corresponda).

## 5. Pruebas y Visualización
Una vez implementado, el sistema estará listo para recibir datos. Para que nuestro sistema muestre los reportes de visualización:
- El módulo `ReporteController` (y sus correspondientes vistas en `app/Views/reportes/visualizar.php`) deberán consultar la tabla local `consulta` uniendo con `tipo_consulta` para listar las consultas generadas en tiempo real.
