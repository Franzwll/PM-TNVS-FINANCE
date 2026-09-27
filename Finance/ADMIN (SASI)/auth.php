<?php
// Session and authentication helpers
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/db.php';

function is_logged_in(): bool {
	return !empty($_SESSION['user']);
}

function current_user() {
	return $_SESSION['user'] ?? null;
}

function require_login(): void {
	if (!is_logged_in()) {
		header('Location: login.php');
		exit;
	}
}

function require_role(string $role): void {
	require_login();
	$user = current_user();
	if (!$user || ($user['role'] ?? '') !== $role) {
		http_response_code(403);
		echo 'Forbidden';
		exit;
	}
}

function attempt_login(mysqli $mysqli, string $email, string $password): bool {
	$stmt = $mysqli->prepare('SELECT user_id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
	if (!$stmt) return false;
	$stmt->bind_param('s', $email);
	$stmt->execute();
	$res = $stmt->get_result();
	$row = $res->fetch_assoc();
	$stmt->close();
	if (!$row) return false;
	$hash = $row['password_hash'] ?? '';
	if ($hash && password_verify($password, $hash)) {
		$_SESSION['user'] = [
			'id' => (int)$row['user_id'],
			'name' => $row['name'] ?? 'User',
			'email' => $row['email'],
			'role' => $row['role'] ?? 'driver',
		];
		return true;
	}
	return false;
}

?>

