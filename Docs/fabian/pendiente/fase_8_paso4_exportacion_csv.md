# Fase 8 — Paso 4: Exportación a CSV

> **Dependencias:** Pasos 1, 2 y 3 completados.
> **Archivos afectados:** `app/Controllers/ReporteController.php` (modificar método `exportar()`).

---

## Objetivo

Permitir al usuario descargar un archivo de hoja de cálculo ligero (CSV) que contenga la totalidad de las consultas que coinciden con los filtros aplicados en pantalla, sin límites de paginación.

---

## Tareas

### 4.1 Implementar el método `exportar()` en el Controlador

En `app/Controllers/ReporteController.php`, construir el método que responde a la ruta `/reportes/exportar`:

1. **Capturar Filtros:**
   - Recibir por `GET` los parámetros `fecha_inicio`, `fecha_fin` e `id_tipo`.
   - (El parámetro de página `p` debe ser ignorado, ya que la exportación extrae todos los registros que cumplan el filtro).
2. **Obtener Datos Crudos:**
   - Llamar a `$modelo->getAllConsultasExport($filtros)` para obtener el arreglo completo de resultados de la base de datos.
3. **Configurar Cabeceras HTTP:**
   - Establecer las cabeceras para forzar la descarga del archivo:
     ```php
     header('Content-Type: text/csv; charset=utf-8');
     header('Content-Disposition: attachment; filename="reporte_consultas_' . date('Ymd_His') . '.csv"');
     ```
4. **Generar el CSV (Stream):**
   - Abrir el flujo de salida de PHP: `$output = fopen('php://output', 'w');`
   - Escribir la marca de orden de bytes (BOM) para que Excel reconozca el UTF-8 correctamente:
     ```php
     fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
     ```
   - Escribir la fila de encabezados:
     ```php
     fputcsv($output, ['ID Consulta', 'Cód. Socio', 'Nombres', 'Tipo', 'Fecha', 'Hora', 'Atendido por']);
     ```
   - Iterar sobre el arreglo de consultas y escribir cada fila:
     ```php
     foreach ($consultas as $row) {
         fputcsv($output, [
             $row['id_consulta'],
             $row['codigo_socio'],
             $row['nombres'],
             $row['tipo'],
             $row['fecha_consulta'],
             $row['hora_consulta'],
             $row['username'] ?? 'Chatbot' // Fallback si no hay usuario
         ]);
     }
     ```
   - Cerrar el flujo: `fclose($output);`
   - Finalizar ejecución (`exit();`) para evitar que el framework intente cargar alguna vista posterior.

---

## Verificación

```bash
docker exec cosmol_app bash -c "php -l app/Controllers/ReporteController.php"
```
No deben haber errores de sintaxis. Posteriormente, probar el flujo visual: aplicar un filtro en la web, presionar "Exportar a CSV" y verificar que el archivo descargado se abre correctamente en Excel sin caracteres extraños (ñ, tildes).
