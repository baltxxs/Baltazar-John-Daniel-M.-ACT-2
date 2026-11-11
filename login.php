<?php
require_once __DIR__ . '/config/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$identity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, name, username, email, password, role FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Incorrect email/username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In | My Website</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth-page">
  <section class="welcome-panel">
    <div class="brand">MY WEBSITE</div>
    <div class="welcome-copy">
      <p class="eyebrow light">WELCOME</p>
      <h1>Your next chapter starts here.</h1>
      <p>Sign in to continue, or create an account to get started.</p>
    </div>
    <p class="copyright">© 2026 My Website. All rights reserved.</p>
  </section>
  <section class="form-panel">
    <div class="form-wrap">
      <div class="mobile-brand">MY WEBSITE</div>
      <p class="eyebrow">WELCOME BACK</p>
      <h2>Sign in</h2>
      <p class="subtext">Enter your details to access your account.</p>

      <?php if (isset($_GET['registered'])): ?>
        <div class="alert success">Your account was created successfully. You can now sign in.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="identity">Email address or username</label>
        <input id="identity" name="identity" type="text" placeholder="name@example.com" value="<?= e($identity) ?>" autocomplete="username" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
        <button type="submit" class="primary-btn">Sign In</button>
      </form>
      <p class="switch-text">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
  </section>
</main>
</body>
</html>
