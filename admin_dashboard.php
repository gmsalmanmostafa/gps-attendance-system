<?php
require_once 'config.php';
require_admin();
require_once 'db.php';

// Total employees
$total_stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'employee' AND status = 'active'");
$total_employees = $total_stmt->fetch()['total'] ?? 0;

// Present today = employees who have at least one IN today
$present_stmt = $pdo->query("
    SELECT COUNT(DISTINCT user_id) AS present
    FROM attendance
    WHERE check_type = 'IN' AND DATE(created_at) = CURDATE()
");
$present_today = $present_stmt->fetch()['present'] ?? 0;

$absent_today = max(0, $total_employees - $present_today);

// Employee list with today check-in/out
$query = "
SELECT
    u.id,
    u.name,
    u.team,
    u.phone,
    u.status,
    -- latest IN today
    (SELECT a1.created_at FROM attendance a1
        WHERE a1.user_id = u.id AND a1.check_type = 'IN' AND DATE(a1.created_at) = CURDATE()
        ORDER BY a1.created_at ASC LIMIT 1) AS first_checkin_time,
    (SELECT CONCAT(a1.latitude, ',', a1.longitude) FROM attendance a1
        WHERE a1.user_id = u.id AND a1.check_type = 'IN' AND DATE(a1.created_at) = CURDATE()
        ORDER BY a1.created_at ASC LIMIT 1) AS first_checkin_location,
    -- latest OUT today
    (SELECT a2.created_at FROM attendance a2
        WHERE a2.user_id = u.id AND a2.check_type = 'OUT' AND DATE(a2.created_at) = CURDATE()
        ORDER BY a2.created_at DESC LIMIT 1) AS last_checkout_time,
    (SELECT CONCAT(a2.latitude, ',', a2.longitude) FROM attendance a2
        WHERE a2.user_id = u.id AND a2.check_type = 'OUT' AND DATE(a2.created_at) = CURDATE()
        ORDER BY a2.created_at DESC LIMIT 1) AS last_checkout_location
FROM users u
WHERE u.role = 'employee'
ORDER BY u.id ASC
";

$stmt = $pdo->query($query);
$employees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – GPS Attendance</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <span class="navbar-brand fw-semibold">GPS Attendance – Admin</span>
    <div class="d-flex align-items-center">
        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['name']) ?>
        </span>
        <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container my-4">
    <!-- Top stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card bg-white">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Total Employees</h6>
                    <h3 class="fw-bold mb-0"><?= (int)$total_employees ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card bg-white">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Present Today</h6>
                    <h3 class="fw-bold text-success mb-0"><?= (int)$present_today ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card bg-white">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Absent Today</h6>
                    <h3 class="fw-bold text-danger mb-0"><?= (int)$absent_today ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Employee Attendance Overview</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Team</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Check-in (Location & Time)</th>
                        <th>Check-out (Location & Time)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$employees): ?>
                    <tr>
                        <td colspan="8" class="text-center">No employees found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employees as $emp): 
                        $is_present = !empty($emp['first_checkin_time']);
                        $status_label = $is_present ? 'Present' : 'Absent';
                        $status_class = $is_present ? 'badge bg-success' : 'badge bg-danger';
                    ?>
                        <tr>
                            <td>#EMP<?= str_pad($emp['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($emp['name']) ?></td>
                            <td><?= htmlspecialchars($emp['team'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($emp['phone'] ?? 'N/A') ?></td>
                            <td><span class="<?= $status_class ?>"><?= $status_label ?></span></td>
                            <td>
                                <?php if ($emp['first_checkin_time']): ?>
                                    <?php
                                      $cin_loc = $emp['first_checkin_location'] ?? '';
                                      $cin_time = $emp['first_checkin_time'];
                                      $map_url = "https://www.google.com/maps?q=" . urlencode($cin_loc);
                                    ?>
                                    <div><small><?= htmlspecialchars($cin_time) ?></small></div>
                                    <a href="<?= htmlspecialchars($map_url) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        View Location
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No check-in</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($emp['last_checkout_time']): ?>
                                    <?php
                                      $cout_loc = $emp['last_checkout_location'] ?? '';
                                      $cout_time = $emp['last_checkout_time'];
                                      $map_url_out = "https://www.google.com/maps?q=" . urlencode($cout_loc);
                                    ?>
                                    <div><small><?= htmlspecialchars($cout_time) ?></small></div>
                                    <a href="<?= htmlspecialchars($map_url_out) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                        View Location
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No check-out</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?= (int)$emp['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="delete_user.php?id=<?= (int)$emp['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                   Remove
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
