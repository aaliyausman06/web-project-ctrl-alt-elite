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

$staff_id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id = ?");
$stmt->execute([$staff_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    die("Staff member not found.");
}

$programs = $conn->query("SELECT id, name, type FROM programs WHERE published = 1 ORDER BY type, name")->fetchAll(PDO::FETCH_ASSOC);
$modules = $conn->query("SELECT m.id, m.module_name, m.block, m.year, m.program_id, p.name AS program_name, p.type AS program_type FROM modules m JOIN programs p ON m.program_id = p.id WHERE p.published = 1 ORDER BY p.type, p.name, m.year, m.block")->fetchAll(PDO::FETCH_ASSOC);

$current_program = $conn->prepare("SELECT id FROM programs WHERE program_leader_id = ?");
$current_program->execute([$staff_id]);
$current_program_id = $current_program->fetchColumn();

$current_module_program_id = null;
$module_ids_led = $conn->prepare("SELECT id, program_id FROM modules WHERE module_leader_id = ?");
$module_ids_led->execute([$staff_id]);
$selected_module_ids = [];
foreach ($module_ids_led->fetchAll(PDO::FETCH_ASSOC) as $module) {
    $selected_module_ids[] = $module['id'];
    $current_module_program_id = $module['program_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $office = $_POST['office'];
    $program_id = $_POST['program_id'] ?? null;
    $module_ids = $_POST['module_ids'] ?? [];

    $stmt = $conn->prepare("UPDATE staff SET office = ? WHERE staff_id = ?");
    $stmt->execute([$office, $staff_id]);

    $conn->prepare("UPDATE programs SET program_leader_id = NULL WHERE program_leader_id = ?")->execute([$staff_id]);
    if ($program_id) {
        $conn->prepare("UPDATE programs SET program_leader_id = ? WHERE id = ?")->execute([$staff_id, $program_id]);
    }

    $conn->prepare("UPDATE modules SET module_leader_id = NULL WHERE module_leader_id = ?")->execute([$staff_id]);
    if (!empty($module_ids) && !in_array('none', $module_ids)) {
        foreach ($module_ids as $mid) {
            $stmt = $conn->prepare("UPDATE modules SET module_leader_id = ? WHERE id = ?");
            $stmt->execute([$staff_id, $mid]);
        }
    }

    header("Location: admin_dashboard.php?section=staff&message=Staff updated successfully");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
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

        .edit-form select[multiple] {
            height: 180px;
            background-color: #fcfcfc;
        }

        .edit-form button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            margin-top: 10px;
            border-radius: 25px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .edit-form button:hover {
            background-color: rgb(0, 0, 0);
            color: white;
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
    <script>
        function handleModuleNone(selectElement) {
            const noneOption = selectElement.querySelector('option[value="none"]');
            const selectedOptions = Array.from(selectElement.selectedOptions);

            if (selectedOptions.some(opt => opt.value === 'none')) {
                selectElement.querySelectorAll('option').forEach(opt => {
                    if (opt.value !== 'none') opt.selected = false;
                });
            } else {
                if (noneOption) noneOption.selected = false;
            }
        }
    </script>
</head>
<body>
<div class="sidebar">
    <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
        <div class= "logout-button">
        <a href="logout.php">Logout</a>
        </div>
    </div>
<div class="edit-container">
    <form class="edit-form" method="POST">
        <h1>Edit Staff Member</h1>
        <label for="staff_id">Staff ID</label>
        <input type="text" value="<?= htmlspecialchars($staff['staff_id']) ?>" readonly>

        <label for="name">Full Name</label>
        <input type="text" value="<?= htmlspecialchars($staff['name']) ?>" readonly>

        <label for="phone">Phone</label>
        <input type="text" value="<?= htmlspecialchars($staff['phone']) ?>" readonly>

        <label for="email">Email</label>
        <input type="text" value="<?= htmlspecialchars($staff['email']) ?>" readonly>

        <label for="office">Office</label>
        <input type="text" name="office" value="<?= htmlspecialchars($staff['office']) ?>" required>

        <label for="program_id">Assign as Program Leader</label>
        <select name="program_id" id="program_id">
            <option value="">None</option>
            <?php foreach ($programs as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $current_program_id == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?> (<?= $p['type'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="module_ids[]">Assign as Module Leader</label>
        <select name="module_ids[]" multiple id="module_ids" onchange="handleModuleNone(this)">
            <option value="none" <?= empty($selected_module_ids) ? 'selected' : '' ?>>None</option>
            <?php
            $group_key = '';
            foreach ($modules as $m):
                $new_group_key = $m['program_name'] . ' (' . $m['program_type'] . ') - Year ' . $m['year'];
                if ($new_group_key !== $group_key):
                    if ($group_key !== '') echo "</optgroup>";
                    echo "<optgroup label=\"$new_group_key\">";
                    $group_key = $new_group_key;
                endif;
            ?>
                <option value="<?= $m['id'] ?>" <?= in_array($m['id'], $selected_module_ids) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['module_name']) ?> - Block <?= $m['block'] ?>
                </option>
            <?php endforeach; if ($group_key !== '') echo "</optgroup>"; ?>
        </select>

        <button type="submit">Update Staff Member</button>
    </form>
</div>
</body>
</html>
