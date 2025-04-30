<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';

$error = '';
$success = '';

// Handle form submission (with location geocoded via JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'], $_POST['location'], $_POST['pin'], $_POST['latitude'], $_POST['longitude'])) {
    $cid = $_POST['container_id'];
    $loc = $_POST['location'];
    $pin = $_POST['pin'];
    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);

    if (!preg_match('/^\d{4}$/', $pin)) {
        $error = '❌ PIN must be a 4-digit number.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO containers (container_id, location, pin, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$cid, $loc, $pin, $lat, $lng]);
            $success = '✅ Container added successfully!';
        } catch (PDOException $e) {
            $error = '❌ Failed to add container: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$containers = $pdo->query("SELECT * FROM containers")->fetchAll();
$users = $pdo->query("SELECT id, username, role, created_at FROM users")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – LogiLock</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        .modal {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            z-index: 1000;
            text-align: center;
        }
        .modal-content { position: relative; }
        .modal-content .close-btn {
            position: absolute;
            top: -10px; right: -10px;
            background: red; color: white;
            border: none; border-radius: 50%;
            font-size: 14px; width: 24px; height: 24px;
            cursor: pointer;
        }
        #map { height: 400px; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h1>LogiLock – Admin</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (Admin)</h2>

    <!-- Feedback Messages -->
    <?php if ($error || $success): ?>
        <div class="modal" id="statusModal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('statusModal').style.display='none'">×</button>
                <h2 style="color:<?= $error ? 'red' : 'green' ?>;"><?= $error ?: $success ?></h2>
            </div>
        </div>
        <script>
            setTimeout(() => {
                const modal = document.getElementById('statusModal');
                if (modal) modal.style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <!-- Add Container Form -->
    <h3>Add New Container</h3>
    <form id="addForm" method="POST">
        <input type="text" name="container_id" placeholder="Container ID" required>
        <input type="text" name="location" placeholder="Location" id="locationInput" required>
        <input type="password" name="pin" placeholder="4-digit PIN" maxlength="4" required>
        <button type="submit">Add Container</button>
    </form>

    <!-- Auto-Geocode Location -->
    <script>
    document.getElementById("addForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const form = this;
        const location = document.getElementById("locationInput").value;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const lat = document.createElement("input");
                    lat.type = "hidden"; lat.name = "latitude"; lat.value = data[0].lat;

                    const lon = document.createElement("input");
                    lon.type = "hidden"; lon.name = "longitude"; lon.value = data[0].lon;

                    form.appendChild(lat);
                    form.appendChild(lon);
                    form.submit();
                } else {
                    alert("❌ Location not found. Try again with a more specific place.");
                }
            })
            .catch(() => alert("❌ Error fetching location data."));
    });
    </script>

    <!-- Container Table -->
    <h3>Container Status Monitoring</h3>
    <table>
        <thead>
            <tr>
                <th>Container ID</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($containers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['container_id']) ?></td>
                <td><?= $c['status'] == 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
                <td><?= $c['updated_at'] ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Map -->
    <h3>Container Map</h3>
    <div id="map"></div>
    <script>
        const map = L.map('map').setView([10.3157, 123.8854], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const containers = <?= json_encode($containers) ?>;
        containers.forEach(c => {
            if (c.latitude && c.longitude) {
                const iconColor = c.status === 'Tampered' ? 'red' : 'green';
                const marker = L.circleMarker([c.latitude, c.longitude], {
                    radius: 8,
                    fillColor: iconColor,
                    color: '#000',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                marker.bindPopup(`<b>${c.container_id}</b><br>Status: ${c.status}<br>Location: ${c.location}`);
            }
        });
    </script>

    <!-- User Table -->
    <h3>Registered Users</h3>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= ucfirst($u['role']) ?></td>
                <td><?= $u['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
