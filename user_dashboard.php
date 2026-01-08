<?php
require_once 'config.php';
require_login();
require_once 'db.php';

// current user info for profile
$stmt = $pdo->prepare("SELECT name, email, team, phone, designation FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard – GPS Attendance</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container-fluid">
    <span class="navbar-brand fw-semibold">GPS Attendance</span>
    <div class="d-flex align-items-center">
        <span class="navbar-text text-white me-3">
            <?= htmlspecialchars($_SESSION['name']) ?>
        </span>
        <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="row g-4">
        <!-- Profile card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">User Profile</h5>
                    <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($user['name'] ?? '') ?></p>
                    <p class="mb-1"><strong>Designation:</strong> <?= htmlspecialchars($user['designation'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Team:</strong> <?= htmlspecialchars($user['team'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Attendance actions -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h4 class="mb-3">Attendance</h4>
                    <p class="text-muted mb-3">
                        Use the buttons below to Check-In or Check-Out. Your live location will be captured automatically.
                    </p>

                    <div class="mb-3">
                        <button class="btn btn-success me-2" onclick="markAttendance('IN')">
                            Check-In
                        </button>
                        <button class="btn btn-danger" onclick="markAttendance('OUT')">
                            Check-Out
                        </button>
                    </div>

                    <div class="alert alert-info py-2" id="status">
                        Click a button to capture your current location and mark attendance.
                    </div>

                    <div id="mapLink" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAttendance(type) {
    const statusEl = document.getElementById('status');
    const mapEl = document.getElementById('mapLink');

    if (!navigator.geolocation) {
        statusEl.className = "alert alert-danger py-2";
        statusEl.innerHTML = "Geolocation is not supported by this browser.";
        return;
    }

    statusEl.className = "alert alert-warning py-2";
    statusEl.innerHTML = "Fetching your location, please wait...";

    navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "save_attendance.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            statusEl.className = "alert alert-success py-2";
            statusEl.innerHTML = this.responseText;

            const mapUrl = "https://www.google.com/maps?q=" + encodeURIComponent(lat + "," + lon);
            mapEl.innerHTML =
                '<a class="btn btn-sm btn-outline-primary mt-2" target="_blank" href="' +
                mapUrl + '">View your location on map</a>';
        };

        const data =
            "type=" + encodeURIComponent(type) +
            "&lat=" + encodeURIComponent(lat) +
            "&lon=" + encodeURIComponent(lon);

        xhr.send(data);

    }, function(error) {
        statusEl.className = "alert alert-danger py-2";
        statusEl.innerHTML = "Could not get your location. Please enable GPS/Location and try again.";
    }, {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0
    });
}
</script>

</body>
</html>
