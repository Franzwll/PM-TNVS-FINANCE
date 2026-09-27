<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin - TNVS</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div id="app" class="app">
		<header class="app-header">
			<button id="menuToggle" class="icon-btn" aria-label="Toggle menu"><span></span><span></span><span></span></button>
			<div class="brand" role="img" aria-label="TNVS Finance System">
				<svg viewBox="0 0 120 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<text x="10" y="16" font-size="16" font-weight="800" letter-spacing="0.5" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif" fill="currentColor">SAKAYSAYA</text>
				</svg>
			</div>
			<div class="user">
				<span id="currentUser">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
				<a class="text-btn" href="logout.php">Logout</a>
			</div>
		</header>
		<nav id="sidebar" class="sidebar">
			<div class="group">
				<h4>Main</h4>
				<a class="nav" href="dashboard.php"><span>Dashboard</span></a>
			</div>
			<div class="group">
				<h4>Accounts Management</h4>
				<a class="nav" href="ledger.php"><span>General Ledger</span></a>
				<a class="nav" href="accounts_receivable.php"><span>Accounts Receivable</span></a>
				<a class="nav" href="accounts_payable.php"><span>Accounts Payable</span></a>
				<a class="nav" href="bank_recon.php"><span>Bank Reconciliation</span></a>
			</div>
			<div class="group">
				<h4>Financial Operations</h4>
				<a class="nav" href="collection.php"><span>Collection</span></a>
				<a class="nav" href="disbursement.php"><span>Disbursement</span></a>
				<a class="nav" href="budget.php"><span>Budget</span></a>
			</div>
			<div class="group">
				<h4>Administrative System</h4>
				<a class="nav" href="users.php"><span>Users</span></a>
			</div>
			<div class="group">
				<h4>Management</h4>
				<a class="nav active" href="admin.php"><span>Admin</span></a>
				<a class="nav" href="users.php"><span>Users</span></a>
			</div>
			<div class="group">
				
			</div>
		</nav>
		<div id="overlay" class="overlay"></div>
		<main class="content" id="mainContent">
			<section class="section active">
				<h3>Admin Panel</h3>
				<p>You are logged in as <strong>admin</strong>.</p>
				<p>Go to <a href="dashboard.php">Dashboard</a>.</p>
			</section>
		</main>
	</div>
	<script src="app.js"></script>
</body>
</html>

