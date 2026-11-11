<?php
require_once __DIR__ . '/config/auth.php';
require_login();

$stmt = $pdo->prepare('SELECT name, username, email, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM records WHERE user_id = ?');
$countStmt->execute([$_SESSION['user_id']]);
$recordCount = (int)$countStmt->fetchColumn();

$page_title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="content-card">
  <p class="eyebrow">YOUR ACCOUNT</p>
  <h1>Welcome, <?= e($user['name']) ?>!</h1>
  <p class="subtext">You are signed in. Your current role is <strong><?= e($_SESSION['role']) ?></strong>.</p>

  <div class="stats-grid">
    <div class="stat-box"><span>YOUR RECORDS</span><strong><?= $recordCount ?></strong></div>
    <div class="stat-box"><span>ACCOUNT ROLE</span><strong class="role-text"><?= e(strtoupper($_SESSION['role'])) ?></strong></div>
  </div>

  <h2>Account information</h2>
  <div class="details-list">
    <p><strong>Full name</strong><span><?= e($user['name']) ?></span></p>
    <p><strong>Username</strong><span><?= e($user['username']) ?></span></p>
    <p><strong>Email</strong><span><?= e($user['email']) ?></span></p>
    <p><strong>Registered</strong><span><?= e($user['created_at']) ?></span></p>
  </div>

  <div class="action-row">
    <a class="primary-link" href="records.php">Manage My Records</a>
    <?php if (($_SESSION['role'] ?? 'user') === 'admin'): ?>
      <a class="secondary-link" href="users.php">Open User Management</a>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
