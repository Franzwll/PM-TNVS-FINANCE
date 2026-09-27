<?php
// Single-file TNVS app: DB + Auth + Views (login, admin, user, dashboard)
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// DB connection
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
$mysqli->set_charset('utf8mb4');

// Auth helpers
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

function user() { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return !empty($_SESSION['user']); }
function is_admin(): bool { return (($_SESSION['user']['role'] ?? '') === 'admin'); }

// Actions
$error = '';
if (($_GET['action'] ?? '') === 'logout') {
	session_unset();
	session_destroy();
	header('Location: index.php');
	exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'login') {
	$email = trim($_POST['email'] ?? '');
	$pass = (string)($_POST['password'] ?? '');
	if (!$email || !$pass) {
		$error = 'Please enter email and password';
	} else {
		// Simple admin-only login: accept only password 'admin'
		if ($pass === 'admin') {
			$_SESSION['user'] = [
				'id' => 0,
				'name' => 'Admin',
				'email' => $email ?: 'admin@example.com',
				'role' => 'admin',
			];
			header('Location: index.php?page=admin');
			return;
		}
		$error = 'Invalid password';
	}
}

// Data helpers
function fetch_value($mysqli, $sql, $default = 0) {
	if (!($res = $mysqli->query($sql))) return $default;
	$row = $res->fetch_row();
	return isset($row[0]) ? (float)$row[0] : $default;
}

$page = $_GET['page'] ?? '';
if (!is_logged_in() && !in_array($page, ['', 'login'], true)) {
	$page = 'login';
}

