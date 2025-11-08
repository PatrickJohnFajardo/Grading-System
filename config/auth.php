<?php
/**
 * Authentication and Authorization Handler
 */

require_once __DIR__ . '/db.php';

/**
 * Check if user is logged in as admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is logged in as student
 * @return bool
 */
function isStudent() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = "Unauthorized access. Admin login required.";
        header("Location: " . SITE_URL . "admin/login.php");
        exit();
    }
}

/**
 * Require student authentication
 */
function requireStudent() {
    if (!isStudent()) {
        $_SESSION['error'] = "Unauthorized access. Student login required.";
        header("Location: " . SITE_URL . "student/login.php");
        exit();
    }
}

/**
 * Login Admin
 * @param string $username
 * @param string $password
 * @return bool|array
 */
function loginAdmin($username, $password) {
    $pdo = getPDO();
    
    $sql = "SELECT * FROM admins WHERE username = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['username'] = $admin['username'];
        $_SESSION['full_name'] = $admin['full_name'];
        $_SESSION['last_activity'] = time();
        
        return $admin;
    }
    
    return false;
}

/**
 * Login Student
 * @param string $student_id
 * @param string $password
 * @return bool|array
 */
function loginStudent($student_id, $password) {
    $pdo = getPDO();
    
    $sql = "SELECT * FROM students WHERE student_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();
    
    if ($student && password_verify($password, $student['password_hash'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['user_role'] = 'student';
        $_SESSION['full_name'] = $student['first_name'] . ' ' . $student['last_name'];
        $_SESSION['last_activity'] = time();
        
        return $student;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Generate CSRF Token
 * @return string
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * @param string $token
 * @return bool
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check session timeout (30 minutes)
 */
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        logout();
        $_SESSION['error'] = "Session expired. Please login again.";
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Sanitize input data
 * @param mixed $data
 * @return mixed
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Calculate final grade and letter
 * @param float $prelim
 * @param float $midterm
 * @param float $final
 * @return array
 */
function calculateGrade($prelim, $midterm, $final) {
    // Validate inputs
    if (!is_numeric($prelim) || !is_numeric($midterm) || !is_numeric($final)) {
        return ['error' => 'Invalid grade values'];
    }
    
    $prelim = floatval($prelim);
    $midterm = floatval($midterm);
    $final = floatval($final);
    
    // Check range
    if ($prelim < 0 || $prelim > 100 || $midterm < 0 || $midterm > 100 || $final < 0 || $final > 100) {
        return ['error' => 'Grades must be between 0 and 100'];
    }
    
    // Calculate weighted average: Prelim 30%, Midterm 30%, Final 40%
    $finalGrade = ($prelim * 0.30) + ($midterm * 0.30) + ($final * 0.40);
    $finalGrade = round($finalGrade, 2);
    
    // Determine letter grade
    $letter = 'F';
    foreach (GRADE_SCALE as $grade => $range) {
        if ($finalGrade >= $range['min'] && $finalGrade <= $range['max']) {
            $letter = $grade;
            break;
        }
    }
    
    // Determine remarks
    $remarks = $finalGrade >= PASSING_GRADE ? 'Passed' : 'Failed';
    
    return [
        'final' => $finalGrade,
        'letter' => $letter,
        'remarks' => $remarks
    ];
}

/**
 * Log admin action to audit_logs
 * @param int $admin_id
 * @param string $action
 * @param string $target_table
 * @param int $target_id
 */
function logAuditAction($admin_id, $action, $target_table, $target_id) {
    $sql = "INSERT INTO audit_logs (admin_id, action, target_table, target_id, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    execute($sql, [$admin_id, $action, $target_table, $target_id]);
}
?>