<?php
require_once __DIR__ . '/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email'] ?? '');
	$password = (string)($_POST['password'] ?? '');
	if ($email && $password) {
		// Simple admin-only login: accept only password 'admin'
		if ($password === 'admin') {
			if (session_status() === PHP_SESSION_NONE) { session_start(); }
			$_SESSION['user'] = [
				'id' => 0,
				'name' => 'Admin',
				'email' => $email ?: 'admin@example.com',
				'role' => 'admin',
			];
			header('Location: admin.php');
			exit;
		}
		$error = 'Invalid password';
	} else {
		$error = 'Please enter email and password';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login - TNVS</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div id="login" class="login">
		<div class="login-card">
			<div class="logo">
				<h1>SAKAYSAYA</h1>
				<p>Finance System Login</p>
			</div>
			<form class="login-form" method="post" action="">
				<label>Email
					<input type="email" name="email" placeholder="you@example.com" required />
				</label>
				<label>Password
					<input type="password" name="password" placeholder="••••••••" required />
				</label>
				<div class="error-message"><?php echo htmlspecialchars($error); ?></div>
				<button type="submit">Sign in</button>
			</form>
		</div>
	</div>
	<script src="app.js"></script>
</body>
</html>

