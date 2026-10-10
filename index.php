<?php
require 'db.php';

if (isset($_SESSION['user_id'])) { header('Location: home.php'); exit; }

$error = $success = '';
$mode = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode  = $_POST['mode'] ?? 'login';
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif ($mode === 'register') {
        $name = trim($_POST['fullname'] ?? '');
        if ($name === '' || strlen($pass) < 6) {
            $error = 'Enter your name and a password of at least 6 characters.';
        } else {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'That email is already registered. Log in instead.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO users (fullname, email, password_hash) VALUES (?, ?, ?)');
                $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                $success = 'Account created. You can log in now.';
                $mode = 'login';
            }
        }
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['fullname']  = $user['fullname'];
            header('Location: home.php');
            exit;
        }
        $error = 'Wrong email or password.';
    }
}
$isReg = $mode === 'register';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $isReg ? 'Create account' : 'Log in' ?></title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px;
      font-family: Arial, sans-serif;
      background: #eef3f1;
      color: #14231f;
    }
    .card {
      width: 100%;
      max-width: 400px;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    h1 { margin: 0 0 5px; }
    .sub { margin: 0 0 20px; color: #690568; }
    label { display: block; margin: 15px 0 5px; font-weight: bold; }
    input {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      width: 100%;
      margin-top: 20px;
      padding: 12px;
      font-size: 16px;
      color: #fff;
      background: #4b0960;
      border: 0;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover { background: #af25cb; }
    a { color: #6a0f76; }
    .switch { margin-top: 20px; text-align: center; }
    .alert { padding: 10px; border-radius: 6px; margin-bottom: 10px; }
    .error { background: #fdecec; color: #a12626; }
    .ok { background: #e4f5ec; color: #17603a; }
  </style>
</head>
<body>
  <main class="card">
    <h1><?= $isReg ? 'Create your account' : 'Welcome back' ?></h1>
    <p class="sub"><?= $isReg ? 'It takes less than a minute.' : 'Log in to open your dashboard.' ?></p>

    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert ok"><?= e($success) ?></div><?php endif; ?>

    <form method="post" autocomplete="on">
      <input type="hidden" name="mode" value="<?= $isReg ? 'register' : 'login' ?>">

      <?php if ($isReg): ?>
        <label for="fullname">Full name</label>
        <input id="fullname" name="fullname" type="text" required value="<?= e($_POST['fullname'] ?? '') ?>">
      <?php endif; ?>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">

      <label for="password">Password</label>
      <input id="password" name="password" type="password" required minlength="6">

      <button type="submit"><?= $isReg ? 'Create account' : 'Log in' ?></button>
    </form>

    <p class="switch">
      <?php if ($isReg): ?>Already have an account? <a href="index.php">Log in</a>
      <?php else: ?>New here? <a href="index.php?mode=register">Create an account</a><?php endif; ?>
    </p>
  </main>
</body>
</html>