// Prepare dashboard data if needed
if ($page === '' || $page === 'dashboard') {
	$totalRevenue = fetch_value($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='fare'");
	$totalExpenses = fetch_value($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type IN ('fuel','lease','incentive','payout','platform_fee')");
	$netIncome = $totalRevenue - $totalExpenses;
	$apPending = fetch_value($mysqli, "SELECT COALESCE(SUM(amount_due),0) FROM accountspayable WHERE status='pending'");
	$arPending = fetch_value($mysqli, "SELECT COALESCE(SUM(amount_due),0) FROM accountsreceivable WHERE status IN ('pending','overdue')");
	$recent = [];
	if ($res = $mysqli->query("SELECT transaction_id, type, amount, transaction_date FROM transactions ORDER BY transaction_date DESC, transaction_id DESC LIMIT 10")) {
		while ($row = $res->fetch_assoc()) { $recent[] = $row; }
		$res->free();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>TNVS</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if ($page === 'login' || (!is_logged_in() && $page !== 'admin' && $page !== 'user')): ?>
	<div id="login" class="login">
		<div class="login-card">
			<div class="logo">
				<h1>SAKAYSAYA</h1>
				<p>Finance System Login</p>
			</div>
			<form class="login-form" method="post">
				<input type="hidden" name="do" value="login" />
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
<?php else: ?>
	<div id="app" class="app">
		<header class="app-header">
			<button id="menuToggle" class="icon-btn" aria-label="Toggle menu"><span></span><span></span><span></span></button>
			<div class="brand" role="img" aria-label="TNVS Finance System">
				<svg viewBox="0 0 120 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<text x="10" y="16" font-size="16" font-weight="800" letter-spacing="0.5" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif" fill="currentColor">SAKAYSAYA</text>
				</svg>
			</div>
			<div class="user">
				<span id="currentUser">Welcome, <?php echo htmlspecialchars(user()['name'] ?? 'User'); ?></span>
				<a class="text-btn" href="?action=logout">Logout</a>
			</div>
		</header>
		<nav id="sidebar" class="sidebar">
			<div class="group">
				<h4>Main</h4>
				<a class="nav <?php echo ($page===''||$page==='dashboard')?'active':''; ?>" href="?page=dashboard"><span>Dashboard</span></a>
				<a class="nav" href="ledger.php"><span>General Ledger</span></a>
				<a class="nav" href="accounts_pr.php"><span>Accounts P/R</span></a>
			</div>
			<div class="group">
				<h4>Financial</h4>
				<a class="nav" href="disbursement.php"><span>Disbursement</span></a>
				<a class="nav" href="collection.php"><span>Collection</span></a>
				<a class="nav" href="budget.php"><span>Budget</span></a>
			</div>
			<div class="group">
				<h4>Management</h4>
				<a class="nav <?php echo ($page==='admin')?'active':''; ?>" href="?page=admin"><span>Admin</span></a>
				<a class="nav" href="users.php"><span>Users</span></a>
				<a class="nav <?php echo ($page==='user')?'active':''; ?>" href="?page=user"><span>User</span></a>
			</div>
			<div class="group">
				<h4>Tools</h4>
				<a class="nav" href="chat.php"><span>AI Chat</span></a>
			</div>
		</nav>
		<div id="overlay" class="overlay"></div>
		<main class="content" id="mainContent">
			<?php if ($page === '' || $page === 'dashboard'): ?>
			<section id="dashboard" class="section active">
				<h3>Dashboard Overview</h3>
				<div class="cards">
					<div class="card success">
						<h4>Total Revenue</h4>
						<div class="big">₱<?php echo number_format($totalRevenue, 2); ?></div>
						<div class="card-trend positive">Up to date</div>
					</div>
					<div class="card warning">
						<h4>Total Expenses</h4>
						<div class="big">₱<?php echo number_format($totalExpenses, 2); ?></div>
						<div class="card-trend">Tracked</div>
					</div>
					<div class="card">
						<h4>Net Income</h4>
						<div class="big">₱<?php echo number_format($netIncome, 2); ?></div>
						<div class="card-trend <?php echo ($netIncome >= 0 ? 'positive' : ''); ?>"><?php echo $netIncome >= 0 ? 'Positive' : 'Negative'; ?></div>
					</div>
					<div class="card">
						<h4>AR Pending</h4>
						<div class="big">₱<?php echo number_format($arPending, 2); ?></div>
						<div class="card-trend">Receivables</div>
					</div>
					<div class="card">
						<h4>AP Pending</h4>
						<div class="big">₱<?php echo number_format($apPending, 2); ?></div>
						<div class="card-trend">Payables</div>
					</div>
				</div>
				<div class="analytics">
					<div class="chart-card">
						<h4>Recent Transactions</h4>
						<div class="table-wrap">
							<table class="table" id="txTable">
								<thead>
									<tr>
										<th>ID</th>
										<th>Type</th>
										<th>Amount</th>
										<th>Date</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach (($recent ?? []) as $r): ?>
									<tr>
										<td><?php echo (int)$r['transaction_id']; ?></td>
										<td><?php echo htmlspecialchars($r['type']); ?></td>
										<td>₱<?php echo number_format((float)$r['amount'], 2); ?></td>
										<td><?php echo htmlspecialchars($r['transaction_date']); ?></td>
									</tr>
									<?php endforeach; ?>
									<?php if (empty($recent)): ?>
									<tr><td colspan="4">No transactions found.</td></tr>
									<?php endif; ?>
							</tbody>
							</table>
						</div>
					</div>
					<div class="chart-card">
						<h4>Summary</h4>
						<p>Connected to database: <strong><?php echo htmlspecialchars($DB_NAME); ?></strong></p>
					</div>
				</div>
			</section>
			<?php elseif ($page === 'admin'): ?>
			<?php if (!is_admin()) { echo '<section class="section active"><h3>Forbidden</h3><p>Admin only.</p></section>'; } else { ?>
			<section class="section active">
				<h3>Admin Panel</h3>
				<p>You are logged in as <strong>admin</strong>.</p>
				<p>Go to <a href="?page=dashboard">Dashboard</a>.</p>
			</section>
			<?php } ?>
			<?php elseif ($page === 'user'): ?>
			<section class="section active">
				<h3>User Panel</h3>
				<p>You are logged in as: <strong><?php echo htmlspecialchars(user()['role'] ?? 'user'); ?></strong></p>
				<p>Go to <a href="?page=dashboard">Dashboard</a>.</p>
			</section>
			<?php else: ?>
			<section class="section active"><h3>Not Found</h3></section>
			<?php endif; ?>
		</main>
	</div>
<?php endif; ?>

	<script src="app.js"></script>
</body>
</html>

