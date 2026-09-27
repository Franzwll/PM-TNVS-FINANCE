<?php
require_once __DIR__ . '/auth.php';
require_login();
require __DIR__ . '/db.php';
$user = current_user();

function value_of($mysqli, $sql){ if(!($r=$mysqli->query($sql))) return 0; $row=$r->fetch_row(); return (float)($row[0]??0); }
$revenue = value_of($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='fare'");
$expenses = value_of($mysqli, "SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type IN ('fuel','lease','incentive','payout','platform_fee')");
$net = $revenue - $expenses;

$defaultBudget = [
	'Fuel' => 0.25,
	'Lease' => 0.30,
	'Incentives' => 0.10,
	'Platform Fees' => 0.05,
	'Savings' => 0.30,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Budget - TNVS</title>
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
				<a class="nav" href="accounts_pr.php"><span>Accounts Receivable / Payable</span></a>
				<?php if ((($user['role'] ?? '') === 'admin')): ?>
				<a class="nav" href="bank_recon.php"><span>Bank Reconciliation</span></a>
				<?php endif; ?>
			</div>
			<div class="group">
				<h4>Financial Operations</h4>
				<a class="nav" href="disbursement.php"><span>Disbursement</span></a>
				<a class="nav" href="collection.php"><span>Collection</span></a>
				<a class="nav active" href="budget.php"><span>Budget</span></a>
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
				<h3>Budget Planner</h3>
				<div class="cards">
					<div class="card success"><h4>Revenue</h4><div class="big">₱<?php echo number_format($revenue,2); ?></div></div>
					<div class="card warning"><h4>Expenses</h4><div class="big">₱<?php echo number_format($expenses,2); ?></div></div>
					<div class="card"><h4>Net</h4><div class="big">₱<?php echo number_format($net,2); ?></div></div>
				</div>
				<div class="chart-card">
					<h4>Suggested Allocation</h4>
					<div class="breakdown">
						<?php foreach ($defaultBudget as $name=>$pct): $amt=$net*$pct; $w=min(100,max(5,round($pct*100))); ?>
						<div class="breakdown-row"><div class="label"><?php echo htmlspecialchars($name); ?> (<?php echo round($pct*100); ?>%)</div><div class="barline"><span style="--final-width: <?php echo $w; ?>%;"></span></div><div class="value">₱<?php echo number_format($amt,2); ?></div></div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		</main>
	</div>
	<script src="app.js"></script>
</body>
</html>
