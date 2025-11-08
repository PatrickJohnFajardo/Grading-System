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

// Check if subject exists
$subject = queryOne("SELECT * FROM subjects WHERE id = ?", [$id]);

if (!$subject) {
    $_SESSION['error'] = "Subject not found.";
    header("Location: index.php");
    exit();
}

// Delete subject (CASCADE will delete associated grades)
if (execute("DELETE FROM subjects WHERE id = ?", [$id])) {
    logAuditAction($_SESSION['user_id'], 'DELETE', 'subjects', $id);
    $_SESSION['success'] = "Subject deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete subject.";
}

header("Location: index.php");
exit();
?>