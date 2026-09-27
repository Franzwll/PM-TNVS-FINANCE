<?php
// Basic PHP dashboard that reads from the `finance` schema
require __DIR__ . '/db.php';

// Helper to fetch a single value safely
function fetch_value($mysqli, $sql, $default = 0) {
	if (!($res = $mysqli->query($sql))) {
		return $default;
	}
	$row = $res->fetch_row();
	return isset($row[0]) ? (float)$row[0] : $default;
}

// Totals and simple KPIs
$totalRevenue = fetch_value($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='fare'");
$totalExpenses = fetch_value($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type IN ('fuel','lease','incentive','payout','platform_fee')");
$netIncome = $totalRevenue - $totalExpenses;

$apPending = fetch_value($mysqli, "SELECT COALESCE(SUM(amount_due),0) FROM accountspayable WHERE status='pending'");
$arPending = fetch_value($mysqli, "SELECT COALESCE(SUM(amount_due),0) FROM accountsreceivable WHERE status IN ('pending','overdue')");

// Simple recent transactions
$recent = [];
if ($res = $mysqli->query("SELECT transaction_id, type, amount, transaction_date FROM transactions ORDER BY transaction_date DESC, transaction_id DESC LIMIT 10")) {
	while ($row = $res->fetch_assoc()) {
		$recent[] = $row;
	}
	$res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>TNVS Dashboard</title>
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
				<span id="currentUser">Welcome</span>
				<a href="logout.php" class="text-btn">Logout</a>
			</div>
		</header>

		<nav id="sidebar" class="sidebar">
			<div class="group">
				<h4>Main</h4>
				<a class="nav active" href="dashboard.php"><span>Dashboard</span></a>
			</div>
			<div class="group">
				<h4>Accounts Management</h4>
				<a class="nav" href="ledger.php"><span>General Ledger</span></a>
				<a class="nav" href="accounts_receivable.php"><span>Accounts Receivable</span></a>
				<a class="nav" href="accounts_payable.php"><span>Accounts Payable</span></a>
				<?php if ((($_SESSION['user']['role'] ?? '') === 'admin')): ?>
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
				<?php if ((($_SESSION['user']['role'] ?? '') === 'admin')): ?>
				<a class="nav" href="users.php"><span>Users</span></a>
				<?php endif; ?>
			</div>
			<div class="group">
				
			</div>
		</nav>
		<div id="overlay" class="overlay"></div>

		<main class="content" id="mainContent">
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
									<?php foreach ($recent as $r): ?>
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
		</main>
	</div>
	<script src="app.js"></script>
</body>
</html>

