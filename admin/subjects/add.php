<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$errors = [];
$formData = [];

// Get semesters for dropdown
$semesters = query("SELECT * FROM semesters ORDER BY start_date DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        // Sanitize inputs
        $formData = [
            'code' => sanitize($_POST['code'] ?? ''),
            'title' => sanitize($_POST['title'] ?? ''),
            'units' => (int)($_POST['units'] ?? 0),
            'semester_id' => !empty($_POST['semester_id']) ? (int)$_POST['semester_id'] : null
        ];
        
        // Validation
        if (empty($formData['code'])) {
            $errors[] = "Subject code is required.";
        }
        if (empty($formData['title'])) {
            $errors[] = "Subject title is required.";
        }
        if ($formData['units'] < 1 || $formData['units'] > 6) {
            $errors[] = "Units must be between 1 and 6.";
        }
        
        // Check if code already exists
        if (empty($errors)) {
            $existing = queryOne("SELECT id FROM subjects WHERE code = ?", [$formData['code']]);
            if ($existing) {
                $errors[] = "Subject code already exists.";
            }
        }
        
        // Insert if no errors
        if (empty($errors)) {
            $sql = "INSERT INTO subjects (code, title, units, semester_id) VALUES (?, ?, ?, ?)";
            
            if (execute($sql, [
                $formData['code'],
                $formData['title'],
                $formData['units'],
                $formData['semester_id']
            ])) {
                $subjectId = lastInsertId();
                logAuditAction($_SESSION['user_id'], 'CREATE', 'subjects', $subjectId);
                
                $_SESSION['success'] = "Subject added successfully!";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Failed to add subject. Please try again.";
            }
        }
    }
}

$pageTitle = "Add New Subject";
include '../../includes/admin_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Subject</h5>
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
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="code" name="code" 
                               value="<?php echo htmlspecialchars($formData['code'] ?? ''); ?>" 
                               placeholder="e.g., CS101" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Subject Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>" 
                               placeholder="e.g., Introduction to Programming" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="units" class="form-label">Units <span class="text-danger">*</span></label>
                        <select class="form-select" id="units" name="units" required>
                            <option value="">Select...</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($formData['units'] ?? 0) == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <select class="form-select" id="semester_id" name="semester_id">
                            <option value="">Select...</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo $semester['id']; ?>" 
                                        <?php echo ($formData['semester_id'] ?? 0) == $semester['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($semester['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Save Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>