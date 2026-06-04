<?php
// URLs de los microservicios
// En local apuntan a localhost, en Kubernetes al nombre del servicio
define('AUTH_SERVICE',    getenv('AUTH_SERVICE_URL')    ?: 'http://localhost:8001');
define('CLIENTS_SERVICE', getenv('CLIENTS_SERVICE_URL') ?: 'http://localhost:8002');

/**
 * Hace una petición HTTP a un microservicio
 * $method  = GET | POST | PUT | DELETE
 * $url     = URL completa
 * $data    = array con el body (para POST/PUT)
 * $token   = JWT opcional
 */
function api_request(string $method, string $url, array $data = [], string $token = ''): array {
    $ch = curl_init($url);

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true) ?? []
    ];
}
