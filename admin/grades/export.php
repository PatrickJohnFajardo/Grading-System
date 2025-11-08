<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($studentId > 0) {
    // Get student data
    $student = queryOne("SELECT * FROM students WHERE id = ?", [$studentId]);
    
    if (!$student) {
        die("Student not found.");
    }
    
    // Get student grades
    $grades = query("
        SELECT g.*, s.code, s.title, s.units
        FROM grades g
        JOIN subjects s ON g.subject_id = s.id
        WHERE g.student_id = ?
        ORDER BY s.code
    ", [$studentId]);
    
    // Calculate GPA
    $totalPoints = 0;
    $totalUnits = 0;
    foreach ($grades as $grade) {
        $totalPoints += $grade['final_grade'];
        $totalUnits++;
    }
    $gpa = $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;
    
    // Generate HTML report
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Grade Report - <?php echo htmlspecialchars($student['student_id']); ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            @media print {
                .no-print { display: none; }
            }
            body { background: white; }
            .report-header {
                text-align: center;
                border-bottom: 3px solid #333;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="container my-5">
            <div class="no-print mb-3">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Report
                </button>
                <a href="../students/view.php?id=<?php echo $studentId; ?>" class="btn btn-secondary">
                    Back to Student
                </a>
            </div>
            
            <div class="report-header">
                <h2><?php echo APP_NAME; ?></h2>
                <h4>Student Grade Report</h4>
                <p>Generated on: <?php echo date('F d, Y'); ?></p>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Student ID:</th>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . 
                                ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . 
                                $student['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Year Level:</th>
                            <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Overall GPA:</th>
                            <td><strong><?php echo number_format($gpa, 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h5 class="mb-3">Academic Records</h5>
            
            <?php if (empty($grades)): ?>
                <p class="text-muted">No grades recorded.</p>
            <?php else: ?>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Subject Title</th>
                            <th>Units</th>
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
                                <td><?php echo htmlspecialchars($grade['code']); ?></td>
                                <td><?php echo htmlspecialchars($grade['title']); ?></td>
                                <td><?php echo htmlspecialchars($grade['units']); ?></td>
                                <td><?php echo number_format($grade['prelim'], 2); ?></td>
                                <td><?php echo number_format($grade['midterm'], 2); ?></td>
                                <td><?php echo number_format($grade['final'], 2); ?></td>
                                <td><?php echo number_format($grade['final_grade'], 2); ?></td>
                                <td><?php echo htmlspecialchars($grade['letter_grade']); ?></td>
                                <td><?php echo htmlspecialchars($grade['remarks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <div class="mt-5 pt-3 border-top">
                <p class="text-muted small">
                    This is a computer-generated report. No signature is required.<br>
                    Grade Scale: A (90-100), B (80-89), C (70-79), D (60-69), F (0-59)<br>
                    Computation: Final Grade = (Prelim × 30%) + (Midterm × 30%) + (Final × 40%)
                </p>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// If no student selected, show selection page
$students = query("SELECT * FROM students ORDER BY last_name, first_name");

$pageTitle = "Export Reports";
include '../../includes/admin_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Export Student Report</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Select a student to generate their grade report</p>
                
                <form method="GET">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Choose a student...</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['student_id'] . ' - ' . 
                                               $student['last_name'] . ', ' . $student['first_name']); ?>
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
                            <i class="bi bi-file-earmark-pdf"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>