<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

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

$programs = $conn->query("SELECT id, name, type FROM programs WHERE published = 1 ORDER BY type, name")->fetchAll(PDO::FETCH_ASSOC);
$modules = $conn->query("SELECT m.id, m.module_name, m.block, m.year, m.program_id, p.name AS program_name, p.type AS program_type FROM modules m JOIN programs p ON m.program_id = p.id WHERE p.published = 1 ORDER BY p.type, p.name, m.year, m.block")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = $_POST['phone'];
    $office = $_POST['office'];
    $program_id = $_POST['program_id'] ?? null;
    $module_ids = $_POST['module_ids'] ?? [];

    $name_parts = explode(' ', $name);
    $baseUsername = strtolower($name_parts[0] . ($name_parts[1] ?? ''));
    $username = $baseUsername;
    $email = $username . '@my365.dmu.ac.uk';

        $i = 1;
        while (true) {
            $checkEmail = $conn->prepare("SELECT COUNT(*) FROM staff WHERE email = ?");
            $checkEmail->execute([$email]);
            if ($checkEmail->fetchColumn() == 0) break;

            $username = $baseUsername . $i;
            $email = $username . '@my365.dmu.ac.uk';
            $i++;
        }

    $password = password_hash('staff123', PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO staff (name, phone, office, username, email, password, staff_icon) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $office, $username, $email, $password, 'default_icon.png']);
    $staff_id = $conn->lastInsertId();

    if ($program_id) {
        $conn->prepare("UPDATE programs SET program_leader_id = ? WHERE id = ?")->execute([$staff_id, $program_id]);
    }

    if (!empty($module_ids) && !in_array('none', $module_ids)) {
        $stmt = $conn->prepare("UPDATE modules SET module_leader_id = ? WHERE id = ?");
        foreach ($module_ids as $mid) {
            $stmt->execute([$staff_id, $mid]);
        }
    }

    header("Location: admin_dashboard.php?section=staff&message=Staff added successfully");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff</title>
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
        <h1>Add New Staff Member</h1>

        <label for="name">Full Name</label>
        <input type="text" name="name" required>

        <label for="phone">Phone</label>
        <input type="text" name="phone" required>

        <label for="office">Office</label>
        <input type="text" name="office" required>

        <label for="program_id">Assign as Program Leader</label>
        <select name="program_id" id="program_id">
            <option value="">None</option>
            <?php foreach ($programs as $p): ?>
                <option value="<?= $p['id'] ?>">
                    <?= htmlspecialchars($p['name']) ?> (<?= $p['type'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="module_ids[]">Assign as Module Leader</label>
        <select name="module_ids[]" multiple id="module_ids" onchange="handleModuleNone(this)">
            <option value="none">None</option>
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
                <option value="<?= $m['id'] ?>" data-program="<?= $m['program_id'] ?>">
                    <?= htmlspecialchars($m['module_name']) ?> - <?= $m['block'] ?>
                </option>
            <?php endforeach; if ($group_key !== '') echo "</optgroup>"; ?>
        </select>

        <button type="submit">Add Staff Member</button>
    </form>
</div>
</body>
</html>
