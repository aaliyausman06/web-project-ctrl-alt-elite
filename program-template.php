<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

<?php
include 'db_connect.php';

$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($program_id <= 0) {
    die("Invalid program ID.");
}

$stmt = $conn->prepare("SELECT p.*, s.name AS leader_name, s.email AS leader_email, s.phone AS leader_phone, s.office AS leader_office FROM programs p LEFT JOIN staff s ON p.program_leader_id = s.staff_id WHERE p.id = :id");
$stmt->bindParam(':id', $program_id);
$stmt->execute();
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    die("Program not found.");
}

$stmt = $conn->prepare("SELECT 
        m.*, s.name AS teacher_name, s.email AS teacher_email, s.phone AS teacher_phone, s.office AS teacher_office, s.staff_icon AS teacher_icon FROM modules m LEFT JOIN staff s ON m.module_leader_id = s.staff_id WHERE m.program_id = :program_id ORDER BY m.year, m.block");
$stmt->bindParam(':program_id', $program_id);
$stmt->execute();
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_modules = [
    '1' => [], // Level 4
    '2' => [], // Level 5
    '3' => []  // Level 6
];

foreach ($modules as $module) {
    $grouped_modules[$module['year']][] = $module;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($program['name']); ?></title>
    <link rel="stylesheet" href="style.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .course-description,
        .modules,
        .entry-requirements,
        .teaching-assessment {
            font-family: 'Segoe UI', sans-serif;
            max-width: 1000px;
            margin: 60px auto;
            padding: 2rem;
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .course-description h2,
        .modules h2,
        .entry-requirements h2,
        .teaching-assessment h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            margin-top: 2rem;
            padding-left: 0.2rem;
            color: #000;
            border-left: 4px solid #000;
            padding-left: 1rem;
        }

        .course-description h3,
        .modules h3,
        .entry-requirements h3,
        .teaching-assessment h3 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
            margin-top: 2rem;
            padding-left: 0.2rem;
            color: #000;
        }
        
        .course-description p,
        .modules p,
        .entry-requirements p,
        .teaching-assessment p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #333;
            line-height: 1.8;
            text-align: justify;
            text-justify: inter-word;
        }
        

        .program-header {
            background-image: 
                linear-gradient(rgba(24, 25, 26, 0.8), rgba(46, 46, 48, 0)),
                url('program_images/<?= htmlspecialchars($program['program_image']); ?>');
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            min-height: 340px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .program-banner {
            background-color: #0c0c0c;
            color: white;
            padding: 3rem 1.5rem;
            font-family: 'Segoe UI', sans-serif;
        }

        .program-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
            border-bottom: 1px solid #333;
            padding-bottom: 1rem;
        }

        .banner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .banner-card {
            background-color: #111;
            padding: 1.2rem;
            border: 1px solid #222;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .banner-card.highlight {
            background-color: #f9f2d4;
            color: black;
            font-weight: 500;
        }

        .banner-card .label {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 0.2rem;
            text-transform: uppercase;
            font-weight: 500;
        }

        .banner-card h2, 
        .banner-card h3 {
            margin: 0.3rem 0;
        }

        .banner-card p {
            font-size: 0.85rem;
            margin: 0.2rem 0;
        }

        .banner-card a {
            font-size: 0.85rem;
            margin: 0.2rem 0;
            color: #999;
            text-decoration: underline;
        }

        .banner-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .apply-button,
        .open-day-button {
            background-color: white;
            color: black;
            padding: 0.8rem 1.4rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .open-day-button {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .apply-button:hover {
            background-color: #eaeaea;
        }

        .open-day-button:hover {
            background-color: white;
            color: black;
        }

        .year-selection {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-left: 0.2rem;
            align-items: flex-start;
        }

        .year-selection button {
            background-color:rgb(255, 255, 255);
            color: #000;
            padding: 0.8rem 1.2rem;
            border: none;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .year-selection button.active {
            background-color: #0c0c0c;
            color: #fff;
        }

        .year-selection button:hover {
            background-color: black;
            color: white;
        }

        .year {
            margin-bottom: 2rem;
        }

        .year h3 {
            font-size: 1.4rem;
            color:rgb(0, 0, 0);
            margin-bottom: 1rem;
        }

        .year ul {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .year ul li {
            background-color: #fff;
            border-left: 4px solid #000;
            padding: 1rem;
            margin-bottom: 0.7rem;
            border-radius: 0px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .year ul li:hover {
            background-color:rgb(0, 0, 0);
            color: white;
        }

        .year ul li div {
            margin-top: 0.5rem;
            font-size: 0.95rem;
            color: #333;
        }

        .staff-member {
            display: flex;
            align-items: center;
            margin-top: 1rem;
            background-color: #fff;
            padding: 0.8rem;
            border-radius: 0%;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }

        .staff-image {
            width: 150px;
            height: 150px;
            border-radius: 0%;
            object-fit: cover;
            margin-right: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .staff-info h5 {
            margin: 0;
            font-size: 1rem;
        }

        .staff-info p {
            margin: 0.2rem 0;
            font-size: 0.85rem;
        }

        .entry-requirements ul {
            padding-left: 1.5rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .action-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 1rem;
            font-family: 'Segoe UI', sans-serif;
        }

        .action-card {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s ease;
            
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .action-card img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .action-card h3 {
            font-size: 1.5rem;
            color: #000;
            margin-bottom: 0.5rem;
        }

        .action-card p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 1.2rem;
            margin: 20px;
            line-height: 1.6;
        }

        .card-btn {
            background-color: rgb(255, 255, 255);
            color: rgb(0, 0, 0);
            padding: 0.8rem 1.4rem;
            padding: 10px 20px;
            margin-bottom: 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border: 2px solid rgb(0, 0, 0);
        }

        .card-btn:hover {
            background-color: black;
            color: white;
        }

        html {
        scroll-behavior: smooth;
        }

        /* .side-nav {
        position: sticky;
        top: 0;
        align-self: flex-start;
        padding: 1.5rem;
        margin: 10px 10px;
        max-width: 220px;
        font-family: 'Segoe UI', sans-serif;
        }

        .side-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        }

        .side-nav ul li {
        margin-bottom: 1rem;
        }

        .side-nav ul li a {
        text-decoration: none;
        color: #000;
        font-weight: 500;
        transition: color 0.3s ease;
        }

        .side-nav ul li a:hover {
        color:rgb(142, 24, 24);
        } */
    </style>

    <script src="script.js"></script>
    <script>
    function showYear(yearId) {
        const years = document.querySelectorAll('.year');
        years.forEach(year => year.style.display = 'none');
        document.getElementById(yearId).style.display = 'block';

        const buttons = document.querySelectorAll('.year-selection button');
        buttons.forEach(btn => btn.classList.remove('active'));

        const activeButton = Array.from(buttons).find(btn =>
            btn.getAttribute('onclick')?.includes(yearId)
        );
        if (activeButton) activeButton.classList.add('active');
    }

    function toggleModuleInfo(id) {
        const el = document.getElementById(id);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', () => {
        showYear('level4');
    });
</script>

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

<section class="program-header">
    
</section>

<section class="program-banner">
  <h1 class="program-title"><?php echo htmlspecialchars($program['name']); ?></h1>

  <div class="banner-grid">
    <div class="banner-card highlight">
      <p class="label">Ranking</p>
      <h2>Top 100</h2>
      <p>QS World University Rankings</p>
      <a href="#">Ranking details ↓</a>
    </div>

    <div class="banner-card">
      <p class="label">Campus</p>
      <h3>Dubai</h3>
    </div>

    <div class="banner-card">
      <p class="label">Start date</p>
      <h3><?php echo htmlspecialchars($program['intake']); ?></h3>
      <p>2025</p>
    </div>

    <div class="banner-card">
      <p class="label">Award</p>
      <h3><?php echo htmlspecialchars($program['type']); ?> <?php echo htmlspecialchars($program['award']); ?></h3>
    </div>

    <div class="banner-card">
      <p class="label">Duration</p>
      <h3><?php echo htmlspecialchars($program['duration_years']); ?> year(s)</h3>
      <p>2025 – 2028</p>
    </div>

    <div class="banner-card">
      <p class="label">Entry Requirements</p>
      <h3>3 A Levels</h3>
      <p>Typical offer: A*AA – ABB. Maths A required.</p>
      <a href="#">Full requirements ↓</a>
    </div>

    <div class="banner-card">
      <p class="label">Fees</p>
      <h3><?php echo htmlspecialchars($program['fees']); ?></h3>
      <p>September 2025 (full-time)</p>
      <a href="#">Fee details ↓</a>
    </div>
  </div>

  <div class="banner-actions">
    <a href="apply.php" class="apply-button">Apply now ↗</a>
    <a href="apply.php" class="open-day-button">Join our next Open Day: 19 April</a>
  </div>
</section>

<!-- <div class="side-nav">
  <ul>
    <li><a href="#course-description">Course Description</a></li>
    <li><a href="#modules">What You Will Study</a></li>
    <li><a href="#entry-requirements">Entry Requirements</a></li>
    <li><a href="#teaching-assessment">Teaching & Assessment</a></li>
  </ul>
</div> -->

<section id= "course-description" class="course-description">
    <h2>Course Description</h2>
    <br>
    <p><strong>Program Leader:</strong>
                    <?php if ($program['leader_name']): ?>
                        <?= htmlspecialchars($program['leader_name']) ?>
                    <?php else: ?>
                        No program leader assigned.
                    <?php endif; ?>
    </p>
    <p><?php if (!empty($program['course_description'])): ?>
        <p><?php echo nl2br(htmlspecialchars($program['course_description'])); ?></p>
    <?php else: ?>
        <p><em>No course description available.</em></p>
    <?php endif; ?></p>
</section>

<section id= "modules" class="modules">
    <h2>What you will study</h2>
    <div class="year-selection">
        <button onclick="showYear('level4')">Level 4</button>
        <button onclick="showYear('level5')">Level 5</button>
        <button onclick="showYear('level6')">Level 6</button>
    </div>

    <?php
    $levels = [
        '1' => 'level4',
        '2' => 'level5',
        '3' => 'level6'
    ];

    foreach ($levels as $year => $levelId):
        $levelTitle = "Level " . ($year + 3);
        $moduleList = $grouped_modules[$year];
    ?>
        <div id="<?php echo $levelId; ?>" class="year" style="display: <?php echo $levelId === 'level4' ? 'block' : 'none'; ?>;">
            <h3><?php echo $levelTitle; ?> Modules</h3>
            <ul>
                <?php if (count($moduleList) === 0): ?>
                    <li>No modules for this level.</li>
                <?php else: ?>
                    <?php foreach ($moduleList as $index => $mod): ?>
                    <?php $moduleId = "module" . $year . "_" . $index; ?>
                    <li onclick="toggleModuleInfo('<?php echo $moduleId; ?>')">
                    <b><?php echo htmlspecialchars($mod['block']); ?></b> : <?php echo htmlspecialchars($mod['module_name']); ?>
                        <div id="<?php echo $moduleId; ?>" style="display: none;">
                        Module Leader: <?php echo htmlspecialchars($mod['teacher_name']); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>

            <h3>Staff Contacts</h3>
            <?php
            $unique_teachers = [];
            foreach ($moduleList as $mod) {
                if (!empty($mod['teacher_name']) && !isset($unique_teachers[$mod['teacher_name']])) {
                    $unique_teachers[$mod['teacher_name']] = $mod;
                }
                
            }
            
            foreach ($unique_teachers as $teacher):
                $imageName = $teacher['staff_icon'] ?? 'default_icon.png';
            ?>
                <?php
                $imageSrc = !empty($teacher['teacher_icon']) ? $teacher['teacher_icon'] : 'staff_icons/default_icon.png';
                ?>
                <div class="staff-member">
                <img src="<?= htmlspecialchars($imageSrc) ?>"
                    alt="<?= htmlspecialchars($teacher['teacher_name']) ?>"
                    class="staff-image"
                    onerror="this.onerror=null; this.src='staff_icons/default_icon.png';">
                <div class="staff-info">
                    <h5><?= htmlspecialchars($teacher['teacher_name']) ?></h5>
                    <p>Module Leader</p>
                    <p>Email: <?= htmlspecialchars($teacher['teacher_email']) ?></p>
                    <p>Office: <?= htmlspecialchars($teacher['teacher_office']) ?></p>
                </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</section>

<section id="entry-requirements" class="entry-requirements">
    <h2>Entry Requirements</h2>
    <div>
    <h3>Entry Criteria</h3>
    <?php
        $course_type = strtolower($program['type']);

        if ($course_type === 'foundation') {
            echo '
            <ul>
                <li><strong>British GCE/IGCSE:</strong> CCCCC</li>
                <li><strong>Indian CBSE/HSSC or equivalent:</strong> Year 11: 60%, Year 12: 45%</li>
                <li><strong>UAE Tawjihiyya:</strong> 70%</li>
                <li><strong>US American High School Diploma:</strong> 70% (without SAT)</li>
                <li><strong>Africa WASSC (WAEC/NECO):</strong> 5 "C6" grades</li>
                <li><strong>Philippine High School:</strong> 70%</li>
                <li><strong>Russian/Kazakhstan National Curriculum:</strong> Attestat with minimum grades of 3</li>
                <li><strong>International Baccalaureate – MYP Year 11:</strong> 28 points, minimum grade 3 in each component</li>
            </ul>';
        } elseif ($course_type === 'undergraduate') {
            echo '
            <p><strong>A typical offer:</strong> 112 UCAS points from at least two A-levels or equivalent or BTEC National Diploma/Extended Diploma at DMM</p>

            <ul>
                <li>Five GCSEs at grade 4 or above, including English and Mathematics or equivalent</li>
                <li><strong>Access to HE:</strong> QAA-accredited Pass (English & Maths GCSEs required separately; equivalency not accepted)</li>
                <li>Applicants should normally have had a break from full-time education before taking Access course</li>
                <li><strong>International Baccalaureate:</strong> 26+ points</li>
                <li><strong>T Levels:</strong> Merit</li>
            </ul>';
        } elseif ($course_type === 'postgraduate') {
            echo '
            <ul>
                <li><strong>Academic Requirement:</strong> Undergraduate degree in a relevant subject with a minimum 2:2 or equivalent overseas qualification</li>
                <li><strong>Professional Qualifications:</strong> Considered on an individual basis if deemed equivalent</li>
                <li><strong>Work Experience:</strong> Not required</li>
            </ul>';
        } else {
            echo "<p><em>Entry requirements information is not available for this course type.</em></p>";
        }
        ?>
    </div>

    <div>
    <h3>English Language</h3>
    <?php
        $course_type = strtolower($program['type']);

        if ($course_type === 'foundation') {
            echo '
            <p><strong>Accepted Qualifications:</strong></p>
            <ul>
                <li>IELTS Academic: 5.5 (minimum 5.0 in each band)</li>
                <li>TOEFL Internet-based: 65</li>
                <li>Pearson PTE Academic: 51</li>
            </ul>

            <p><strong>Additional Qualifications Considered:</strong></p>
            <ul>
                <li>GCSE/IGCSE/O-Level English (First or Second Language): Grade C or higher</li>
                <li>CBSE/All Indian State Boards: Minimum 45% in English</li>
                <li>International Baccalaureate: Grade 4 in English A1 (SL/HL) or Grade 5 in English B (HL)</li>
                <li>WAEC/WASSCE/SSSCE: Minimum grade C6</li>
            </ul>';
        } elseif ($course_type === 'undergraduate' || $course_type === 'postgraduate') {
            echo '
            <p><strong>Accepted Qualifications:</strong></p>
            <ul>
                <li>IELTS Academic: 6.0 (minimum 5.5 in each band)</li>
                <li>TOEFL Internet-based: 72 (17 in Listening & Writing, 20 in Speaking, 18 in Reading)</li>
                <li>Pearson PTE Academic: 51</li>
            </ul>

            <p><strong>Additional Qualifications Considered:</strong></p>
            <ul>
                <li>GCSE/IGCSE/O-Level English (First or Second Language): Grade C or higher</li>
                <li>CBSE/ISC/NIOS/All State Boards: Minimum 55% in English</li>
                <li>International Baccalaureate: Grade 5 in English A1 (SL/HL) or Grade 5 in English B (HL)</li>
                <li>WAEC/WASSCE/SSSCE: Minimum grade C6</li>
            </ul>';
        } else {
            echo "<p><em>English language requirements are not available for this course type.</em></p>";
        }
    ?>
</div>
</section>

<div id= "teaching-assessment" class="teaching-assessment">
    <h2>Teaching and Assessment</h2>
    <p>On this course, you will benefit from <strong>Education 2030</strong> – DMU’s new way of delivering courses. Through block teaching, you will focus on one subject at a time instead of several at once.</p>

    <p>The teaching methods delivered on this course include staff-directed learning via lectures, tutorials and laboratories, in addition to student-centred resource-based learning (including web-based resources), collaborative and group working, individual learning, and student-centred learning via individual research/literature review.</p>

    <p>Students will be assessed via a range of methods including time-constrained phase tests, portfolios of work, programming and other laboratory exercises, examinations, project work, individual work, and group work.</p>
</div>


<div class="action-cards">
    <div class="action-card">
        <img src="images/apply.jpg" alt="Apply Now">
        <h3>Apply Now</h3>
        <p>Take the next step in your journey by submitting your application today.</p>
        <a href="apply.php" class="card-btn">Apply Now</a>
    </div>
    <div class="action-card">
        <img src="images/open-day.jpg" alt="Open Day">
        <h3>Join an Open Day</h3>
        <p>Explore the campus, meet staff and students, and see if it's the right fit for you.</p>
        <a href="apply.php" class="card-btn">Discover More</a>
    </div>
</div>
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