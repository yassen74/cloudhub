<?php
mysqli_report(MYSQLI_REPORT_OFF);

if (!function_exists('fayen_db_log')) {
    function fayen_db_log(string $message): void
    {
        $uri = $_SERVER['REQUEST_URI'] ?? 'cli';
        error_log('[FayenDB] ' . $uri . ' ' . $message);
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'fayen';
$pass = getenv('DB_PASSWORD');
if ($pass === false || $pass === '') {
    $pass = getenv('DB_PASS') ?: 'itverse123';
}
$name = getenv('DB_NAME') ?: 'lms_db';
$port = (int) (getenv('DB_PORT') ?: 3306);
$connectTimeout = max(1, (int) (getenv('DB_CONNECT_TIMEOUT') ?: 3));
$readTimeout = max(1, (int) (getenv('DB_READ_TIMEOUT') ?: 5));
$connectStart = microtime(true);

$conn = mysqli_init();

if ($conn === false) {
    fayen_db_log('mysqli_init failed');
    http_response_code(500);
    exit('Database initialization failed.');
}

mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, $connectTimeout);
if (defined('MYSQLI_OPT_READ_TIMEOUT')) {
    mysqli_options($conn, MYSQLI_OPT_READ_TIMEOUT, $readTimeout);
}

$connected = @mysqli_real_connect($conn, $host, $user, $pass, $name, $port);
$connectMs = (int) round((microtime(true) - $connectStart) * 1000);

if (!$connected) {
    fayen_db_log(
        'connect failed after ' . $connectMs . 'ms to ' . $host . ':' . $port . ' error=' . mysqli_connect_error()
    );
    http_response_code(503);
    exit('Database temporarily unavailable.');
}

if ($connectMs >= 1000) {
    fayen_db_log('slow connect ' . $connectMs . 'ms to ' . $host . ':' . $port);
}

if (!@$conn->set_charset('utf8mb4')) {
    fayen_db_log('set_charset failed: ' . $conn->error);
}
?>
