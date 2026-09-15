<?php
require_once __DIR__ . '/../app/Services/ApiClient.php';

$ch = curl_init('http://api.cosmol.com.bo/api-consultas/reclamos/20');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'usuario_conclucion' => 1,
    'glosa' => 'Test patch'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "PATCH HTTP $httpCode: $res\n";
curl_close($ch);
