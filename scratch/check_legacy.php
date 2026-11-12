<?php
require_once __DIR__ . '/../app/Services/ApiClient.php';
$config = require __DIR__ . '/../app/Config/api.php';
use App\Services\ApiClient;

$clientRecl = new ApiClient($config['reclamos']['base_url']);
$resConc = $clientRecl->get('/reclamos?estado=CONCLUIDO');
$reclamosConcluidos = $resConc['datos'] ?? [];

echo "Reclamos concluidos en API con marcas:\n";
foreach ($reclamosConcluidos as $r) {
    $st = ApiClient::determinarEstadoTrabajo($r);
    if ($st !== 'CONCLUIDO') {
        echo "ID: {$r['id_reclamo']} - Estado calculado: $st - Glosa: {$r['glosa']}\n";
    }
}

$clientRec = new ApiClient($config['reconexiones']['base_url']);
$resRecConc = $clientRec->get('/reconexiones?estado=CONCLUIDA');
$recConcluidas = $resRecConc['datos'] ?? [];

echo "\nReconexiones concluidas en API con marcas:\n";
foreach ($recConcluidas as $r) {
    $st = ApiClient::determinarEstadoTrabajo($r);
    if ($st !== 'CONCLUIDA' && $st !== 'CONCLUIDO') {
        echo "ID: {$r['id_reconexion']} - Estado calculado: $st - Glosa: {$r['glosa']}\n";
    }
}
