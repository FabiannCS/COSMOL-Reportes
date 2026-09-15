<?php
require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/TrabajoSeguimiento.php';

use App\Models\TrabajoSeguimiento;

try {
    $model = new TrabajoSeguimiento();
    
    // Test Insert
    echo "Registrando seguimiento local...\n";
    $exito = $model->registrarSeguimiento(999, 'reclamo', 'NO CONCLUIDO', 'Prueba de seguimiento');
    echo "Exito: " . ($exito ? 'Si' : 'No') . "\n";
    
    // Test Select
    echo "Obteniendo seguimiento local...\n";
    $seg = $model->obtenerSeguimiento(999, 'reclamo');
    print_r($seg);
    
    // Test Delete
    echo "Eliminando seguimiento local...\n";
    $exito = $model->eliminarSeguimiento(999, 'reclamo');
    echo "Exito: " . ($exito ? 'Si' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
