# Paso 2: Modelo y Servicio API — Módulo Operador (Fase 6)

> **Proyecto:** Sistema de Reportes y Gestión de Trabajos de Operadores (COSMOL)  
> **Fase:** 6 — Módulo Operador  
> **Paso:** 2 de 6 — Capa de Datos Locales (Model) y Consumo de API (Service)  
> **Estado:** 🟢 Completado  

---

## 1. Objetivo del Paso
Implementar el modelo `Especialidad` para acceso a datos locales y el servicio `ApiClient` para consumir las APIs externas de reconexiones y reclamos. **No se crean modelos de trabajos** — los datos de trabajos provienen de APIs externas (ver AGENTS.md §1.1).

## 2. Modelo Local a Implementar

### 2.1 `app/Models/Especialidad.php`
- Hereda de `App\Core\Model`.
- Método `getByUsuario($idUsuario)`: Retorna la especialidad asignada al operador.
- Método `getAll()`: Lista todas las especialidades del catálogo.
- Método `asignar($idEspecialidad, $idUsuario)`: Asigna un operador a una especialidad.

## 3. Servicio de Consumo de API

### 3.1 `app/Services/ApiClient.php`
Cliente HTTP centralizado usando cURL (compatible con PHP 7.3). Toda petición a APIs externas pasa por este servicio.

**Métodos principales:**
- `get($url, $params = [])`: Petición GET. Devuelve array decodificado del JSON.
- `post($url, $data = [])`: Petición POST con body JSON. Devuelve array decodificado.

**Responsabilidades:**
- Construir la URL completa a partir de la URL base (de `Config/api.php`) + endpoint.
- Configurar headers (`Content-Type: application/json`, `Accept: application/json`).
- Manejar errores HTTP (timeout, 500, conexión rechazada) y devolver `null` o lanzar excepción controlada.
- Log de errores vía `error_log()`.

**Ejemplo de uso desde un Controller:**
```php
$apiConfig = require __DIR__ . '/../Config/api.php';
$client = new ApiClient($apiConfig['reconexiones']['base_url']);
$reconexiones = $client->get('/socios/23807/reconexiones');
```

## 4. Archivos que NO se crean
- ~~`app/Models/Trabajo.php`~~ — Los trabajos no se almacenan localmente.
- ~~`app/Models/Observacion.php`~~ — Las observaciones no se manejan localmente.

## 5. Consideraciones Técnicas
- Mantener compatibilidad estricta con PHP 7.3 (no usar `str_contains`, `match`, etc.).
- `ApiClient` debe ser instanciable con diferentes URLs base para soportar las dos APIs (reconexiones y reclamos).
- No acoplar `ApiClient` a un Controller específico — debe ser reutilizable.
