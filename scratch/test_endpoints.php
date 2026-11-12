<?php
$endpoints = [
    '/reclamos',
    '/reconexiones',
    '/socios',
    '/consultas',
    '/usuarios',
    '/tipos-reclamo',
    '/tipo_reclamo',
];
foreach ($endpoints as $ep) {
    $ch = curl_init('http://api.cosmol.com.bo/api-consultas' . $ep);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "$ep -> HTTP $code: " . substr(strip_tags($res), 0, 100) . "\n";
    curl_close($ch);
}
