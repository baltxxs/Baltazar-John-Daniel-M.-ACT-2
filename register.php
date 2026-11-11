<?php
require_once __DIR__ . '/config/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$name = $username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter your full name (maximum 100 characters).';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) $errors[] = 'Username must be 3–30 characters using letters, numbers, or underscores.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $errors[] = 'That username or email is already registered.';
        } else {
            // Public registration always creates a regular user, never an admin.
            $insert = $pdo->prepare('INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, "user")');
            $insert->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | My Website</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="auth-page">
  <section class="welcome-panel">
    <div class="brand">MY WEBSITE</div>
    <div class="welcome-copy">
      <p class="eyebrow light">GET STARTED</p>
      <h1>A place to begin something new.</h1>
      <p>Create your account and become part of our website.</p>
    </div>
    <p class="copyright">© 2026 My Website. All rights reserved.</p>
  </section>
  <section class="form-panel">
    <div class="form-wrap">
      <div class="mobile-brand">MY WEBSITE</div>
      <p class="eyebrow">GET STARTED</p>
      <h2>Create account</h2>
      <p class="subtext">Fill in the details below to register.</p>

      <?php if ($errors): ?>
        <div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="name">Full name</label>
        <input id="name" name="name" type="text" placeholder="Your full name" maxlength="100" value="<?= e($name) ?>" required>
        <label for="username">Username</label>
        <input id="username" name="username" type="text" placeholder="Choose a username" minlength="3" maxlength="30" value="<?= e($username) ?>" required>
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" placeholder="name@example.com" maxlength="190" value="<?= e($email) ?>" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" placeholder="At least 8 characters" minlength="8" required>
        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter your password" minlength="8" required>
        <button type="submit" class="primary-btn">Create Account</button>
      </form>
      <p class="switch-text">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
  </section>
</main>
</body>
</html>
