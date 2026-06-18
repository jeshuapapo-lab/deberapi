<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión.']);
    exit;
}

function obtenerJson(string $url): array {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => 'GeoClima-MultiAPI/1.0',
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $respuesta = curl_exec($curl);
    $codigo = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($respuesta === false || $codigo < 200 || $codigo >= 300) {
        throw new Exception($error ?: "Servicio externo respondió con código $codigo");
    }

    $datos = json_decode($respuesta, true);
    if (!is_array($datos)) {
        throw new Exception('La respuesta externa no contiene JSON válido.');
    }

    return $datos;
}

try {
    $ciudad = trim($_GET['ciudad'] ?? '');
    if ($ciudad === '') {
        throw new Exception('Escribe una ciudad.');
    }

    $geoUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' . rawurlencode($ciudad) . '&count=1&language=es&format=json';
    $geo = obtenerJson($geoUrl);

    if (empty($geo['results'][0])) {
        http_response_code(404);
        echo json_encode(['error' => 'Ciudad no encontrada.']);
        exit;
    }

    $ubicacion = $geo['results'][0];
    $latitud = $ubicacion['latitude'];
    $longitud = $ubicacion['longitude'];
    $codigoPais = strtoupper($ubicacion['country_code'] ?? '');

    $climaUrl = 'https://api.open-meteo.com/v1/forecast?latitude=' . rawurlencode((string)$latitud)
        . '&longitude=' . rawurlencode((string)$longitud)
        . '&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m'
        . '&daily=temperature_2m_max,temperature_2m_min&timezone=auto&forecast_days=1';

    $paisUrl = 'https://restcountries.com/v3.1/alpha/' . rawurlencode($codigoPais)
        . '?fields=name,translations,capital,population,flags,currencies';

    $solUrl = 'https://api.sunrise-sunset.org/json?lat=' . rawurlencode((string)$latitud)
        . '&lng=' . rawurlencode((string)$longitud) . '&formatted=0';

    $clima = obtenerJson($climaUrl);
    $sol = obtenerJson($solUrl);

    try {
        $paisRespuesta = obtenerJson($paisUrl);
        $pais = isset($paisRespuesta[0]) ? $paisRespuesta[0] : $paisRespuesta;
    } catch (Throwable $e) {
        $pais = [
            'name' => ['common' => $ubicacion['country'] ?? $codigoPais],
            'capital' => ['No disponible'],
            'population' => 0,
            'flags' => ['svg' => 'https://flagcdn.com/' . strtolower($codigoPais) . '.svg'],
            'currencies' => []
        ];
    }

    $monedas = [];
    foreach (($pais['currencies'] ?? []) as $moneda) {
        if (!empty($moneda['name'])) {
            $monedas[] = $moneda['name'];
        }
    }

    echo json_encode([
        'ciudad' => $ubicacion['name'],
        'pais' => [
            'nombre' => $pais['translations']['spa']['common'] ?? $pais['name']['common'] ?? ($ubicacion['country'] ?? $codigoPais),
            'capital' => $pais['capital'][0] ?? 'No disponible',
            'poblacion' => (int)($pais['population'] ?? 0),
            'bandera' => $pais['flags']['svg'] ?? $pais['flags']['png'] ?? ('https://flagcdn.com/' . strtolower($codigoPais) . '.svg'),
            'moneda' => $monedas ? implode(', ', $monedas) : 'No disponible'
        ],
        'clima' => [
            'temperatura' => $clima['current']['temperature_2m'],
            'sensacion' => $clima['current']['apparent_temperature'],
            'humedad' => $clima['current']['relative_humidity_2m'],
            'viento' => $clima['current']['wind_speed_10m'],
            'codigo' => $clima['current']['weather_code'],
            'maxima' => $clima['daily']['temperature_2m_max'][0],
            'minima' => $clima['daily']['temperature_2m_min'][0],
            'fecha' => $clima['current']['time'],
            'zonaHoraria' => $clima['timezone'] ?? ($ubicacion['timezone'] ?? 'UTC')
        ],
        'sol' => [
            'amanecer' => $sol['results']['sunrise'] ?? null,
            'atardecer' => $sol['results']['sunset'] ?? null
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>