<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';

$error = '';
$success = '';

// Delete Container Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_container_id'])) {
    $delete_id = $_POST['delete_container_id'];
    $stmt = $pdo->prepare("DELETE FROM containers WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $success = '🗑️ Container deleted successfully!';
    } else {
        $error = '❌ Failed to delete container.';
    }
}

// Add Container Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['container_id'], $_POST['location'], $_POST['origin'], $_POST['destination'], $_POST['pin'], $_POST['latitude'], $_POST['longitude'], $_POST['latitude_origin'], $_POST['longitude_origin'], $_POST['latitude_dest'], $_POST['longitude_dest']) && !isset($_POST['delete_container_id'])) {
    $cid = $_POST['container_id'];
    $loc = $_POST['location'];
    $origin = $_POST['origin'];
    $dest = $_POST['destination'];
    $pin = $_POST['pin'];

    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);
    $lat_orig = floatval($_POST['latitude_origin']);
    $lng_orig = floatval($_POST['longitude_origin']);
    $lat_dest = floatval($_POST['latitude_dest']);
    $lng_dest = floatval($_POST['longitude_dest']);

    if (!preg_match('/^\d{4}$/', $pin)) {
        $error = '❌ PIN must be a 4-digit number.';
    } else {
        $earthRadius = 6371;
        $dLat = deg2rad($lat_dest - $lat_orig);
        $dLon = deg2rad($lng_dest - $lng_orig);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat_orig)) * cos(deg2rad($lat_dest)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        $speed = 40;
        $eta = date('Y-m-d H:i:s', time() + (int)(($distance / $speed) * 3600));


        $stmt = $pdo->prepare("INSERT INTO containers (container_id, location, pin, origin, destination, estimated_arrival, latitude, longitude, latitude_origin, longitude_origin, latitude_dest, longitude_dest) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$cid, $loc, $pin, $origin, $dest, $eta, $lat, $lng, $lat_orig, $lng_orig, $lat_dest, $lng_dest]);
            $success = '✅ Container added successfully!';

            // Log location change
            logContainerInteraction($cid, 'Location Change', 'Container location updated to ' . $loc);

        } catch (PDOException $e) {
            $error = '❌ Failed to add container: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$containers = $pdo->query("SELECT * FROM containers")->fetchAll();
$users = $pdo->query("SELECT id, username, role, created_at FROM users")->fetchAll();

function logContainerInteraction($container_id, $action, $details) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO container_logs (container_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$container_id, $action, $details]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – LogiLock</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>
<body>

<header>
    <h1>LogiLock – Admin</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="records.php">Records</a> <!-- New button -->
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (Admin)</h2>

    <?php if ($error || $success): ?>
        <div class="modal" id="statusModal">
            <div class="modal-content">
                <button class="close-btn" onclick="document.getElementById('statusModal').style.display='none'">×</button>
                <h2 style="color:<?= $error ? 'red' : 'green' ?>;"><?= $error ?: $success ?></h2>
            </div>
        </div>
        <script>setTimeout(() => document.getElementById('statusModal').style.display = 'none', 3000);</script>
    <?php endif; ?>

    <h3>Add New Container</h3>
    <form id="addForm" method="POST">
        <input type="text" name="container_id" placeholder="Container ID" required>
        <input type="text" name="location" placeholder="Current Location" id="locationInput" required>
        <input type="text" name="origin" placeholder="Origin" id="originInput" required>
        <input type="text" name="destination" placeholder="Destination" id="destinationInput" required>
        <input type="password" name="pin" placeholder="4-digit PIN" maxlength="4" required>
        <button type="submit">Add Container</button>
    </form>

    <script>
    document.getElementById("addForm").addEventListener("submit", function(e) {
        e.preventDefault();
        const form = this;
        const loc = document.getElementById("locationInput").value;
        const origin = document.getElementById("originInput").value;
        const dest = document.getElementById("destinationInput").value;

        Promise.all([
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(loc)}`).then(res => res.json()),
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(origin)}`).then(res => res.json()),
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(dest)}`).then(res => res.json())
        ]).then(([locData, origData, destData]) => {
            if (locData[0] && origData[0] && destData[0]) {
                const addInput = (name, value) => {
                    const input = document.createElement("input");
                    input.type = "hidden"; input.name = name; input.value = value;
                    form.appendChild(input);
                };
                addInput("latitude", locData[0].lat);
                addInput("longitude", locData[0].lon);
                addInput("latitude_origin", origData[0].lat);
                addInput("longitude_origin", origData[0].lon);
                addInput("latitude_dest", destData[0].lat);
                addInput("longitude_dest", destData[0].lon);
                form.submit();
            } else {
                alert("❌ One or more locations couldn't be found.");
            }
        }).catch(() => alert("❌ Error during location lookup."));
    });
    </script>

    <h3>Container Status Monitoring</h3>
    <table>
        <thead>
            <tr>
                <th>Container ID</th>
                <th>Status</th>
                <th>Location</th>
                <th>Origin → Destination</th>
                <th>ETA</th>
                <th>Last Updated</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($containers as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['container_id']) ?></td>
                <td><?= $c['status'] === 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
                <td><?= htmlspecialchars($c['origin']) ?> → <?= htmlspecialchars($c['destination']) ?></td>
                <td><?= $c['estimated_arrival'] ?></td>
                <td><?= $c['updated_at'] ?></td>
                <td>
                    <button onclick="openDeleteModal(<?= $c['id'] ?>)">🗑️</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Deletion Modal -->
    <div id="deleteModal" class="modal" style="display:none;">
        <div class="modal-content">
            <h2>Are you sure you want to delete this container?</h2>
            <form method="POST">
                <input type="hidden" name="delete_container_id" id="delete_container_id">
                <button type="submit" style="background: red; color: white;">Yes, Delete</button>
                <button type="button" onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>
    <script>
        function openDeleteModal(id) {
            document.getElementById("delete_container_id").value = id;
            document.getElementById("deleteModal").style.display = "flex";
        }
    </script>

    <h3>Container Map</h3>
    <div id="map" style="height: 400px;"></div>
    <script>
        const map = L.map('map').setView([10.3157, 123.8854], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        const containers = <?= json_encode($containers) ?>;
        containers.forEach(c => {
            if (c.latitude && c.longitude) {
                const marker = L.circleMarker([c.latitude, c.longitude], {
                    radius: 8,
                    fillColor: c.status === 'Tampered' ? 'red' : 'green',
                    color: '#000',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);
                marker.bindPopup(`<strong>${c.container_id}</strong><br>${c.origin} → ${c.destination}<br>ETA: ${c.estimated_arrival}`);
            }
        });
    </script>

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
