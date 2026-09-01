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
     * Ejecuta la petición cURL
     *
     * @param string $method GET o POST
     * @param string $url URL completa
     * @param array|null $data Datos opcionales para POST
     * @return array|null
     */
    private function execute($method, $url, $data = null)
    {
        $ch = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ];

        if ($method === 'POST' && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($error) {
            error_log("ApiClient Error ({$method} {$url}): {$error}");
            return null;
        }

        if ($httpCode >= 400) {
            error_log("ApiClient HTTP Error ({$method} {$url}): Status {$httpCode} - Response: {$response}");
            return null;
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ApiClient JSON Decode Error ({$method} {$url}): " . json_last_error_msg());
            return null;
        }

        return $decoded;
    }


    private function buildUrl($endpoint, $params = [])
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
