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
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $type = htmlspecialchars($_POST['type']);
    $duration_years = intval($_POST['duration_years']);
    $award = htmlspecialchars($_POST['award']);
    $intake_month = $_POST['intake_month'];
    $intake_year = $_POST['intake_year'];
    $intake = "$intake_month $intake_year";
    $fees = htmlspecialchars($_POST['fees']);
    $course_description = htmlspecialchars($_POST['course_description']);
    $imageName = $program['program_image']; 


if (isset($_FILES['program_image']) && $_FILES['program_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'program_images/';
    $fileTmpPath = $_FILES['program_image']['tmp_name'];
    $fileName = basename($_FILES['program_image']['name']);
    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $fileName); // sanitize
    $destPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $imageName = $fileName;
    }
}

    $stmt = $conn->prepare("UPDATE programs 
    SET name = :name, type = :type, duration_years = :duration_years, 
        award = :award, intake = :intake, fees = :fees, 
        course_description = :course_description, program_leader_id = :program_leader_id,
        program_image = :program_image
    WHERE id = :id");


    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':duration_years', $duration_years);
    $stmt->bindParam(':award', $award);
    $stmt->bindParam(':intake', $intake);
    $stmt->bindParam(':fees', $fees);
    $stmt->bindParam(':course_description', $course_description);
    $stmt->bindParam(':program_image', $imageName);
    $stmt->bindParam(':id', $id);
    $program_leader_id = intval($_POST['program_leader_id']);
    $stmt->bindParam(':program_leader_id', $program_leader_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?message=Program updated successfully.");
        exit();
    } else {
        echo "Error updating program.";
    }
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM programs WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$program = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $conn->prepare("SELECT staff_id, name FROM staff");
$stmt->execute();
$program_leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt->execute();
$program_leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$intake_parts = explode(' ', $program['intake']);
$current_month = $intake_parts[0];
$current_year = $intake_parts[1] ?? '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Program</title>
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
            background-color:rgb(0, 0, 0);
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
            background-color:#f1f1f1;
            color: black;
        }

        .edit-container {
            margin-left: 270px;
            padding: 2rem 2rem 2rem;
            flex-grow: 1;
        }

        .edit-form {
            background-color: #fff;
            padding: 2rem 4rem 2rem 2.5rem;
            border-radius: 15px;
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
            max-width: 800px;
            margin: 0 auto;
            box-sizing: border-box;
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
        .edit-form textarea,
        .edit-form select {
            width: 100%;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 0.5rem;
            font-size: 1rem;
        }

        .edit-form select {
            width: auto;
            padding: 0.75rem;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-top: 0.5rem;
            font-size: 1rem;
        }
        
        .edit-form textarea {
            min-height: 120px;
            resize: none;
        }

        .edit-form button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            margin-top:10px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .edit-form button:hover {
            background-color:rgb(0, 0, 0);
            color: white;
        }

        .dmu-logo {
            display: block;
            max-width: 70%;
            height: auto;
            margin: 0 auto 1rem;
            justify-content: left;
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
    <div class="dashboard-container">
    <div class="sidebar">
    <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
        <div class= "logout-button">
        <a href="logout.php">Logout</a>
        </div>
    </div>

        <div class="edit-container">
            <form class="edit-form" action="edit_program.php" method="POST" enctype="multipart/form-data">
                <h1>Edit Program</h1>
                <input type="hidden" name="id" value="<?= $program['id']; ?>">

                <label for="name">Program Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($program['name']); ?>" required>

                <label for="program_leader_id">Program Leader:</label>
                <select name="program_leader_id" required>
                    <option value="">Select a leader</option>
                    <?php foreach ($program_leaders as $leader): ?>
                        <option value="<?= $leader['staff_id']; ?>" 
                    <?= $program['program_leader_id'] == $leader['staff_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($leader['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <label for="type">Program Type:</label>
                <select name="type" required>
                    <option value="Foundation" <?= $program['type'] === 'Foundation' ? 'selected' : ''; ?>>Foundation</option>
                    <option value="Undergraduate" <?= $program['type'] === 'Undergraduate' ? 'selected' : ''; ?>>Undergraduate</option>
                    <option value="Postgraduate" <?= $program['type'] === 'Postgraduate' ? 'selected' : ''; ?>>Postgraduate</option>
                </select>

                <label for="duration_years">Duration (Years):</label>
                <input type="number" name="duration_years" value="<?= htmlspecialchars($program['duration_years']); ?>" required>

                <label for="award">Award:</label>
                <input type="text" name="award" value="<?= htmlspecialchars($program['award']); ?>" required>
                <label for="intake">Intake:</label>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <select name="intake_month" required>
                        <option value="January" <?= $current_month === 'January' ? 'selected' : '' ?>>January</option>
                        <option value="September" <?= $current_month === 'September' ? 'selected' : '' ?>>September</option>
                    </select>

                    <select name="intake_year" required>
                        <?php for ($y = 2025; $y <= 2030; $y++): ?>
                            <option value="<?= $y ?>" <?= $current_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <label for="fees">Fees (e.g., AED 71,610):</label>
                <input type="text" name="fees" value="<?= htmlspecialchars($program['fees']); ?>" required>

                <label for="course_description">Course Description:</label>
                <textarea name="course_description" required><?= htmlspecialchars($program['course_description']); ?></textarea>
                
                <label for="program_image">Upload Program Image:</label>
                <input type="file" name="program_image" accept="image/*">

                <button type="submit">Update Program</button>
        </div>
    </div>
</body>
</html>