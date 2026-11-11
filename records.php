<?php
require_once __DIR__ . '/config/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title !== '' && mb_strlen($title) <= 150) {
            if ($action === 'create') {
                $stmt = $pdo->prepare('INSERT INTO records (user_id, title, description) VALUES (?, ?, ?)');
                $stmt->execute([$_SESSION['user_id'], $title, $description]);
            } elseif (($_SESSION['role'] ?? 'user') === 'admin') {
                $stmt = $pdo->prepare('UPDATE records SET title = ?, description = ? WHERE id = ?');
                $stmt->execute([$title, $description, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE records SET title = ?, description = ? WHERE id = ? AND user_id = ?');
                $stmt->execute([$title, $description, $id, $_SESSION['user_id']]);
            }
        }
    } elseif ($action === 'delete' && $id > 0) {
        if (($_SESSION['role'] ?? 'user') === 'admin') {
            $stmt = $pdo->prepare('DELETE FROM records WHERE id = ?');
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM records WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $_SESSION['user_id']]);
        }
    }
    header('Location: records.php');
    exit;
}

$editRecord = null;
if (isset($_GET['edit'])) {
    if (($_SESSION['role'] ?? 'user') === 'admin') {
        $stmt = $pdo->prepare('SELECT * FROM records WHERE id = ?');
        $stmt->execute([(int)$_GET['edit']]);
    } else {
        $stmt = $pdo->prepare('SELECT * FROM records WHERE id = ? AND user_id = ?');
        $stmt->execute([(int)$_GET['edit'], $_SESSION['user_id']]);
    }
    $editRecord = $stmt->fetch();
}

if (($_SESSION['role'] ?? 'user') === 'admin') {
    $records = $pdo->query('SELECT records.*, users.username FROM records INNER JOIN users ON users.id = records.user_id ORDER BY records.created_at DESC')->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT records.*, users.username FROM records INNER JOIN users ON users.id = records.user_id WHERE records.user_id = ? ORDER BY records.created_at DESC');
    $stmt->execute([$_SESSION['user_id']]);
    $records = $stmt->fetchAll();
}

$page_title = 'My Records';
require __DIR__ . '/includes/header.php';
?>
<section class="content-card wide-card">
  <div class="section-heading">
    <div><p class="eyebrow">CREATE · READ · UPDATE · DELETE</p><h1>Records Management</h1><p class="subtext">Create and manage your website records.</p></div>
    <a href="dashboard.php">← Dashboard</a>
  </div>

  <div class="form-card">
    <h2><?= $editRecord ? 'Update record' : 'Create a new record' ?></h2>
    <form method="POST" class="record-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="<?= $editRecord ? 'update' : 'create' ?>">
      <?php if ($editRecord): ?><input type="hidden" name="id" value="<?= (int)$editRecord['id'] ?>"><?php endif; ?>
      <label for="title">Record title</label>
      <input id="title" name="title" maxlength="150" value="<?= e($editRecord['title'] ?? '') ?>" placeholder="Enter a title" required>
      <label for="description">Description</label>
      <textarea id="description" name="description" rows="3" placeholder="Write a short description..."><?= e($editRecord['description'] ?? '') ?></textarea>
      <div class="form-actions"><button class="primary-btn compact" type="submit"><?= $editRecord ? 'Save Changes' : 'Add Record' ?></button><?php if ($editRecord): ?><a href="records.php">Cancel</a><?php endif; ?></div>
    </form>
  </div>

  <h2>Saved records</h2>
  <?php if (!$records): ?>
    <p class="empty-state">No records found. Use the form above to add your first record.</p>
  <?php else: ?>
    <div class="table-wrap"><table>
      <thead><tr><th>Title</th><th>Description</th><?php if (($_SESSION['role'] ?? '') === 'admin'): ?><th>Owner</th><?php endif; ?><th>Date created</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($records as $record): ?>
        <tr>
          <td><strong><?= e($record['title']) ?></strong></td>
          <td><?= nl2br(e($record['description'])) ?></td>
          <?php if (($_SESSION['role'] ?? '') === 'admin'): ?><td><?= e($record['username']) ?></td><?php endif; ?>
          <td><?= e($record['created_at']) ?></td>
          <td><div class="table-actions">
            <a href="records.php?edit=<?= (int)$record['id'] ?>">Edit</a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
              <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$record['id'] ?>">
              <button class="danger-button" type="submit">Delete</button>
            </form>
          </div></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
