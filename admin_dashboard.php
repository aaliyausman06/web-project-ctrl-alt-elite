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

$section = isset($_GET['section']) ? $_GET['section'] : 'programs';

if ($section == 'programs') {
    $typeFilter = $_GET['type'] ?? '';
    $publishedFilter = $_GET['published'] ?? '';

    $conditions = [];
    $params = [];

    if (!empty($typeFilter)) {
        $conditions[] = "p.type = :type";
        $params[':type'] = $typeFilter;
    }

    if ($publishedFilter !== '') {
        $conditions[] = "p.published = :published";
        $params[':published'] = $publishedFilter;
    }

    $whereClause = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

    $stmt = $conn->prepare("
        SELECT p.*, s.name AS leader_name, s.email AS leader_email, s.phone AS leader_phone
        FROM programs p
        LEFT JOIN staff s ON p.program_leader_id = s.staff_id
        $whereClause
    ");

    $stmt->execute($params);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($section == 'staff') {
    $filterType = $_GET['type'] ?? '';

    if (!empty($filterType)) {
        $stmt = $conn->prepare("
            SELECT DISTINCT s.*, 1 AS is_program_leader, 
                   EXISTS(
                       SELECT 1 FROM modules m 
                       JOIN programs p ON m.program_id = p.id
                       WHERE m.module_leader_id = s.staff_id AND p.type = :type
                   ) AS is_module_leader
            FROM staff s
            JOIN programs p ON p.program_leader_id = s.staff_id
            WHERE p.type = :type

            UNION

            SELECT DISTINCT s.*, 
                   EXISTS(
                       SELECT 1 FROM programs p 
                       WHERE p.program_leader_id = s.staff_id AND p.type = :type
                   ) AS is_program_leader,
                   1 AS is_module_leader
            FROM staff s
            JOIN modules m ON m.module_leader_id = s.staff_id
            JOIN programs p ON m.program_id = p.id
            WHERE p.type = :type
        ");
        $stmt->execute([':type' => $filterType]);
    } else {
        // No filtering — show all staff with roles
        $stmt = $conn->prepare("
            SELECT DISTINCT s.*,
                EXISTS(
                    SELECT 1 FROM programs p 
                    WHERE p.program_leader_id = s.staff_id
                ) AS is_program_leader,
                EXISTS(
                    SELECT 1 FROM modules m 
                    JOIN programs p ON m.program_id = p.id
                    WHERE m.module_leader_id = s.staff_id
                ) AS is_module_leader
            FROM staff s
        ");
        $stmt->execute();
    }

    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

} elseif ($section == 'forms') {
    $query = "
        SELECT c.*, p.name AS program_name, p.type AS program_type 
        FROM contactus c
        LEFT JOIN programs p ON c.program_id = p.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($_GET['form_type'])) {
        $query .= " AND c.form_type = ?";
        $params[] = $_GET['form_type'];
    }

    if (!empty($_GET['program_type'])) {
        $query .= " AND p.type = ?";
        $params[] = $_GET['program_type'];
    }

    if (!empty($_GET['program_id'])) {
        $query .= " AND c.program_id = ?";
        $params[] = $_GET['program_id'];
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
        }

        .dashboard-container {
            display: flex;
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

        .admin-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solidrgb(255, 255, 255);
        }

        .main-content {
            margin-left: 270px;
            padding: 5rem 2rem 2rem;
            flex-grow: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
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

        .btn, button, .button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }

        .btn:hover, button:hover, .button:hover {
            background-color: #f1f1f1;
        }

        h3 {
            margin-top:40px;
            margin-bottom: 20px;
        }

        .program {
            background: white;
            padding: 1rem;
            margin-top: 1.5rem;
            border-radius: 10px;
        }

        .year-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-left: 0.2rem;
            align-items: flex-start;
        }

        .year-btn {
            background-color:rgb(255, 255, 255);
            color: #000;
            padding: 0.8rem 1.2rem;
            border: none;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .year-btn.active {
            background-color: #0c0c0c;
            color: #fff;
        }

        .year-selection button:hover {
            background-color: black;
            color: white;
        }

        .title-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        }

        .title-container h2 {
        margin: 0;
        font-size: 1.5rem;
        }

        .action-button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top:0.5rem;
        }

        button.danger {
            background-color: rgb(0, 0, 0);
            color: white;
            padding: 0.8rem 1.4rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        button.danger:hover {
            background-color: white;
            color: rgb(0, 0, 0);
        }
        
        .dmu-logo {
            display: block;
            max-width: 70%;
            height: auto;
            margin: 0 auto 1rem;
            justify-content: left;
            padding-right: 80px;
        }

        .filter-staff, .filter-programs, .filter-form {
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        }

        .filter-staff label, .filter-programs label, .filter-form label {
        font-weight: bold;
        }

        .filter-staff select, .filter-staff button, .filter-programs select, .filter-programs button, .filter-form select, .filter-form button {
        padding: 5px 10px;
        border-radius: 5px;
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

  .sidebar h2 {
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
<section class="dashboard-container">
    <div class="sidebar">
    <img src="images/dmu white logo.png" alt="DMU Logo" class="dmu-logo">
        <img src="admin_icons/<?= htmlspecialchars($adminIcon); ?>" alt="Admin Icon" class="admin-icon">
        <a href="?section=programs" class="<?= $section == 'programs' ? 'active' : '' ?>">Programs</a>
        <a href="?section=staff" class="<?= $section == 'staff' ? 'active' : '' ?>">Staff</a>
        <a href="?section=forms" class="<?= $section == 'forms' ? 'active' : '' ?>">Student Forms</a>
        <div class= "logout-button">
        <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="main-content">
    <div class="admin-greeting">
        <h3 style="margin-bottom: 5px;">Welcome back, <?= htmlspecialchars($adminName); ?>!</h3>
    </div>
        <?php if (isset($_SESSION['email_sent'])): ?>
            <p style="color: <?= $_SESSION['email_sent'] ? 'green' : 'red'; ?>">
                <?= $_SESSION['email_sent'] ? 'Email sent successfully!' : 'Failed to send email.' ?>
            </p>
            <?php unset($_SESSION['email_sent']); ?>
        <?php endif; ?>

        <?php if ($section == 'forms'): ?>
            <h1 style="margin-bottom: 45px;">Student Forms</h1>
            <form method="GET" class="filter-form">
    <input type="hidden" name="section" value="forms">

    <label for="form_type">Form Type:</label>
    <select name="form_type" id="form_type">
        <option value="">All</option>
        <option value="Application Form" <?= ($_GET['form_type'] ?? '') === 'Application Form' ? 'selected' : '' ?>>Application Form</option>
        <option value="Open-Day Registration Form" <?= ($_GET['form_type'] ?? '') === 'Open-Day Registration Form' ? 'selected' : '' ?>>Open-Day Registration Form</option>
        <option value="Contact Us Form" <?= ($_GET['form_type'] ?? '') === 'Contact Us Form' ? 'selected' : '' ?>>Contact Us Form</option>
    </select>

    <label for="program_type">Program Type:</label>
    <select name="program_type" id="program_type">
        <option value="">All</option>
        <option value="Foundation" <?= ($_GET['program_type'] ?? '') === 'Foundation' ? 'selected' : '' ?>>Foundation</option>
        <option value="Undergraduate" <?= ($_GET['program_type'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
        <option value="Postgraduate" <?= ($_GET['program_type'] ?? '') === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
    </select>

    <label for="program_id">Program:</label>
    <select name="program_id" id="program_id">
        <option value="">All</option>
        <?php
        if (!empty($_GET['program_type'])) {
            $stmt = $conn->prepare("SELECT id, name FROM programs WHERE type = ?");
            $stmt->execute([$_GET['program_type']]);
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($programs as $program) {
                $selected = ($_GET['program_id'] ?? '') == $program['id'] ? 'selected' : '';
                echo "<option value=\"{$program['id']}\" $selected>" . htmlspecialchars($program['name']) . "</option>";
            }
        }
        ?>
    </select>
</form>

<script>
    document.querySelectorAll('.filter-form select').forEach(select => {
        select.addEventListener('change', function () {
            this.form.submit();
        });
    });
</script>
              <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Form Type</th><th>First Name</th><th>Last Name</th><th>Email</th>
                            <th>Gender</th><th>Phone</th><th>Course</th>
                            <th>Year of Entry</th><th>Message</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id']) ?></td>
                                <td><?= htmlspecialchars($student['form_type']) ?></td>
                                <td><?= htmlspecialchars($student['firstname']) ?></td>
                                <td><?= htmlspecialchars($student['lastname']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['Sex']) ?></td>
                                <td><?= htmlspecialchars($student['Phone']) ?></td>
                                <td><?= htmlspecialchars($student['program_name']) ?></td>
                                <td><?= htmlspecialchars($student['Year of Entry']) ?></td>
                                <td><?= htmlspecialchars($student['Your Message']) ?></td>
                                <td>
                                    <form action="send_email.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="email" value="<?= htmlspecialchars($student['email']) ?>">
                                        <button type="submit">Send Email</button>
                                    </form>
                                    <form action="delete_registration.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $student['id'] ?>">
                                        <button type="submit" class="danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

        <?php elseif ($section == 'staff'): ?>
        <h1 >Staff</h1>
    <!-- staff section goes here -->
    <div class="button-container">
    <a href="add_staff.php" class="button">Add New Staff</a>
    </div>
    <form method="GET" class= "filter-staff" style="margin-bottom: 20px;">
        <input type="hidden" name="section" value="staff">
        <label for="type">Filter by Program Type:</label>
        <select name="type" id="type" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="Foundation" <?= $filterType === 'Foundation' ? 'selected' : '' ?>>Foundation</option>
            <option value="Undergraduate" <?= $filterType === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
            <option value="Postgraduate" <?= $filterType === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
        </select>
    </form>
    <table>
        <thead>
            <tr>
                <th>Staff ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Office</th><th>Roles</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['staff_id']) ?></td>
                    <td><?= htmlspecialchars($member['name']) ?></td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['phone']) ?></td>
                    <td><?= htmlspecialchars($member['office']) ?></td>
                    <td>
                        <?php
                            $roles = [];
                            if ($member['is_program_leader']) $roles[] = 'Program Leader';
                            if ($member['is_module_leader']) $roles[] = 'Module Leader';
                            echo $roles ? implode(', ', $roles) : '—';
                        ?>
                    </td>
                    <td>
                        <form action="staff_profile.php" method="GET" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $member['staff_id'] ?>">
                            <button type="submit">View Profile</button>
                        </form>
                        <form action="edit_staff.php" method="GET" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $member['staff_id'] ?>">
                            <button type="submit">Edit</button>
                        </form>
                        <form action="delete_staff.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $member['staff_id'] ?>">
                            <button class="danger" onclick="return confirm('Delete this staff member?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        <?php elseif ($section == 'programs'): ?>
            <h1>Programs</h1>

            <div class="button-container">
                    <a href="add_program.php" class="button">Add New Program</a>
            </div>

            <form method="GET" class="filter-form" style="margin-bottom: 20px;">
            <input type="hidden" name="section" value="programs">

            <label for="type_filter">Filter by Program Type:</label>
            <select name="type" id="type_filter">
                <option value="">All</option>
                <option value="Foundation" <?= isset($_GET['type']) && $_GET['type'] === 'Foundation' ? 'selected' : '' ?>>Foundation</option>
                <option value="Undergraduate" <?= isset($_GET['type']) && $_GET['type'] === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
                <option value="Postgraduate" <?= isset($_GET['type']) && $_GET['type'] === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
            </select>

            <label for="published_filter">Published Status:</label>
            <select name="published" id="published_filter">
                <option value="">All</option>
                <option value="1" <?= isset($_GET['published']) && $_GET['published'] === '1' ? 'selected' : '' ?>>Published</option>
                <option value="0" <?= isset($_GET['published']) && $_GET['published'] === '0' ? 'selected' : '' ?>>Unpublished</option>
            </select>
        </form>

        <script>
            // Auto-submit the program filter form when any select changes
            document.querySelectorAll('.filter-form select').forEach(select => {
                select.addEventListener('change', function () {
                    this.form.submit();
                });
            });
        </script>
            <!-- Program List -->
            <?php foreach ($programs as $program): ?>
                <div class="program">
                    <div class= "title-container">
                    <h2><?= htmlspecialchars($program['name']) ?></h2>
                    <div class="action-button-container">
                    <form action="edit_program.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $program['id'] ?>">
                        <button type="submit">Edit</button>
                    </form>

                    <form action="add_module.php" method="GET" style="display:inline;">
                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                        <button type="submit">Add Module</button>
                    </form>

                    <form action="delete_program.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $program['id'] ?>">
                        <button type="submit" class="danger" onclick="return confirm('Delete this program?')">Delete</button>
                    </form>

                    <form action="publish_program.php" method="GET" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $program['id'] ?>">
                        <input type="hidden" name="action" value="<?= $program['published'] ? 'unpublish' : 'publish' ?>">
                        <button type="submit"><?= $program['published'] ? 'Unpublish' : 'Publish' ?></button>
                    </form>
                    </div>
                    </div>

                    <p><strong>Program Leader:</strong>
                    <?php if ($program['leader_name']): ?>
                        <?= htmlspecialchars($program['leader_name']) ?>
                    <?php else: ?>
                        No program leader assigned.
                    <?php endif; ?></p>
                    <br>
                    <p><strong>Type:</strong> <?= htmlspecialchars($program['type']) ?></p>
                    <p><strong>Duration:</strong> <?= htmlspecialchars($program['duration_years']) ?> year(s)</p>
                    <p><strong>Award:</strong> <?= htmlspecialchars($program['award']) ?></p>
                    <p><strong>Intake:</strong> <?= htmlspecialchars($program['intake']) ?></p>
                    <p><strong>Fees:</strong> <?= htmlspecialchars($program['fees']) ?></p>
                    <p><strong>Status:</strong> <?= $program['published'] ? 'Published' : 'Unpublished' ?></p>
                    
                    <h3>Modules by Year</h3>
                    <div class="year-toggle">
                        <button class="year-btn active" onclick="showYear(this, '<?= $program['id'] ?>', 1)">Year 1</button>
                        <button class="year-btn" onclick="showYear(this, '<?= $program['id'] ?>', 2)">Year 2</button>
                        <button class="year-btn" onclick="showYear(this, '<?= $program['id'] ?>', 3)">Year 3</button>
                    </div>

                    <?php
                    $stmt = $conn->prepare("
                        SELECT m.*, s.name AS leader_name, s.email AS leader_email, s.phone AS leader_phone
                        FROM modules m
                        LEFT JOIN staff s ON m.module_leader_id = s.staff_id
                        WHERE m.program_id = :program_id
                    ");
                    $stmt->bindParam(':program_id', $program['id']);
                    $stmt->execute();
                    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                    $modules_by_year = [];
                    foreach ($modules as $module) {
                        $modules_by_year[$module['year']][] = $module;
                    }

                    foreach ([1, 2, 3] as $year): ?>
                        <div class="modules-by-year program-<?= $program['id'] ?> year-<?= $year ?>" style="<?= $year === 1 ? '' : 'display:none;' ?>">
                            <?php if (!empty($modules_by_year[$year])): ?>
                                <h4>Year <?= $year ?></h4>
                                <table>
                                    <thead>
                                        <tr><th>Block</th><th>Module Name</th><th>Type</th><th>Teacher</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($modules_by_year[$year] as $module): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($module['block']) ?></td>
                                                <td><?= htmlspecialchars($module['module_name']) ?></td>
                                                <td><?= htmlspecialchars($module['module_type']) ?></td>
                                                <td>
                                                    <?php if ($module['leader_name']): ?>
                                                        <?= htmlspecialchars($module['leader_name']) ?><br>
                                                        <small>
                                                    <?php else: ?>
                                                        <em>Unassigned</em>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                <form action="edit_module.php" method="GET" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $module['id'] ?>">
                                                    <button type="submit">Edit</button>
                                                </form>

                                                <form action="delete_module.php" method="GET" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?= $module['id'] ?>">
                                                    <button type="submit" class="danger" onclick="return confirm('Delete this module?')">Delete</button>
                                                </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No modules for Year <?= $year ?>.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<script>
function showYear(btn, programId, year) {
    const container = btn.closest('.year-toggle');
    const buttons = container.querySelectorAll('.year-btn');
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const allYearSections = document.querySelectorAll(`.program-${programId}`);
    allYearSections.forEach(section => section.style.display = 'none');

    const activeSection = document.querySelector(`.program-${programId}.year-${year}`);
    if (activeSection) activeSection.style.display = 'block';
}
</script>
</body>
</html>