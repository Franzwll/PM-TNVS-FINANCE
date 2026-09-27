<?php
// Simple reusable MySQL connection using mysqli
// Update credentials as needed for your local environment
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'finance';
$DB_PORT = (int)(getenv('DB_PORT') ?: 3306);

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
	http_response_code(500);
	echo 'Database connection failed: ' . htmlspecialchars($mysqli->connect_error);
	exit;
}

// Ensure proper charset
$mysqli->set_charset('utf8mb4');
?>

