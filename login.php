<?php
session_start();
require_once 'db.php';

$error = '';

// Manual login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND status='active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        // ✅ employee হলে user_dashboard.php এ পাঠাও
        header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'));
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

// Admin auto-login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role='admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch();

    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['name']    = $admin['name'];
        $_SESSION['role']    = $admin['role'];
        header("Location: admin_dashboard.php");
        exit;
    }
}

// Employee auto-login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_auto'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role='employee' LIMIT 1");
    $stmt->execute();
    $emp = $stmt->fetch();

    if ($emp) {
        $_SESSION['user_id'] = $emp['id'];
        $_SESSION['name']    = $emp['name'];
        $_SESSION['role']    = $emp['role'];
        // ✅ এখানে পরিবর্তন: employee হলে user_dashboard.php এ পাঠাও
        header("Location: user_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GPS Attendance – Login</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card p-4 shadow-lg">
    <h3 class="text-center mb-3">Login Panel</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Manual login -->
    <form method="post" class="mb-4">
      <h5>Manual Login</h5>
      <div class="mb-2">
        <input type="email" name="email" class="form-control" placeholder="Email" required>
      </div>
      <div class="mb-2">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
      </div>
      <button type="submit" name="manual_login" class="btn btn-success w-100">Login</button>
    </form>

    <!-- Auto-login buttons -->
    <form method="post" class="mb-3">
      <button type="submit" name="admin_login" class="btn btn-dark w-100">Admin Auto Login</button>
    </form>

    <form method="post" class="mb-3">
      <button type="submit" name="employee_auto" class="btn btn-primary w-100">Employee Auto Login</button>
    </form>
  </div>
</div>

</body>
</html>
