<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

<?php
include 'db_connect.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$studyLevel = isset($_GET['study-level']) ? $_GET['study-level'] : '';

$results = [];

if ($searchTerm !== '' && $studyLevel !== 'default') {
    $stmt = $conn->prepare("SELECT * FROM programs WHERE name LIKE :term AND type = :type AND published = 1");
    $stmt->execute([
        ':term' => '%' . $searchTerm . '%',
        ':type' => $studyLevel
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($searchTerm !== '') {
    $stmt = $conn->prepare("SELECT * FROM programs WHERE name LIKE :term AND published = 1");
    $stmt->execute([':term' => '%' . $searchTerm . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="script.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            color: #000;
        }

        .results-banner{
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.3)), url('images/contact-banner.jpg');
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

        .results-section {
            max-width: 1000px;
            margin: 4rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .results-section h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #000;
            padding-left: 1rem;
        }

        .search-summary {
            margin-bottom: 2rem;
            font-size: 1.1rem;
            color: #555;
        }

        .program-list {
            list-style: none;
            padding: 0;
        }

        .program-list li {
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #000;
            background-color: #fafafa;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .program-list li:hover {
            background-color: #f0f0f0;
        }

        .program-list a {
            text-decoration: none;
            color: #000;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .no-results {
            font-size: 1.1rem;
            color: #888;
        }
    </style>
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
                <li><a href="about.html">About Us</a></li>
                <li><a href="campus.html">Campus</a></li>
                <li><a href="apply.php">Apply Now!</a></li>
            </ul>
            </div> 
        </div>
    </nav>
</section>

<section class= "results-banner">
        <h1>Courses</h1>
</section>
    <section class="results-section">
        <h2>Search Results</h2>

        <?php if ($searchTerm !== ''): ?>
            <p class="search-summary">You searched for <strong>"<?= htmlspecialchars($searchTerm) ?>"</strong></p>

            <?php if (count($results) > 0): ?>
                <ul class="program-list">
                    <?php foreach ($results as $program): ?>
                        <li>
                            <a href="program-template.php?id=<?= $program['id'] ?>">
                                <?= htmlspecialchars($program['name']) ?> (<?= htmlspecialchars(ucfirst($program['type'])) ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-results">No programs found matching your search.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="no-results">Please enter a search term.</p>
        <?php endif; ?>
    </section>
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