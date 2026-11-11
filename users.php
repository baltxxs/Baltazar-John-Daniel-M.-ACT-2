<?php
require_once __DIR__ . '/config/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // An administrator cannot change or delete their own account from this page.
    if ($id > 0 && $id !== (int)$_SESSION['user_id']) {
        if ($action === 'role') {
            $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
            $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$role, $id]);
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
        }
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT id, name, username, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$page_title = 'User Management';
require __DIR__ . '/includes/header.php';
?>
<section class="content-card wide-card">
  <div class="section-heading"><div><p class="eyebrow">ADMINISTRATOR ONLY</p><h1>User Management</h1><p class="subtext">View registered accounts, update user roles, and delete accounts.</p></div><a href="dashboard.php">← Dashboard</a></div>
  <div class="alert info">Only administrators can access this page. Regular users will receive an access-denied message.</div>
  <div class="table-wrap"><table>
    <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Account action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><strong><?= e($user['name']) ?></strong></td><td><?= e($user['username']) ?></td><td><?= e($user['email']) ?></td>
        <td>
          <?php if ((int)$user['id'] === (int)$_SESSION['user_id']): ?>
            <span class="role-badge"><?= e($user['role']) ?> (you)</span>
          <?php else: ?>
            <form method="POST" class="role-form">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
              <input type="hidden" name="action" value="role">
              <select name="role"><option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select>
              <button class="small-btn" type="submit">Update</button>
            </form>
          <?php endif; ?>
        </td>
        <td>
          <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
            <form method="POST" onsubmit="return confirm('Delete this account and its records?');">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <button class="danger-button" type="submit">Delete user</button>
            </form>
          <?php else: ?>Current account<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
