<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS, PHP) -->

<?php
session_start();
require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM staff WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['staff_logged_in'] = true;
        $_SESSION['staff_id'] = $staff['staff_id'];
        header("Location: staff_dashboard.php");
        exit();
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <style>
       body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-image: linear-gradient(rgba(0, 0, 0, 0.76), rgba(4, 9, 30, 0.55)), url('images/banner main.jpeg');
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .login-container {
        background-color: #fff;
        padding: 2.5rem 3rem;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 400px;
    }

    .login-container h1 {
        margin-bottom: 1.5rem;
        color: #1c1f2b;
        text-align: center;
    }

    .login-container label {
        display: block;
        margin-top: 1rem;
        margin-bottom: 0.25rem;
        color: #333;
        font-weight: 500;
    }

    .login-container input {
        width: 93.75%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .login-container input:focus {
        border-color:rgb(0, 0, 0);
        outline: none;
    }

    .login-container button {
        margin-top: 1.5rem;
        width: 100%;
        padding: 0.8rem 1.4rem;
        background-color:rgb(120, 7, 7);
        color: white;
        font-size: 1rem;
        border: 2px solid white;
        border-radius: 25px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .login-container button:hover {
        background-color:rgb(0, 0, 0);
    }
    </style>
</head>
<body>
    <section class="login-container">
        <h1>Staff Login</h1>
        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <form action="" method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
            <p style="text-align:center; margin-top: 1rem;">
            <a href="admin_login.php" style="color:#000; text-decoration: underline; font-weight: 500;">Admin? Log in here.</a>
            </p>
        </form>
    </section>
</body>
</html>