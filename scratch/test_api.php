<?php
require_once __DIR__ . '/../app/Services/ApiClient.php';
$config = require __DIR__ . '/../app/Config/api.php';

use App\Services\ApiClient;

echo "Config:\n";
print_r($config);

$clientRecl = new ApiClient($config['reclamos']['base_url']);
$resPend = $clientRecl->get('/reclamos?estado=PENDIENTE');
echo "\nReclamos PENDIENTES count: " . (isset($resPend['datos']) ? count($resPend['datos']) : 0) . "\n";

$resConc = $clientRecl->get('/reclamos?estado=CONCLUIDO');
echo "Reclamos CONCLUIDOS count: " . (isset($resConc['datos']) ? count($resConc['datos']) : 0) . "\n";

// Muestra los últimos 3 reclamos concluidos
if (!empty($resConc['datos'])) {
    echo "\nÚltimos 3 Reclamos CONCLUIDOS:\n";
    $ultimos = array_slice($resConc['datos'], -3);
    foreach ($ultimos as $item) {
        echo "ID: {$item['id_reclamo']}, Estado: {$item['estado']}, Glosa: {$item['glosa']}\n";
    }
}

// Muestra los últimos 3 reclamos pendientes
if (!empty($resPend['datos'])) {
    echo "\nÚltimos 3 Reclamos PENDIENTES:\n";
    $ultimos = array_slice($resPend['datos'], -3);
    foreach ($ultimos as $item) {
        echo "ID: {$item['id_reclamo']}, Estado: {$item['estado']}, Glosa: {$item['glosa']}\n";
    }
}
