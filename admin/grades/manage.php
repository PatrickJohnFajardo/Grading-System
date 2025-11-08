<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$students = query("SELECT id, student_id, first_name, last_name FROM students ORDER BY last_name, first_name");
$subjects = query("SELECT id, code, title FROM subjects ORDER BY code");

$selectedStudent = null;
$studentGrades = [];

if (isset($_GET['student_id'])) {
    $studentId = (int)$_GET['student_id'];
    $selectedStudent = queryOne("SELECT * FROM students WHERE id = ?", [$studentId]);
    
    if ($selectedStudent) {
        // Get all subjects with grades (if exists)
        $studentGrades = query("
            SELECT s.id, s.code, s.title, s.units,
                   g.id as grade_id, g.prelim, g.midterm, g.final, g.final_grade, g.letter_grade, g.remarks
            FROM subjects s
            LEFT JOIN grades g ON s.id = g.subject_id AND g.student_id = ?
            ORDER BY s.code
        ", [$studentId]);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grades'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid security token.";
    } else {
        $studentId = (int)$_POST['student_id'];
        $grades = $_POST['grades'] ?? [];
        
        $pdo = getPDO();
        $pdo->beginTransaction();
        
        try {
            foreach ($grades as $subjectId => $gradeData) {
                $prelim = floatval($gradeData['prelim'] ?? 0);
                $midterm = floatval($gradeData['midterm'] ?? 0);
                $final = floatval($gradeData['final'] ?? 0);
                
                // Skip if all grades are 0
                if ($prelim == 0 && $midterm == 0 && $final == 0) {
                    continue;
                }
                
                // Calculate final grade
                $result = calculateGrade($prelim, $midterm, $final);
                
                if (isset($result['error'])) {
                    throw new Exception($result['error']);
                }
                
                // Check if grade exists
                $existing = queryOne("SELECT id FROM grades WHERE student_id = ? AND subject_id = ?", 
                                    [$studentId, $subjectId]);
                
                if ($existing) {
                    // Update existing grade
                    execute("UPDATE grades SET prelim = ?, midterm = ?, final = ?, 
                            final_grade = ?, letter_grade = ?, remarks = ?, updated_at = NOW()
                            WHERE student_id = ? AND subject_id = ?",
                            [$prelim, $midterm, $final, $result['final'], $result['letter'], 
                             $result['remarks'], $studentId, $subjectId]);
                } else {
                    // Insert new grade
                    execute("INSERT INTO grades (student_id, subject_id, prelim, midterm, final, 
                            final_grade, letter_grade, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                            [$studentId, $subjectId, $prelim, $midterm, $final, 
                             $result['final'], $result['letter'], $result['remarks']]);
                }
            }
            
            $pdo->commit();
            logAuditAction($_SESSION['user_id'], 'UPDATE', 'grades', $studentId);
            $_SESSION['success'] = "Grades saved successfully!";
            header("Location: manage.php?student_id=" . $studentId);
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error saving grades: " . $e->getMessage();
        }
    }
}

$pageTitle = "Manage Grades";
include '../../includes/admin_header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h4>Enter/Edit Student Grades</h4>
        <p class="text-muted">Select a student to manage their grades</p>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-10">
                    <label for="student_id" class="form-label">Select Student</label>
                    <select class="form-select" id="student_id" name="student_id" required>
                        <option value="">Choose a student...</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>" 
                                    <?php echo ($selectedStudent && $selectedStudent['id'] == $student['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['last_name'] . ', ' . $student['first_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Load Grades</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedStudent && !empty($studentGrades)): ?>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person"></i> 
                <?php echo htmlspecialchars($selectedStudent['student_id'] . ' - ' . 
                           $selectedStudent['first_name'] . ' ' . $selectedStudent['last_name']); ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="student_id" value="<?php echo $selectedStudent['id']; ?>">
                <input type="hidden" name="save_grades" value="1">
                
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Title</th>
                                <th>Prelim (30%)</th>
                                <th>Midterm (30%)</th>
                                <th>Final (40%)</th>
                                <th>Final Grade</th>
                                <th>Letter</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentGrades as $subject): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($subject['code']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($subject['title']); ?></td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" 
                                               class="form-control form-control-sm grade-input" 
                                               name="grades[<?php echo $subject['id']; ?>][prelim]" 
                                               value="<?php echo $subject['prelim'] ?? ''; ?>"
                                               data-row="<?php echo $subject['id']; ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" 
                                               class="form-control form-control-sm grade-input" 
                                               name="grades[<?php echo $subject['id']; ?>][midterm]" 
                                               value="<?php echo $subject['midterm'] ?? ''; ?>"
                                               data-row="<?php echo $subject['id']; ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" 
                                               class="form-control form-control-sm grade-input" 
                                               name="grades[<?php echo $subject['id']; ?>][final]" 
                                               value="<?php echo $subject['final'] ?? ''; ?>"
                                               data-row="<?php echo $subject['id']; ?>">
                                    </td>
                                    <td>
                                        <span class="final-grade-display" data-row="<?php echo $subject['id']; ?>">
                                            <?php echo $subject['final_grade'] ? number_format($subject['final_grade'], 2) : '-'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="letter-grade-display" data-row="<?php echo $subject['id']; ?>">
                                            <?php if ($subject['letter_grade']): ?>
                                                <span class="badge bg-<?php 
                                                    echo $subject['letter_grade'] == 'A' ? 'success' : 
                                                         ($subject['letter_grade'] == 'B' ? 'primary' : 
                                                         ($subject['letter_grade'] == 'C' ? 'info' : 
                                                         ($subject['letter_grade'] == 'D' ? 'warning' : 'danger'))); 
                                                ?>">
                                                    <?php echo $subject['letter_grade']; ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="remarks-display" data-row="<?php echo $subject['id']; ?>">
                                            <?php if ($subject['remarks']): ?>
                                                <span class="badge bg-<?php echo $subject['remarks'] == 'Passed' ? 'success' : 'danger'; ?>">
                                                    <?php echo $subject['remarks']; ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between mt-3">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save All Grades
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php elseif ($selectedStudent): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> No subjects available. Please add subjects first.
    </div>
<?php endif; ?>

<script>
// Auto-calculate grades when input changes
document.querySelectorAll('.grade-input').forEach(input => {
    input.addEventListener('input', function() {
        const row = this.dataset.row;
        const inputs = document.querySelectorAll(`input[data-row="${row}"]`);
        
        const prelim = parseFloat(inputs[0].value) || 0;
        const midterm = parseFloat(inputs[1].value) || 0;
        const final = parseFloat(inputs[2].value) || 0;
        
        if (prelim || midterm || final) {
            // Calculate final grade
            const finalGrade = (prelim * 0.30) + (midterm * 0.30) + (final * 0.40);
            
            // Determine letter grade
            let letter = 'F';
            let badgeClass = 'danger';
            if (finalGrade >= 90) {
                letter = 'A';
                badgeClass = 'success';
            } else if (finalGrade >= 80) {
                letter = 'B';
                badgeClass = 'primary';
            } else if (finalGrade >= 70) {
                letter = 'C';
                badgeClass = 'info';
            } else if (finalGrade >= 60) {
                letter = 'D';
                badgeClass = 'warning';
            }
            
            // Determine remarks
            const remarks = finalGrade >= 60 ? 'Passed' : 'Failed';
            const remarksClass = finalGrade >= 60 ? 'success' : 'danger';
            
            // Update display
            document.querySelector(`.final-grade-display[data-row="${row}"]`).textContent = finalGrade.toFixed(2);
            document.querySelector(`.letter-grade-display[data-row="${row}"]`).innerHTML = 
                `<span class="badge bg-${badgeClass}">${letter}</span>`;
            document.querySelector(`.remarks-display[data-row="${row}"]`).innerHTML = 
                `<span class="badge bg-${remarksClass}">${remarks}</span>`;
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>