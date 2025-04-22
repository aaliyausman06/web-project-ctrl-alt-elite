<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

<?php
session_start();
if (!isset($_SESSION['staff_logged_in'])) {
    header("Location: staff_login.php");
    exit();
}

include 'db_connect.php';

$staff_id = $_SESSION['staff_id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

$staff_icon = !empty($staff['staff_icon']) ? $staff['staff_icon'] : 'staff_icons/default_icon.png';

$programs = $conn->prepare("SELECT * FROM programs WHERE program_leader_id = ?");
$programs->execute([$staff_id]);
$led_programs = $programs->fetchAll(PDO::FETCH_ASSOC);

$modules = $conn->prepare("SELECT m.*, p.name AS program_name, p.type AS program_type FROM modules m JOIN programs p ON m.program_id = p.id WHERE m.module_leader_id = ?");
$modules->execute([$staff_id]);
$led_modules = $modules->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
    }
    .dashboard-container {
      display: flex;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      background-color: rgb(0, 0, 0);
      color: white;
      padding: 1rem;
      position: fixed;
    }
    .sidebar h2 {
      font-size: 1.5rem;
      margin-bottom: 2rem;
      color: white;
    }
    .sidebar a {
      display: block;
      color: white;
      text-decoration: none;
      margin-bottom: 1rem;
      padding: 0.5rem;
      border-radius: 5px;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #f1f1f1;
      color: black;
    }
    .dmu-logo {
      display: block;
      max-width: 70%;
      height: auto;
      margin: 0 auto 1rem;
      justify-content: left;
      padding-right: 80px;
    }
    .staff-icon {
      width: 100px;
      height: 100px;
      padding: 10px;
      border-radius: 50%;
      border: 3px solid #fff;
      object-fit: cover;
      display: block;
      margin: 1rem auto 3rem;
    }
    .main-content {
      margin-left: 270px;
      padding: 3rem;
      flex-grow: 1;
    }
    .profile-container {
      background-color: white;
      padding: 2rem;
      border-radius: 12px;
      position: relative;
    }
    .edit-button {
      position: absolute;
      top: 2rem;
      right: 2rem;
      background-color: transparent;
      color: black;
      border: 2px solid black;
      padding: 0.6rem 1.2rem;
      border-radius: 25px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }
    .edit-button:hover {
      background-color: black;
      color: white;
    }
    .profile-header {
      display: flex;
      align-items: center;
      gap: 2rem;
      margin-bottom: 2rem;
    }
    .profile-header img {
      width: 350px;
      height: 350px;
      border-radius: 5%;
      object-fit: cover;
    }
    .profile-details h1 {
      margin: 0;
    }
    .profile-details p {
      margin: 0.2rem 0;
    }
    .toggle-buttons {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .toggle-buttons button {
      background-color: #fff;
      color: #000;
      padding: 0.8rem 1.2rem;
      border: none;
      border-radius: 8px 8px 0 0;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .toggle-buttons button.active {
      background-color: #000;
      color: white;
    }
    .toggle-buttons button:hover {
      background-color: #f1f1f1;
      color: black;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #f1f1f1;
    }
    @media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }

    .sidebar {
        position: absolute;
        width: 100%;
        height: auto;
        padding: 1rem;
        top: 0;
        left: 0;
        display: none;
        z-index: 1000;
    }

    .sidebar.open {
        display: block;
        background-color: black;
    }

    .sidebar .dmu-logo,
    .sidebar h2,
    .sidebar .staff-icon {
        margin: 0.5rem auto;
        text-align: center;
    }

    .menu-toggle {
        display: block;
        background-color: black;
        color: white;
        border: none;
        font-size: 1.2rem;
        padding: 0.8rem 1.5rem;
        cursor: pointer;
        width: 100%;
    }

    .main-content {
        margin-left: 0;
        padding: 2rem 1rem;
    }
    }
    .menu-toggle {
    display: none;
    }
    .logout-button a {
        background-color: black;
        color: white;
        border: 2px solid white;
        padding: 0.6rem 1.4rem;
        border-radius: 25px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .logout-button a:hover {
        background-color: black;
        color: white;
    }
  </style>
  <script>
    function toggleView(view) {
      document.getElementById('programs-led').style.display = view === 'programs' ? 'block' : 'none';
      document.getElementById('modules-led').style.display = view === 'modules' ? 'block' : 'none';
      document.getElementById('btn-programs').classList.toggle('active', view === 'programs');
      document.getElementById('btn-modules').classList.toggle('active', view === 'modules');
    }
    window.addEventListener('DOMContentLoaded', () => toggleView('programs'));
  </script>
</head>
<body>
  <div class="dashboard-container">
    <div class="sidebar">
      <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
      <a href="staff_dashboard.php">Dashboard</a>
      <div class="logout-button">
        <a href="logout.php">Logout</a>
      </div>
    </div>
    <div class="main-content">
      <div class="profile-container">
        <a href="edit_staff_profile.php" class="edit-button">Edit Profile</a>
        <div class="profile-header">
          <img src="<?= htmlspecialchars($staff_icon) ?>" onerror="this.onerror=null;this.src='staff_icons/default_icon.png'" alt="Staff Icon">
          <div class="profile-details">
            <h1><?= htmlspecialchars($staff['name']) ?></h1>
            <p><strong>Staff ID:</strong> <?= htmlspecialchars($staff['staff_id']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($staff['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($staff['phone']) ?></p>
            <p><strong>Office:</strong> <?= htmlspecialchars($staff['office']) ?></p>
            <p><strong>Working Hours:</strong> 8:00 a.m. - 5:00 p.m.</p>
          </div>
        </div>

        <div class="toggle-buttons">
          <button id="btn-programs" onclick="toggleView('programs')">View Programs</button>
          <button id="btn-modules" onclick="toggleView('modules')">View Modules</button>
        </div>

        <div id="programs-led">
          <h2>Programs Led</h2>
          <?php if (!empty($led_programs)): ?>
            <table>
              <thead>
                <tr>
                  <th>Program Name</th>
                  <th>Program Type</th>
                  <th>Duration</th>
                  <th>Award</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($led_programs as $prog): ?>
                  <tr>
                    <td><?= htmlspecialchars($prog['name']) ?></td>
                    <td><?= htmlspecialchars($prog['type']) ?></td>
                    <td><?= htmlspecialchars($prog['duration_years']) ?> year(s)</td>
                    <td><?= htmlspecialchars($prog['award']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>No programs available.</p>
          <?php endif; ?>
        </div>

        <div id="modules-led" style="display: none;">
          <h2>Modules Led</h2>
          <?php if (!empty($led_modules)): ?>
            <table>
              <thead>
                <tr>
                  <th>Module Name</th>
                  <th>Module Type</th>
                  <th>Program</th>
                  <th>Program Type</th>
                  <th>Year</th>
                  <th>Block</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($led_modules as $mod): ?>
                  <tr>
                    <td><?= htmlspecialchars($mod['module_name']) ?></td>
                    <td><?= htmlspecialchars($mod['module_type']) ?></td>
                    <td><?= htmlspecialchars($mod['program_name']) ?></td>
                    <td><?= htmlspecialchars($mod['program_type']) ?></td>
                    <td><?= htmlspecialchars($mod['year']) ?></td>
                    <td><?= htmlspecialchars($mod['block']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>No modules available.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>