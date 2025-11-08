<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: index.php");
    exit();
}

// Verify CSRF token
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Invalid security token.";
    header("Location: index.php");
    exit();
}

$id = (int)($_POST['id'] ?? 0);

// Check if student exists
$student = queryOne("SELECT * FROM students WHERE id = ?", [$id]);

if (!$student) {
    $_SESSION['error'] = "Student not found.";
    header("Location: index.php");
    exit();
}

// Delete student (CASCADE will delete associated grades)
if (execute("DELETE FROM students WHERE id = ?", [$id])) {
    logAuditAction($_SESSION['user_id'], 'DELETE', 'students', $id);
    $_SESSION['success'] = "Student deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete student.";
}

header("Location: index.php");
exit();
?>