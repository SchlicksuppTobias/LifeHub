<?php
/**
 * weather.php
 *
 * GET  /api/weather.php          → Gespeicherte Location laden + Wetterdaten holen
 * POST /api/weather.php          → Neue PLZ/Stadt speichern, dann Wetterdaten zurückgeben
 *
 * Benötigt eine Tabelle:
 *   CREATE TABLE user_weather_location (
 *     user_id     INT PRIMARY KEY,
 *     location    VARCHAR(255) NOT NULL,
 *     lat         DECIMAL(9,6),
 *     lon         DECIMAL(9,6),
 *     updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 *   );
 *
 * APIs (kostenlos, kein Key nötig):
 *   Geocoding : https://nominatim.openstreetmap.org
 *   Wetter    : https://api.open-meteo.com
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── DB-Verbindung ────────────────────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'your_database';
$user   = 'your_user';
$pass   = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Datenbankfehler: ' . $e->getMessage()]);
    exit;
}

// ── Hard-coded User ID ───────────────────────────────────────────────────────
$userId = 1;

// ── Helper: HTTP GET ─────────────────────────────────────────────────────────
function httpGet(string $url): ?array {
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 8,
            'header'  => "User-Agent: WeatherApp/1.0 (your@email.com)\r\n",
        ]
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    return json_decode($raw, true);
}

// ── Helper: Geocodierung via Nominatim ───────────────────────────────────────
function geocode(string $query): ?array {
    $url  = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' . urlencode($query);
    $data = httpGet($url);
    if (empty($data[0])) return null;
    return [
        'lat'          => (float)$data[0]['lat'],
        'lon'          => (float)$data[0]['lon'],
        'display_name' => $data[0]['display_name'],
        'city'         => extractCity($data[0]),
    ];
}

function extractCity(array $place): string {
    // Nominatim liefert display_name als kommaseparierte Liste
    // Ersten Teil (Stadtname / PLZ-Ort) zurückgeben
    $parts = explode(',', $place['display_name']);
    return trim($parts[0]);
}

// ── Helper: Wetterdaten via Open-Meteo ───────────────────────────────────────
function fetchWeather(float $lat, float $lon): ?array {
    $url = 'https://api.open-meteo.com/v1/forecast?'
         . "latitude={$lat}&longitude={$lon}"
         . '&current=temperature_2m,relative_humidity_2m,apparent_temperature,'
         . 'weather_code,wind_speed_10m,wind_direction_10m,precipitation,is_day'
         . '&daily=temperature_2m_max,temperature_2m_min,weather_code,'
         . 'precipitation_sum,wind_speed_10m_max,sunrise,sunset'
         . '&timezone=auto'
         . '&forecast_days=7';

    $data = httpGet($url);
    if (!$data || isset($data['error'])) return null;
    return $data;
}

// ── WMO Weather-Code → Label + Icon-Emoji ────────────────────────────────────
function weatherInfo(int $code): array {
    $map = [
        0  => ['Klarer Himmel',          '☀️'],
        1  => ['Überwiegend klar',        '🌤️'],
        2  => ['Teilweise bewölkt',       '⛅'],
        3  => ['Bedeckt',                 '☁️'],
        45 => ['Nebel',                   '🌫️'],
        48 => ['Raureif-Nebel',           '🌫️'],
        51 => ['Leichter Nieselregen',    '🌦️'],
        53 => ['Mäßiger Nieselregen',     '🌦️'],
        55 => ['Starker Nieselregen',     '🌧️'],
        61 => ['Leichter Regen',          '🌧️'],
        63 => ['Mäßiger Regen',           '🌧️'],
        65 => ['Starker Regen',           '🌧️'],
        71 => ['Leichter Schneefall',     '🌨️'],
        73 => ['Mäßiger Schneefall',      '🌨️'],
        75 => ['Starker Schneefall',      '❄️'],
        77 => ['Schneekörner',            '🌨️'],
        80 => ['Leichte Schauer',         '🌦️'],
        81 => ['Mäßige Schauer',          '🌧️'],
        82 => ['Heftige Schauer',         '⛈️'],
        85 => ['Schneeschauer',           '🌨️'],
        86 => ['Starke Schneeschauer',    '❄️'],
        95 => ['Gewitter',                '⛈️'],
        96 => ['Gewitter mit Hagel',      '⛈️'],
        99 => ['Starkes Gewitter + Hagel','⛈️'],
    ];
    return $map[$code] ?? ['Unbekannt', '🌡️'];
}

// ── Windrichtung → Himmelsrichtung ───────────────────────────────────────────
function windDir(float $deg): string {
    $dirs = ['N','NO','O','SO','S','SW','W','NW'];
    return $dirs[round($deg / 45) % 8];
}

// ── Wochentag ─────────────────────────────────────────────────────────────────
function weekday(string $dateStr): string {
    $days = ['So','Mo','Di','Mi','Do','Fr','Sa'];
    return $days[(int)date('w', strtotime($dateStr))];
}

// ── Response zusammenbauen ───────────────────────────────────────────────────
function buildResponse(array $geo, array $raw): array {
    $cur  = $raw['current'];
    $dai  = $raw['daily'];
    $code = (int)$cur['weather_code'];
    [$label, $icon] = weatherInfo($code);

    $forecast = [];
    for ($i = 0; $i < count($dai['time']); $i++) {
        [$dLabel, $dIcon] = weatherInfo((int)$dai['weather_code'][$i]);
        $forecast[] = [
            'date'      => $dai['time'][$i],
            'weekday'   => weekday($dai['time'][$i]),
            'max'       => round($dai['temperature_2m_max'][$i], 1),
            'min'       => round($dai['temperature_2m_min'][$i], 1),
            'precip'    => round($dai['precipitation_sum'][$i], 1),
            'wind_max'  => round($dai['wind_speed_10m_max'][$i], 1),
            'code'      => (int)$dai['weather_code'][$i],
            'label'     => $dLabel,
            'icon'      => $dIcon,
            'sunrise'   => substr($dai['sunrise'][$i], 11, 5),
            'sunset'    => substr($dai['sunset'][$i], 11, 5),
        ];
    }

    return [
        'success'  => true,
        'location' => [
            'query'        => $geo['city'],
            'display_name' => $geo['display_name'],
            'lat'          => $geo['lat'],
            'lon'          => $geo['lon'],
        ],
        'current'  => [
            'temp'        => round($cur['temperature_2m'], 1),
            'feels_like'  => round($cur['apparent_temperature'], 1),
            'humidity'    => (int)$cur['relative_humidity_2m'],
            'wind_speed'  => round($cur['wind_speed_10m'], 1),
            'wind_dir'    => windDir((float)$cur['wind_direction_10m']),
            'precip'      => round($cur['precipitation'], 1),
            'code'        => $code,
            'label'       => $label,
            'icon'        => $icon,
            'is_day'      => (bool)$cur['is_day'],
        ],
        'forecast' => $forecast,
        'timezone' => $raw['timezone'] ?? 'auto',
    ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// GET — gespeicherte Location laden und Wetter abrufen
// ═══════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $pdo->prepare('SELECT location, lat, lon FROM user_weather_location WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row) {
        // Noch keine Location gespeichert
        echo json_encode(['success' => true, 'location' => null]);
        exit;
    }

    $geo = [
        'lat'          => (float)$row['lat'],
        'lon'          => (float)$row['lon'],
        'city'         => $row['location'],
        'display_name' => $row['location'],
    ];

    $raw = fetchWeather($geo['lat'], $geo['lon']);
    if (!$raw) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Wetterdaten konnten nicht geladen werden.']);
        exit;
    }

    echo json_encode(buildResponse($geo, $raw));
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
// POST — neue Location speichern und Wetter zurückgeben
// ═══════════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $body = json_decode(file_get_contents('php://input'), true);
    $query = trim($body['location'] ?? '');

    if ($query === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Bitte eine Stadt oder PLZ eingeben.']);
        exit;
    }

    // Geocodieren
    $geo = geocode($query);
    if (!$geo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Ort nicht gefunden. Bitte andere Schreibweise versuchen.']);
        exit;
    }

    // In DB speichern (INSERT ... ON DUPLICATE KEY UPDATE)
    $stmt = $pdo->prepare('
        INSERT INTO user_weather_location (user_id, location, lat, lon)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE location = VALUES(location), lat = VALUES(lat), lon = VALUES(lon)
    ');
    $stmt->execute([$userId, $geo['city'], $geo['lat'], $geo['lon']]);

    // Wetterdaten holen
    $raw = fetchWeather($geo['lat'], $geo['lon']);
    if (!$raw) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Geocodierung erfolgreich, aber Wetterdaten nicht verfügbar.']);
        exit;
    }

    echo json_encode(buildResponse($geo, $raw));
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Methode nicht erlaubt.']);
