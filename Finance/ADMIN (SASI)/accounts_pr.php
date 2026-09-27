<?php
require_once __DIR__ . '/auth.php';
require_login();
require __DIR__ . '/db.php';
$user = current_user();

$ar = [];$ap = [];$arTotal = 0;$apTotal = 0;
if ($res = $mysqli->query("SELECT ar_id, user_id, amount_due, due_date, status FROM accountsreceivable ORDER BY due_date DESC, ar_id DESC LIMIT 100")) {
	while ($row = $res->fetch_assoc()) { $ar[] = $row; if (($row['status'] ?? '') !== 'paid') $arTotal += (float)$row['amount_due']; }
	$res->free();
}
if ($res = $mysqli->query("SELECT ap_id, user_id, amount_due, due_date, status FROM accountspayable ORDER BY due_date DESC, ap_id DESC LIMIT 100")) {
	while ($row = $res->fetch_assoc()) { $ap[] = $row; if (($row['status'] ?? '') !== 'paid') $apTotal += (float)$row['amount_due']; }
	$res->free();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Accounts P/R - TNVS</title>
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
			<section class="section active" id="arSection">
				<h3>Accounts Receivable (Pending ₱<?php echo number_format($arTotal, 2); ?>)</h3>
				<div class="table-wrap">
					<table class="table">
						<thead><tr><th>ID</th><th>User</th><th>Amount Due</th><th>Due Date</th><th>Status</th></tr></thead>
						<tbody>
							<?php foreach ($ar as $r): ?>
							<tr>
								<td><?php echo (int)$r['ar_id']; ?></td>
								<td><?php echo (int)$r['user_id']; ?></td>
								<td>₱<?php echo number_format((float)$r['amount_due'], 2); ?></td>
								<td><?php echo htmlspecialchars($r['due_date']); ?></td>
								<td><?php echo htmlspecialchars($r['status']); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php if (empty($ar)): ?><tr><td colspan="5">No accounts receivable found.</td></tr><?php endif; ?>
						</tbody>
					</table>
				</div>

			</section>

			<section class="section active" id="apSection">
				<h3>Accounts Payable (Pending ₱<?php echo number_format($apTotal, 2); ?>)</h3>
				<div class="table-wrap">
					<table class="table">
						<thead><tr><th>ID</th><th>User</th><th>Amount Due</th><th>Due Date</th><th>Status</th></tr></thead>
						<tbody>
							<?php foreach ($ap as $r): ?>
							<tr>
								<td><?php echo (int)$r['ap_id']; ?></td>
								<td><?php echo (int)$r['user_id']; ?></td>
								<td>₱<?php echo number_format((float)$r['amount_due'], 2); ?></td>
								<td><?php echo htmlspecialchars($r['due_date']); ?></td>
								<td><?php echo htmlspecialchars($r['status']); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php if (empty($ap)): ?><tr><td colspan="5">No accounts payable found.</td></tr><?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</main>
	</div>
	<!-- Both AR and AP are visible; no tab toggling. -->
	<script src="app.js"></script>
</body>
</html>
