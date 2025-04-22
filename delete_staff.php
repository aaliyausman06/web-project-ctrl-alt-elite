<!-- author@Aaliya Mohamad Usman P2840499 (HTML, CSS)-->

<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db_connect.php';

$staff_id = intval($_POST['id'] ?? 0);

if ($staff_id === 0) {
    die("Staff ID not provided.");
}

$conn->prepare("UPDATE programs SET program_leader_id = NULL WHERE program_leader_id = ?")->execute([$staff_id]);
$conn->prepare("UPDATE modules SET module_leader_id = NULL WHERE module_leader_id = ?")->execute([$staff_id]);

$stmt = $conn->prepare("DELETE FROM staff WHERE staff_id = ?");
if ($stmt->execute([$staff_id])) {
    header("Location: admin_dashboard.php?section=staff&message=Staff+deleted+successfully");
    exit();
} else {
    echo "Error deleting staff member.";
}
?>
