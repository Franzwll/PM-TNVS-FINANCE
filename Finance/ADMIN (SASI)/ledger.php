<?php
require_once __DIR__ . '/auth.php';
require_login();
require __DIR__ . '/db.php';
$user = current_user();

$rows = [];
if ($res = $mysqli->query("SELECT transaction_id, type, amount, description, transaction_date FROM transactions ORDER BY transaction_date DESC, transaction_id DESC LIMIT 100")) {
	while ($row = $res->fetch_assoc()) { $rows[] = $row; }
	$res->free();
}

$totals = [];
if ($res = $mysqli->query("SELECT type, COALESCE(SUM(amount),0) AS total FROM transactions GROUP BY type")) {
	while ($r = $res->fetch_assoc()) { $totals[$r['type']] = (float)$r['total']; }
	$res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>General Ledger - TNVS</title>
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
				<a class="nav active" href="ledger.php"><span>General Ledger</span></a>
				<a class="nav" href="accounts_receivable.php"><span>Accounts Receivable</span></a>
				<a class="nav" href="accounts_payable.php"><span>Accounts Payable</span></a>
				<?php if ((($user['role'] ?? '') === 'admin')): ?>
				<a class="nav" href="bank_recon.php"><span>Bank Reconciliation</span></a>
				<?php endif; ?>
			</div>
			<div class="group">
				<h4>Financial Operations</h4>
				<a class="nav" href="disbursement.php"><span>Disbursement</span></a>
				<a class="nav" href="collection.php"><span>Collection</span></a>
				<a class="nav" href="budget.php"><span>Budget</span></a>
			</div>
			<div class="group">
				<h4>Administrative System</h4>
				<?php if ((($user['role'] ?? '') === 'admin')): ?>
				<a class="nav" href="users.php"><span>Users</span></a>
				<?php endif; ?>
			</div>
			<div class="group">
				
			</div>
		</nav>
		<div id="overlay" class="overlay"></div>
		<main class="content" id="mainContent">
			<section class="section active">
				<h3>General Ledger</h3>
				<div class="cards">
					<div class="card">
						<h4>Totals by Type</h4>
						<div class="breakdown">
							<?php foreach ([
								'fare' => 'Fare',
								'platform_fee' => 'Platform Fee',
								'fuel' => 'Fuel',
								'lease' => 'Lease',
								'incentive' => 'Incentive',
								'payout' => 'Payout',
							] as $key => $label): $val = (float)($totals[$key] ?? 0); $width = min(100, max(0, round($val !== 0 ? 60 : 10))); ?>
							<div class="breakdown-row">
								<div class="label"><?php echo htmlspecialchars($label); ?></div>
								<div class="barline"><span style="--final-width: <?php echo $width; ?>%;"></span></div>
								<div class="value">₱<?php echo number_format($val, 2); ?></div>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="chart-card">
					<h4>Recent Transactions</h4>
					<div class="table-wrap">
						<table class="table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Type</th>
									<th>Amount</th>
									<th>Description</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($rows as $r): ?>
								<tr>
									<td><?php echo (int)$r['transaction_id']; ?></td>
									<td><?php echo htmlspecialchars($r['type']); ?></td>
									<td>₱<?php echo number_format((float)$r['amount'], 2); ?></td>
									<td><?php echo htmlspecialchars($r['description'] ?? ''); ?></td>
									<td><?php echo htmlspecialchars($r['transaction_date']); ?></td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($rows)): ?>
								<tr><td colspan="5">No transactions found.</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</section>
		</main>
	</div>
	<script src="app.js"></script>
</body>
</html>
