<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$id = (int)($_GET['id'] ?? 0);
$errors = [];

// Get student data
$student = queryOne("SELECT * FROM students WHERE id = ?", [$id]);

if (!$student) {
    $_SESSION['error'] = "Student not found.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        // Sanitize inputs
        $formData = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'middle_name' => sanitize($_POST['middle_name'] ?? ''),
            'course' => sanitize($_POST['course'] ?? ''),
            'year_level' => (int)($_POST['year_level'] ?? 0),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];
        
        // Validation
        if (empty($formData['first_name'])) {
            $errors[] = "First name is required.";
        }
        if (empty($formData['last_name'])) {
            $errors[] = "Last name is required.";
        }
        if (empty($formData['course'])) {
            $errors[] = "Course is required.";
        }
        if ($formData['year_level'] < 1 || $formData['year_level'] > 6) {
            $errors[] = "Year level must be between 1 and 6.";
        }
        if (!empty($formData['email']) && !validateEmail($formData['email'])) {
            $errors[] = "Invalid email format.";
        }
        if (!empty($formData['password']) && strlen($formData['password'])
        < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }
        
        // Update if no errors
        if (empty($errors)) {
            $sql = "UPDATE students SET first_name = ?, last_name = ?, middle_name = ?, course = ?, 
                    year_level = ?, email = ?";
            $params = [
                $formData['first_name'],
                $formData['last_name'],
                $formData['middle_name'],
                $formData['course'],
                $formData['year_level'],
                $formData['email']
            ];
            
            // Update password if provided
            if (!empty($formData['password'])) {
                $sql .= ", password_hash = ?";
                $params[] = password_hash($formData['password'], PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            if (execute($sql, $params)) {
                logAuditAction($_SESSION['user_id'], 'UPDATE', 'students', $id);
                
                $_SESSION['success'] = "Student updated successfully!";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Failed to update student. Please try again.";
            }
        }
    }
} else {
    $formData = $student;
}

$pageTitle = "Edit Student";
include '../../includes/admin_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Student</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">Student ID</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['student_id']); ?>" disabled>
                            <small class="form-text text-muted">Student ID cannot be changed</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                   value="<?php echo htmlspecialchars($formData['middle_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="course" class="form-label">Course <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="course" name="course" 
                                   value="<?php echo htmlspecialchars($formData['course'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="year_level" class="form-label">Year Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($formData['year_level'] ?? 0) == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password. Minimum 6 characters if changing.</small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Student
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>