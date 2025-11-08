<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/auth.php';

// If already logged in, send to proper area
if (isAdmin()) {
    header("Location: " . SITE_URL . "admin/index.php");
    exit();
}
if (isStudent()) {
    header("Location: " . SITE_URL . "student/dashboard.php");
    exit();
}

$adminError = '';
$studentError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        if ($role === 'admin') {
            $adminError = 'Invalid security token.';
        } else {
            $studentError = 'Invalid security token.';
        }
    } else if ($role === 'admin') {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($username) || empty($password)) {
            $adminError = 'Please enter both username and password.';
        } else {
            $admin = loginAdmin($username, $password);
            if ($admin) {
                header("Location: " . SITE_URL . "admin/index.php");
                exit();
            } else {
                $adminError = 'Invalid username or password.';
            }
        }
    } else if ($role === 'student') {
        $studentId = sanitize($_POST['student_id'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($studentId) || empty($password)) {
            $studentError = 'Please enter both Student ID and password.';
        } else {
            $student = loginStudent($studentId, $password);
            if ($student) {
                header("Location: " . SITE_URL . "student/dashboard.php");
                exit();
            } else {
                $studentError = 'Invalid Student ID or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/custom.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .nav-pills .nav-link.active {
            background-color: var(--primary-color) !important;
        }
    </style>
<?php /* Simple, self-contained page so no header/footer includes */ ?>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card login-card">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                            <h2 class="mt-2 mb-0"><?php echo APP_NAME; ?></h2>
                            <p class="text-muted">Select your login type</p>
                        </div>

                        <ul class="nav nav-pills nav-justified mb-4" id="loginTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="true">
                                    <i class="bi bi-person-badge"></i> Admin
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="student-tab" data-bs-toggle="pill" data-bs-target="#student" type="button" role="tab" aria-controls="student" aria-selected="false">
                                    <i class="bi bi-person"></i> Student
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="loginTabsContent">
                            <div class="tab-pane fade show active" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                                <?php if ($adminError): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $adminError; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="role" value="admin">
                                    <div class="mb-3">
                                        <label for="admin_username" class="form-label">Username</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" class="form-control" id="admin_username" name="username" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="admin_password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="admin_password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right"></i> Login as Admin
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="student" role="tabpanel" aria-labelledby="student-tab">
                                <?php if ($studentError): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $studentError; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                    <input type="hidden" name="role" value="student">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student ID</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" class="form-control" id="student_id" name="student_id" placeholder="e.g., 2024-0001" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="student_password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="student_password" name="password" required>
                                        </div>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-box-arrow-in-right"></i> Login as Student
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // If there was a student error, switch to student tab on load
        (function () {
            var hasStudentError = <?php echo $studentError ? 'true' : 'false'; ?>;
            if (hasStudentError) {
                var tabTrigger = document.querySelector('#student-tab');
                if (tabTrigger) new bootstrap.Tab(tabTrigger).show();
            }
        })();
    </script>
</body>
</html>

