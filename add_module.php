<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP)
author@Shekinah Glory (PHP) -->

<?php
session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

$stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
$stmt->bindParam(':username', $_SESSION['admin_username']);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$adminName = $admin['username'] ?? 'Admin';
$adminIcon = $admin['admin_icon'] ?? 'default_icon.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = intval($_POST['program_id']);
    $year = intval($_POST['year']);
    $block = htmlspecialchars($_POST['block']);
    $module_name = htmlspecialchars($_POST['module_name']);
    $module_leader_id = intval($_POST['module_leader_id']);
    $module_type = $_POST['module_type'];

    $stmt = $conn->prepare("INSERT INTO modules 
        (program_id, year, block, module_name, module_leader_id, module_type) 
        VALUES (:program_id, :year, :block, :module_name, :module_leader_id, :module_type)");
    $stmt->bindParam(':program_id', $program_id);
    $stmt->bindParam(':year', $year);
    $stmt->bindParam(':block', $block);
    $stmt->bindParam(':module_name', $module_name);
    $stmt->bindParam(':module_leader_id', $module_leader_id);
    $stmt->bindParam(':module_type', $module_type);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?message=Module added successfully.");
        exit();
    } else {
        echo "Error adding module.";
    }
}

$program_id = intval($_GET['program_id']);
$stmt = $conn->prepare("SELECT staff_id, name FROM staff");
$stmt->execute();
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Module</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
        }

        .admin-icon {
            width: 100px;           
            height: 100px;
            padding: 10px;
            border-radius: 50%;
            border: 3px solid #fff;
            object-fit: cover;
            display: block;
            margin: 1rem auto;
            margin-bottom: 5rem;
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

        .edit-container {
            margin-left: 270px;
            padding: 2rem 2rem 2rem;
        }

        .edit-form {
            background-color: #fff;
            padding: 2rem 4rem 2rem 2.5rem;
            border-radius: 15px;
            max-width: 800px;
            margin: 0 auto;
        }

        .edit-form h1 {
            margin-bottom: 2rem;
        }

        .edit-form label {
            font-weight: 600;
            margin-top: 1.2rem;
            display: block;
        }

        .edit-form input,
        .edit-form select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 0.5rem;
            font-size: 1rem;
        }

        .edit-form button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            margin-top:10px;
            border-radius: 25px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .edit-form button:hover {
            background-color: rgb(0, 0, 0);
            color: white;
        }

        .dmu-logo {
            display: block;
            max-width: 70%;
            height: auto;
            margin: 0 auto 1rem;
            padding-right: 80px;
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
<div class="sidebar">
    <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
        <div class= "logout-button">
        <a href="logout.php">Logout</a>
        </div>
    </div>
    <div class="edit-container">
        <form class="edit-form" action="add_module.php" method="POST">
        <h1>Add New Module</h1>
        <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">

        <label for="year">Year:</label>
        <input type="number" name="year" required>

        <label for="block">Block:</label>
        <input type="text" name="block" required>

        <label for="module_name">Module Name:</label>
        <input type="text" name="module_name" required>

        <label for="module_type">Module Type:</label>
        <select name="module_type" required>
            <option value="Mandatory">Mandatory</option>
            <option value="Elective">Elective</option>
        </select>

        <label for="module_leader_id">Module Leader:</label>
        <select name="module_leader_id" required>
            <option value="">Select a Module leader</option>
            <?php foreach ($staff as $member): ?>
                <option value="<?= $member['staff_id']; ?>"><?= htmlspecialchars($member['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Add Module</button>
    </form>
</div>
</body>
</html>