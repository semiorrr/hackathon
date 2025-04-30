<?php
session_start();
if (!isset($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'security') {
    header('Location: login.php');
    exit;
}

require '../config/db.php';
$containers = $pdo->query("SELECT * FROM containers")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LogiLock – Security</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map { height: 400px; margin-top: 20px; }
    </style>
</head>
<body>

<header>
    <h1>LogiLock – Security</h1>
    <nav>
        <a href="Securitydashboard.php">Dashboard</a>
        <a href="tamper.php">Simulate Tamper</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> (Security)</h2>

    <h3>Container Access Log</h3>
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
                <td><?= $c['status'] === 'Tampered' ? '❌ Tampered' : '✅ Sealed' ?></td>
                <td><?= $c['updated_at'] ?></td>
                <td><?= htmlspecialchars($c['location']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Container Map -->
    <h3>Container Location Map</h3>
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

    <br>
    <p><strong>Note:</strong> Security personnel can only view and verify container logs. Admin access is required to add or edit container entries.</p>
</div>

</body>
</html>
