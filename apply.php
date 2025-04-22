<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS)
author@Shekinah Glory (PHP) -->

<?php
include 'db_connect.php';
$stmt = $conn->prepare("SELECT id, name FROM programs WHERE published = 1");
$stmt->execute();
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application Form</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="script.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }
        .banner {
            background-color: #000;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .banner{
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

        .application-form {
            max-width: 600px;
            background-color: white;
            margin: 3rem auto;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .application-form form {
            display: flex;
            flex-direction: column;
        }

        .application-form h1 {
            text-align: center;
        }

        label {
            font-weight: 600;
            margin-top: 1rem;
        }
        input, select, textarea {
            padding: 0.75rem;
            margin-top: 0.5rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border: 2px solid rgb(0, 0, 0);
        }
        button:hover {
            background-color: rgb(0, 0, 0);
            color: rgb(255, 255, 255);
        }
        .button-container {
        display: flex;
        justify-content: center;
        margin-bottom: 1rem;
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
<section class="banner">
<h1>Student Registration Form</h1>
</section>

<section class="application-form">
    <form action="process_form.php" method="POST">

        <label for="form_type">Form Type</label>
        <select name="form_type" required>
            <option value="Application Form">Application Form</option>
            <option value="Open-Day Registration Form">Open-Day Registration</option>
            <option value="Contact Us Form">Contact Us Form</option>
        </select>

        <label for="firstname">First Name</label>
        <input type="text" name="firstname" required>

        <label for="lastname">Last Name</label>
        <input type="text" name="lastname" required>

        <label for="email">Email</label>
        <input type="email" name="email" required>

        <label for="sex">Gender</label>
        <select name="sex" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>

        <label for="phone">Phone</label>
        <input type="number" name="phone" required>

        <label for="program_id">Course</label>
        <select name="program_id" required>
            <option value="" disabled selected>Select a course</option>
            <?php foreach ($programs as $program): ?>
                <option value="<?= $program['id'] ?>"><?= htmlspecialchars($program['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="year_of_entry">Year of Entry</label>
        <select name="year_of_entry" required>
            <option value="2025">2025</option>
            <option value="2026">2026</option>
            <option value="2027">2027</option>
            <option value="2028">2028</option>
            <option value="2029">2029</option>
            <option value="2030">2030</option>
        </select>

        <label for="message">Your Message</label>
        <textarea name="message"></textarea>
        <div class="button-container"></div>
        <button type="submit">Submit Application</button>
        </div>
    </form>
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
