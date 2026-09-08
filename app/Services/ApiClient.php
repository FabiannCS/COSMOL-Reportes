<?php

namespace App\Services;

use Exception;

/**
 * Servicio cliente HTTP centralizado para consumir APIs externas.
 * Utiliza cURL y está diseñado para ser compatible con PHP 7.3.
 */
class ApiClient
{

    private $baseUrl;

    public function __construct($baseUrl)
    {
        // Aseguramos que la url base no termine en slash
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Ejecuta una petición HTTP GET
     *
     * @param string $endpoint Endpoint relativo (ej. /socios/23807/reconexiones)
     * @param array $params Parámetros query opcionales
     * @return array|null Devuelve el array decodificado, o null en caso de error
     */
    public function get($endpoint, $params = [])
    {
        $url = $this->buildUrl($endpoint, $params);
        return $this->execute('GET', $url);
    }

    public function post($endpoint, $data = [])
    {
        $url = $this->buildUrl($endpoint);
        return $this->execute('POST', $url, $data);
    }

    public function put($endpoint, $data = [])
    {
        $url = $this->buildUrl($endpoint);
        return $this->execute('PUT', $url, $data);
    }

    /**
     * Ejecuta la petición cURL individual
     *
     * @param string $method GET, POST o PUT
     * @param string $url URL completa
     * @param array|null $data Datos opcionales para POST/PUT
     * @return array|null
     */
    private function execute($method, $url, $data = null)
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            CURLOPT_ENCODING       => '',
            CURLOPT_TCP_NODELAY    => 1,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ];

        if (($method === 'POST' || $method === 'PUT') && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $this->parseResponse($response, $error, $httpCode, "{$method} {$url}");
    }

    /**
     * Ejecuta múltiples peticiones GET en paralelo sobre este mismo cliente
     *
     * @param array $endpoints Array asociativo ['clave' => '/endpoint?params']
     * @return array Array asociativo con las respuestas decodificadas ['clave' => array|null]
     */
    public function getMultiple(array $endpoints)
    {
        $requests = [];
        foreach ($endpoints as $key => $endpoint) {
            $requests[$key] = [
                'url' => $this->buildUrl($endpoint)
            ];
        }

        return self::executeMulti($requests);
    }

    /**
     * Ejecuta múltiples peticiones GET concurrentes desde diferentes instancias de ApiClient
     *
     * @param array $clientRequests Array asociativo ['clave' => [$apiClientInstance, '/endpoint?query']]
     * @return array Array asociativo con las respuestas decodificadas ['clave' => array|null]
     */
    public static function getMultiFromClients(array $clientRequests)
    {
        $requests = [];
        foreach ($clientRequests as $key => $item) {
            if (is_array($item) && count($item) >= 2 && $item[0] instanceof self) {
                /** @var self $client */
                $client = $item[0];
                $endpoint = $item[1];
                $params = isset($item[2]) && is_array($item[2]) ? $item[2] : [];
                $requests[$key] = [
                    'url' => $client->buildUrl($endpoint, $params)
                ];
            }
        }

        return self::executeMulti($requests);
    }

    /**
     * Ejecutor interno de curl_multi para procesamiento paralelo de alto rendimiento
     *
     * @param array $requests Array asociativo ['clave' => ['url' => '...']]
     * @return array ['clave' => array|null]
     */
    private static function executeMulti(array $requests)
    {
        if (empty($requests)) {
            return [];
        }

        $mh = curl_multi_init();
        $handles = [];
        $results = [];

        foreach ($requests as $key => $req) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $req['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT        => 6,
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                CURLOPT_ENCODING       => '',
                CURLOPT_TCP_NODELAY    => 1,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]
            ]);

            curl_multi_add_handle($mh, $ch);
            $handles[$key] = [
                'handle' => $ch,
                'url'    => $req['url']
            ];
        }

        // Ejecutar las peticiones concurrentemente
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc === CURLM_OK) {
            if (curl_multi_select($mh, 0.5) !== -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc === CURLM_CALL_MULTI_PERFORM);
            }
        }

        // Procesar respuestas
        $dummy = new self('');
        foreach ($handles as $key => $info) {
            $ch = $info['handle'];
            $url = $info['url'];
            
            $response = curl_multi_getcontent($ch);
            $error    = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $results[$key] = $dummy->parseResponse($response, $error, $httpCode, "GET {$url}");
        }

        curl_multi_close($mh);

        return $results;
    }

    /**
     * Decodifica y limpia la respuesta JSON
     */
    private function parseResponse($response, $error, $httpCode, $context)
    {
        if ($error) {
            error_log("ApiClient Error ({$context}): {$error}");
            return null;
        }

        if ($httpCode >= 400) {
            error_log("ApiClient HTTP Error ({$context}): Status {$httpCode} - Response: {$response}");
            return null;
        }

        // Limpiar posible BOM UTF-8 y espacios iniciales que causan errores de sintaxis en json_decode
        $cleanResponse = trim((string)$response);
        if (substr($cleanResponse, 0, 3) === "\xEF\xBB\xBF") {
            $cleanResponse = substr($cleanResponse, 3);
        }
        $cleanResponse = trim($cleanResponse);

        if ($cleanResponse === '') {
            return [];
        }

        $decoded = json_decode($cleanResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ApiClient JSON Decode Error ({$context}): " . json_last_error_msg());
            return null;
        }

        return $decoded;
    }

    public function buildUrl($endpoint, $params = [])
    {
        // Aseguramos que el endpoint empiece con slash
        $endpoint = '/' . ltrim($endpoint, '/');
        $url = $this->baseUrl . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
