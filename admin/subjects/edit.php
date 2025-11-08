<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$id = (int)($_GET['id'] ?? 0);
$errors = [];

// Get subject data
$subject = queryOne("SELECT * FROM subjects WHERE id = ?", [$id]);

if (!$subject) {
    $_SESSION['error'] = "Subject not found.";
    header("Location: index.php");
    exit();
}

// Get semesters for dropdown
$semesters = query("SELECT * FROM semesters ORDER BY start_date DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        // Sanitize inputs
        $formData = [
            'title' => sanitize($_POST['title'] ?? ''),
            'units' => (int)($_POST['units'] ?? 0),
            'semester_id' => !empty($_POST['semester_id']) ? (int)$_POST['semester_id'] : null
        ];
        
        // Validation
        if (empty($formData['title'])) {
            $errors[] = "Subject title is required.";
        }
        if ($formData['units'] < 1 || $formData['units'] > 6) {
            $errors[] = "Units must be between 1 and 6.";
        }
        
        // Update if no errors
        if (empty($errors)) {
            $sql = "UPDATE subjects SET title = ?, units = ?, semester_id = ? WHERE id = ?";
            
            if (execute($sql, [
                $formData['title'],
                $formData['units'],
                $formData['semester_id'],
                $id
            ])) {
                logAuditAction($_SESSION['user_id'], 'UPDATE', 'subjects', $id);
                
                $_SESSION['success'] = "Subject updated successfully!";
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Failed to update subject. Please try again.";
            }
        }
    }
} else {
    $formData = $subject;
}

$pageTitle = "Edit Subject";
include '../../includes/admin_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Subject</h5>
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
                        <label for="code" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($subject['code']); ?>" disabled>
                        <small class="form-text text-muted">Subject code cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Subject Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="units" class="form-label">Units <span class="text-danger">*</span></label>
                        <select class="form-select" id="units" name="units" required>
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
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>