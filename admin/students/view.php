<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$id = (int)($_GET['id'] ?? 0);

// Get student data
$student = queryOne("SELECT * FROM students WHERE id = ?", [$id]);

if (!$student) {
    $_SESSION['error'] = "Student not found.";
    header("Location: index.php");
    exit();
}

// Get student grades
$grades = query("
    SELECT g.*, s.code, s.title, s.units
    FROM grades g
    JOIN subjects s ON g.subject_id = s.id
    WHERE g.student_id = ?
    ORDER BY s.code
", [$id]);

// Calculate GPA
$totalPoints = 0;
$totalUnits = 0;
foreach ($grades as $grade) {
    $totalPoints += $grade['final_grade'];
    $totalUnits++;
}
$gpa = $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;

$pageTitle = "View Student";
include '../../includes/admin_header.php';
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Student Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Student ID:</th>
                        <td><strong><?php echo htmlspecialchars($student['student_id']); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Full Name:</th>
                        <td>
                            <?php 
                            echo htmlspecialchars($student['first_name'] . ' ' . 
                                 ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . 
                                 $student['last_name']); 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Course:</th>
                        <td><?php echo htmlspecialchars($student['course']); ?></td>
                    </tr>
                    <tr>
                        <th>Year Level:</th>
                        <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th>Enrolled:</th>
                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                    </tr>
                </table>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Student
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body text-center">
                <h2 class="display-4"><?php echo number_format($gpa, 2); ?></h2>
                <p class="text-muted mb-0">Overall GPA</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-card-list"></i> Academic Records</h5>
            </div>
            <div class="card-body">
                <?php if (empty($grades)): ?>
                    <p class="text-center text-muted py-4">No grades recorded yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Title</th>
                                    <th>Prelim</th>
                                    <th>Midterm</th>
                                    <th>Final</th>
                                    <th>Grade</th>
                                    <th>Letter</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grades as $grade): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($grade['code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($grade['title']); ?></td>
                                        <td><?php echo number_format($grade['prelim'], 2); ?></td>
                                        <td><?php echo number_format($grade['midterm'], 2); ?></td>
                                        <td><?php echo number_format($grade['final'], 2); ?></td>
                                        <td><strong><?php echo number_format($grade['final_grade'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $grade['letter_grade'] == 'A' ? 'success' : 
                                                     ($grade['letter_grade'] == 'B' ? 'primary' : 
                                                     ($grade['letter_grade'] == 'C' ? 'info' : 
                                                     ($grade['letter_grade'] == 'D' ? 'warning' : 'danger'))); 
                                            ?>">
                                                <?php echo htmlspecialchars($grade['letter_grade']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $grade['remarks'] == 'Passed' ? 'success' : 'danger'; ?>">
                                                <?php echo htmlspecialchars($grade['remarks']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-end mt-3">
                        <a href="../grades/export.php?student_id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>