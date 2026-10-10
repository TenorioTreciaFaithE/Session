<?php
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare('SELECT fullname, email, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user) { session_destroy(); header('Location: index.php'); exit; }

$total = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #eef3f1;
      color: #14231f;
    }
    h1 { margin: 0 0 5px; }
    .sub { margin: 0 0 20px; color: #5d6f69; }
    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      background: #fff;
      border-bottom: 1px solid #ddd;
    }
    .btn-out {
      padding: 6px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      text-decoration: none;
      color: #14231f;
    }
    .wrap { max-width: 760px; margin: 0 auto; padding: 30px 20px; }
    .grid {
      display: grid;
      gap: 15px;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .tile {
      background: #fff;
      padding: 15px;
      border-radius: 10px;
      border: 1px solid #ddd;
    }
    .tile span { display: block; font-size: 14px; color: #5d6f69; }
    .tile b { word-break: break-word; }
  </style>
</head>
<body>
  <header class="nav">
    <strong>SessionApp</strong>
    <a class="btn-out" href="logout.php">Log out</a>
  </header>

  <main class="wrap">
    <h1>Hello, <?= e(explode(' ', $user['fullname'])[0]) ?></h1>
    <p class="sub">You're logged in. Your session stays active until you log out.</p>

    <section class="grid">
      <div class="tile"><span>Name</span><b><?= e($user['fullname']) ?></b></div>
      <div class="tile"><span>Email</span><b><?= e($user['email']) ?></b></div>
      <div class="tile"><span>Member since</span><b><?= e(date('M j, Y', strtotime($user['created_at']))) ?></b></div>
      <div class="tile"><span>Registered users</span><b><?= (int)$total ?></b></div>
    </section>
  </main>
</body>
</html>
