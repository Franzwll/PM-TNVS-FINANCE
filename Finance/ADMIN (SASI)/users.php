<?php
require_once __DIR__ . '/auth.php';
require_role('admin');
require __DIR__ . '/db.php';
$user = current_user();

function users_has_column(mysqli $mysqli, string $column): bool {
	if (!$res = $mysqli->query("SHOW COLUMNS FROM users LIKE '" . $mysqli->real_escape_string($column) . "'")) return false;
	$exists = (bool)$res->num_rows;
	$res->free();
	return $exists;
}

$hasAddress = users_has_column($mysqli, 'address');

$success = '';
$error = '';

// Handle update
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['do'] ?? '') === 'update_user') {
	$uid = (int)($_POST['user_id'] ?? 0);
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$role = $_POST['role'] ?? 'driver';
	$plate = trim($_POST['plate_no'] ?? '');
	$ref = trim($_POST['reference_no'] ?? '');
	$address = $hasAddress ? trim($_POST['address'] ?? '') : '';
	if ($role === 'admin') { $plate = ''; $ref = ''; }
	if (!$uid || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$error = 'Please provide valid name and email.';
	} else if (!in_array($role, ['driver','finance_staff','admin'], true)) {
		$error = 'Invalid role provided.';
	} else {
		// Ensure email uniqueness (except for current user)
		if ($stmt = $mysqli->prepare('SELECT 1 FROM users WHERE email = ? AND user_id <> ? LIMIT 1')) {
			$stmt->bind_param('si', $email, $uid);
			$stmt->execute();
			$stmt->store_result();
			if ($stmt->num_rows > 0) { $error = 'Email already in use by another account.'; }
			$stmt->close();
		}
		if ($error === '') {
			if ($hasAddress) {
				if ($stmt = $mysqli->prepare('UPDATE users SET name = ?, email = ?, role = ?, plate_no = ?, reference_no = ?, address = ? WHERE user_id = ?')) {
					$stmt->bind_param('ssssssi', $name, $email, $role, $plate, $ref, $address, $uid);
					$ok = $stmt->execute();
					$stmt->close();
					$success = $ok ? 'User updated successfully.' : 'Failed to update user.';
				} else {
					$error = 'Internal error (prepare update address).';
				}
			} else {
				if ($stmt = $mysqli->prepare('UPDATE users SET name = ?, email = ?, role = ?, plate_no = ?, reference_no = ? WHERE user_id = ?')) {
					$stmt->bind_param('sssssi', $name, $email, $role, $plate, $ref, $uid);
					$ok = $stmt->execute();
					$stmt->close();
					$success = $ok ? 'User updated successfully.' : 'Failed to update user.';
				} else {
					$error = 'Internal error (prepare update).';
				}
			}
		}
	}
}

// Fetch users
$rows = [];
if ($hasAddress) {
	$sql = "SELECT user_id, name, email, role, plate_no, reference_no, address, created_at FROM users ORDER BY user_id DESC LIMIT 200";
} else {
	$sql = "SELECT user_id, name, email, role, plate_no, reference_no, created_at FROM users ORDER BY user_id DESC LIMIT 200";
}
if ($res = $mysqli->query($sql)) {
	while ($row = $res->fetch_assoc()) { $rows[] = $row; }
	$res->free();
}

