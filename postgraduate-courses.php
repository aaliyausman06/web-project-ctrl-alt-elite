<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

<?php 
include 'db_connect.php';

try {
    $stmt = $conn->prepare("
        SELECT p.*, s.name AS leader_name
        FROM programs p
        LEFT JOIN staff s ON p.program_leader_id = s.staff_id
        WHERE p.published = 1 AND p.type = 'Postgraduate'
    ");
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($programs)) {
        throw new Exception("No postgraduate programs found in the database.");
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postgraduate Courses</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .pg-banner{
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.3)), url('images/pexels-pavel-danilyuk-8438922.jpg');
            background-attachment: fixed;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .pg-banner a {
        text-decoration: none;
        color: white;
        }
    </style>
    <script src="script.js"></script>
</head>
<body>
<section class="header"> 
    <nav>
        <a href="index.html" class="logo">
            <img src="images/logo with name.png" alt="University Logo">
        </a>
        <div class="nav-buttons">
            <button class="menu-toggle">Menu</button>
            <a href="admin_login.php"><button class="admin-login-btn">Log In</button></a>
        </div>
    </nav>

    <!-- Sidebar Menu -->
    <div class="sidebar-menu">
        <div class="sidebar-logo">
            <a href="index.html">
                <img src="images/dmu white logo.png" alt="University Logo">
            </a>
        </div>
        <button class="close-menu">&times;</button>
        <nav>
            <ul>
                <li class="menu-item">
                    <span class="menu-title">Study ▸</span>
                    <ul class="submenu">
                        <li><a href="foundation-courses.php">International Foundataion Year</a></li>
                        <li><a href="undergraduate-courses.php">Undergraduate</a></li>
                        <li><a href="postgraduate-courses.php">Postgraduate</a></li>
                    </ul>
                </li>
                <li class="menu-item">
                    <span class="menu-title">Research ▸</span>
                    <ul class="submenu">
                        <li><a href="#">Research Topics</a></li>
                        <li><a href="#">Research Impact</a></li>
                    </ul>
                </li>
                <li class="menu-item">
                    <span class="menu-title">Collaborate ▸</span>
                    <ul class="submenu">
                        <li><a href="#">Industry Partnerships</a></li>
                        <li><a href="#">Community Engagement</a></li>
                    </ul>
                </li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="#">Jobs</a></li>
                <li><a href="#">Alumni</a></li>
            </ul>
            </div> 
        </div>
    </nav>
</section>
    <section class= "pg-banner">
    <header>
        <h1>Postgraduate Courses</h1>
        <nav>
            <a href="index.html">Home</a> / <a href="postgraduate-courses.php">Postgraduate Courses</a>
        </nav>
    </header>
    </section>
    <main>
        <?php if (is_array($programs) && !empty($programs)): ?>
            <section class="course-container">
                <?php foreach ($programs as $program): ?>
                    <div class="course-card">
                        <img src="program_images/<?= htmlspecialchars($program['program_image']); ?>" alt="Program Image">
                        <div class="course-info">
                            <p>👤 <?= htmlspecialchars($program['leader_name'] ?? 'N/A'); ?> | 🪑 10 Seats | ⏳ <?= htmlspecialchars($program['duration_years']); ?> Year(s)</p>
                            <h3><?php echo htmlspecialchars($program['name']); ?></h3>
                            <p>Explore advanced topics and research in your chosen field with our intensive postgraduate programs.</p>
                            <a href="program-template.php?id=<?php echo $program['id']; ?>" class="apply-btn">Apply now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <p>No postgraduate programs available at the moment.</p>
        <?php endif; ?>
    </main>
    <footer class="university-footer">
    <div class="footer-top">
      <div class="footer-info">
        <img src="images/dmu white logo.png" alt="University Logo" class="footer-logo">
        <p>Dubai, United Arab Emirates<br>Building #12, Dubai Internet City</p>
        <p>Tel: +971 4 123 4567</p>
  
        <div class="social-icons">
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
          <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
  
      <div class="footer-links">
        <h3>Explore</h3>
        <ul>
          <li><a href="#">Culture and Collections →</a></li>
          <li><a href="#">Schools, Institutes & Departments →</a></li>
          <li><a href="#">Services and Facilities →</a></li>
        </ul>
      </div>
    </div>
  
    <div class="footer-bottom">
      <div class="footer-legal">
        <a href="#">Privacy</a>
        <a href="#">Legal</a>
        <a href="#">Accessibility</a>
        <a href="#">Freedom of Information</a>
        <a href="#">Cookies</a>
        <a href="apply.php">Contact Us</a>
      </div>
      <p>&copy; De Montfort University 2025. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>