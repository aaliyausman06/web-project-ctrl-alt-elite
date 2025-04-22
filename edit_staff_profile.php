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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['staff_icon'])) {
    $upload_dir = 'staff_icons/';
    $file_name = basename($_FILES['staff_icon']['name']);
    $target_file = $upload_dir . time() . '_' . $file_name;

    if (move_uploaded_file($_FILES['staff_icon']['tmp_name'], $target_file)) {
        $update = $conn->prepare("UPDATE staff SET staff_icon = ? WHERE staff_id = ?");
        $update->execute([$target_file, $staff_id]);
        $_SESSION['success'] = "Profile picture updated successfully.";
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $error = "Failed to upload image.";
    }
}
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
      border-radius: 10%;
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
    .edit-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .edit-header {
      display: flex;
      align-items: center;
      gap: 2rem;
      margin-bottom: 2rem;
    }
    .edit-header img {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
    }
    .edit-header .details p {
      margin: 0.3rem 0;
    }
    .edit-header .details h1 {
      margin: 0;
      font-size: 2rem;
    }
    form label {
      display: block;
      margin: 1rem 0 0.3rem;
    }
    .update-pic-button {
      position: absolute;
      bottom: 2rem;
      right: 2rem;
      background-color: transparent;
      color: black;
      border: 2px solid black;
      padding: 1rem 2rem;
      border-radius: 25px;
      font-weight: 600;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    .update-pic-button:hover {
      background-color: black;
      color: white;
    }
    .back-link {
      display: inline-block;
      margin-top: 1rem;
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
</head>
<body>
<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰ Menu</button>

  <div class="dashboard-container">
    <div class="sidebar">
      <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
      <a href="staff_dashboard.php">Dashboard</a>
      <div class= "logout-button">
        <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="main-content">
      <div class="profile-container">
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
        <form action="" method="POST" enctype="multipart/form-data">
          <label for="staff_icon"><strong>Select new profile picture:</strong></label>
          <input type="file" name="staff_icon" id="staff_icon" required>
          <button type="submit" class="update-pic-button">Update Profile</button>
        </form>
</div>
</div>
</div>
</div>
</body>
</html>