$admins = array_values(array_filter($rows, function($r){ return ($r['role'] ?? '') === 'admin'; }));
$nonAdmins = array_values(array_filter($rows, function($r){ return ($r['role'] ?? '') !== 'admin'; }));

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editUser = null;
if ($editId) {
	$sel = $hasAddress ? 'user_id, name, email, role, plate_no, reference_no, address' : 'user_id, name, email, role, plate_no, reference_no';
	if ($stmt = $mysqli->prepare("SELECT $sel FROM users WHERE user_id = ? LIMIT 1")) {
		$stmt->bind_param('i', $editId);
		$stmt->execute();
		$res = $stmt->get_result();
		$editUser = $res->fetch_assoc();
		$stmt->close();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users - TNVS</title>
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
				<a class="nav" href="budget.php"><span>Budget</span></a>
			</div>
			<div class="group">
				<h4>Management</h4>
				<a class="nav" href="admin.php"><span>Admin</span></a>
				<a class="nav active" href="users.php"><span>Users</span></a>
			</div>
			<div class="group">
				
			</div>
		</nav>
		<div id="overlay" class="overlay"></div>
		<main class="content" id="mainContent">
			<section class="section active">
				<h3>Users Management</h3>
				<?php if ($success): ?><div class="notification success show" style="position:static;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
				<?php if ($error): ?><div class="notification danger show" style="position:static;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
				<?php if ($editUser): ?>
				<div class="modal-card" style="display:block">
					<div class="modal-header"><h4>Edit User #<?php echo (int)$editUser['user_id']; ?></h4></div>
					<div class="modal-body">
						<form method="post" class="form-grid">
							<input type="hidden" name="do" value="update_user" />
							<input type="hidden" name="user_id" value="<?php echo (int)$editUser['user_id']; ?>" />
							<label>Name<input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required></label>
							<label>Email<input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required></label>
							<label>Role
								<select name="role" id="roleSelect">
									<option value="driver" <?php echo $editUser['role']==='driver'?'selected':''; ?>>Driver</option>
									<option value="finance_staff" <?php echo $editUser['role']==='finance_staff'?'selected':''; ?>>Employee</option>
									<option value="admin" <?php echo $editUser['role']==='admin'?'selected':''; ?>>Admin</option>
								</select>
							</label>
							<?php if ($hasAddress): ?>
							<label id="addressField">Address<input type="text" name="address" value="<?php echo htmlspecialchars($editUser['address'] ?? ''); ?>"></label>
							<?php endif; ?>
							<div id="nonAdminFields" style="<?php echo ($editUser['role']==='admin')?'display:none;':''; ?>">
								<label>Plate No<input type="text" name="plate_no" value="<?php echo htmlspecialchars($editUser['plate_no'] ?? ''); ?>"></label>
								<label>Reference No<input type="text" name="reference_no" value="<?php echo htmlspecialchars($editUser['reference_no'] ?? ''); ?>"></label>
							</div>
							<div style="grid-column: 1 / -1; display:flex; gap:10px;">
								<button class="btn-small" type="submit">Save</button>
								<a class="text-btn" href="users.php">Cancel</a>
							</div>
						</form>
					</div>
				</div>
				<?php endif; ?>

				<?php if (!empty($admins)): ?>
				<div class="chart-card" style="margin-bottom:20px;">
					<h4>Admins</h4>
					<div class="table-wrap">
						<table class="table">
							<thead><tr><th>ID</th><th>Name</th><th>Email</th><?php if($hasAddress): ?><th>Address</th><?php endif; ?><th>Role</th><th>Created</th><th>Action</th></tr></thead>
							<tbody>
								<?php foreach ($admins as $r): ?>
								<tr>
									<td><?php echo (int)$r['user_id']; ?></td>
									<td><?php echo htmlspecialchars($r['name']); ?></td>
									<td><?php echo htmlspecialchars($r['email']); ?></td>
									<?php if($hasAddress): ?><td><?php echo htmlspecialchars($r['address'] ?? ''); ?></td><?php endif; ?>
									<td><?php echo htmlspecialchars($r['role']); ?></td>
									<td><?php echo htmlspecialchars($r['created_at']); ?></td>
									<td><a class="btn-small" href="users.php?edit=<?php echo (int)$r['user_id']; ?>">Edit</a></td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php endif; ?>

				<div class="chart-card">
					<h4>Users</h4>
					<div class="table-wrap">
						<table class="table">
							<thead><tr><th>ID</th><th>Name</th><th>Email</th><?php if($hasAddress): ?><th>Address</th><?php endif; ?><th>Role</th><th>Plate</th><th>Reference</th><th>Created</th><th>Action</th></tr></thead>
							<tbody>
								<?php foreach ($nonAdmins as $r): ?>
								<tr>
									<td><?php echo (int)$r['user_id']; ?></td>
									<td><?php echo htmlspecialchars($r['name']); ?></td>
									<td><?php echo htmlspecialchars($r['email']); ?></td>
									<?php if($hasAddress): ?><td><?php echo htmlspecialchars($r['address'] ?? ''); ?></td><?php endif; ?>
									<td><?php echo htmlspecialchars($r['role']==='finance_staff' ? 'employee' : $r['role']); ?></td>
									<td><?php echo htmlspecialchars($r['plate_no']); ?></td>
									<td><?php echo htmlspecialchars($r['reference_no']); ?></td>
									<td><?php echo htmlspecialchars($r['created_at']); ?></td>
									<td><a class="btn-small" href="users.php?edit=<?php echo (int)$r['user_id']; ?>">Edit</a></td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($nonAdmins)): ?><tr><td colspan="<?php echo $hasAddress? '9':'8'; ?>">No users found.</td></tr><?php endif; ?>
						</tbody>
					</table>
					</div>
				</div>
			</section>
		</main>
	</div>
	<script>
	(function(){
		var sel=document.getElementById('roleSelect');
		if(!sel) return;
		function sync(){ var v=sel.value; var non=document.getElementById('nonAdminFields'); if(non) non.style.display = (v==='admin') ? 'none' : ''; }
		sel.addEventListener('change', sync); sync();
	})();
	</script>
	<script src="app.js"></script>
</body>
</html>
