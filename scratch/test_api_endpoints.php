<?php
require_once __DIR__ . '/../app/Services/ApiClient.php';
$config = require __DIR__ . '/../app/Config/api.php';
use App\Services\ApiClient;

$client = new ApiClient($config['reclamos']['base_url']);

// Probar GET /reclamos/20
echo "--- GET /reclamos/20 ---\n";
$r = $client->get('/reclamos/20');
print_r($r);

// Probar OPTIONS /reclamos/20
echo "--- OPTIONS /reclamos/20 ---\n";
$ch = curl_init('http://api.cosmol.com.bo/api-consultas/reclamos/20');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$res = curl_exec($ch);
echo $res . "\n";
curl_close($ch);

// Probar PATCH /reclamos/20
echo "--- PATCH /reclamos/20 ---\n";
$ch = curl_init('http://api.cosmol.com.bo/api-consultas/reclamos/20');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['glosa' => 'test']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
echo "PATCH response: " . $res . "\n";
curl_close($ch);

// Probar POST /reclamos/20
echo "--- POST /reclamos/20 ---\n";
$ch = curl_init('http://api.cosmol.com.bo/api-consultas/reclamos/20');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['glosa' => 'test']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
echo "POST response: " . $res . "\n";
curl_close($ch);
