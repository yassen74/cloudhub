<?php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_port = (int) (getenv('DB_PORT') ?: 3306);
$db_user = getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'itverse');
$db_password = getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: 'itverse123');
$db_name = getenv('DB_NAME') ?: 'lms_db';

$conn = @new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database connection failed.');
}

$conn->set_charset('utf8mb4');
?>
