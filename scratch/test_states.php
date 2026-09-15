<?php
$states = ['PENDIENTE', 'CONCLUIDO', 'CONCLUIDA', 'NO CONCLUIDO', 'NO_CONCLUIDO', 'NO PROCEDENTE', 'TODOS', 'ALL', '*'];
foreach ($states as $st) {
    $ch = curl_init('http://api.cosmol.com.bo/api-consultas/reclamos?estado=' . urlencode($st));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $json = json_decode($res, true);
    $count = isset($json['datos']) ? count($json['datos']) : (is_array($json) ? count($json) : 0);
    $msg = isset($json['mensaje']) ? $json['mensaje'] : '';
    echo "Reclamos estado='$st' -> HTTP $code (count=$count, msg=$msg)\n";
    curl_close($ch);
}

foreach ($states as $st) {
    $ch = curl_init('http://api.cosmol.com.bo/api-consultas/reconexiones?estado=' . urlencode($st));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $json = json_decode($res, true);
    $count = isset($json['datos']) ? count($json['datos']) : (is_array($json) ? count($json) : 0);
    $msg = isset($json['mensaje']) ? $json['mensaje'] : '';
    echo "Reconexiones estado='$st' -> HTTP $code (count=$count, msg=$msg)\n";
    curl_close($ch);
}
