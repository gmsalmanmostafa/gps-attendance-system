<?php
require_once 'config.php';
require_admin();
require_once 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'employee'");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $team        = trim($_POST['team'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    $upd = $pdo->prepare("
        UPDATE users
        SET name = ?, team = ?, phone = ?, designation = ?, status = ?
        WHERE id = ?
    ");
    $upd->execute([$name, $team, $phone, $designation, $status, $id]);

    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee – GPS Attendance</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3">Edit Employee</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Team</label>
                    <input type="text" name="team" class="form-control"
                           value="<?= htmlspecialchars($user['team'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control"
                           value="<?= htmlspecialchars($user['designation'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="inactive" <?= $user['status']==='inactive'?'selected':'' ?>>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
