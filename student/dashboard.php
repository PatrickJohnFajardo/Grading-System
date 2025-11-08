<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../config/db.php';

requireStudent();
checkSessionTimeout();

$studentId = $_SESSION['user_id'];

// Get student data
$student = queryOne("SELECT * FROM students WHERE id = ?", [$studentId]);

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
$passedSubjects = 0;
$failedSubjects = 0;

foreach ($grades as $grade) {
    $totalPoints += $grade['final_grade'];
    $totalUnits++;
    if ($grade['remarks'] == 'Passed') {
        $passedSubjects++;
    } else {
        $failedSubjects++;
    }
}

$gpa = $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;

// Determine GPA letter grade
$gpaLetter = 'F';
if ($gpa >= 90) $gpaLetter = 'A';
elseif ($gpa >= 80) $gpaLetter = 'B';
elseif ($gpa >= 70) $gpaLetter = 'C';
elseif ($gpa >= 60) $gpaLetter = 'D';

$pageTitle = "Dashboard";
include '../includes/student_header.php';
?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?php echo SITE_URL; ?>student/logout.php" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3>Welcome, <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>!</h3>
                        <p class="mb-0">Student ID: <strong><?php echo htmlspecialchars($student['student_id']); ?></strong></p>
                        <p class="mb-0"><?php echo htmlspecialchars($student['course']); ?> - Year <?php echo $student['year_level']; ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h1 class="display-3 mb-0"><?php echo number_format($gpa, 2); ?></h1>
                        <h5>Overall GPA <span class="badge bg-light text-primary"><?php echo $gpaLetter; ?></span></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                <h2 class="mt-2"><?php echo $passedSubjects; ?></h2>
                <p class="text-muted mb-0">Subjects Passed</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="bi bi-x-circle-fill text-danger" style="font-size: 2rem;"></i>
                <h2 class="mt-2"><?php echo $failedSubjects; ?></h2>
                <p class="text-muted mb-0">Subjects Failed</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bi bi-book-fill text-info" style="font-size: 2rem;"></i>
                <h2 class="mt-2"><?php echo $totalUnits; ?></h2>
                <p class="text-muted mb-0">Total Subjects</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-card-list"></i> My Grades</h5>
            </div>
            <div class="card-body">
                <?php if (empty($grades)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No grades available yet. Please check back later.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Title</th>
                                    <th>Prelim</th>
                                    <th>Midterm</th>
                                    <th>Final</th>
                                    <th>Final Grade</th>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Grade Scale Reference</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Range</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">A</span></td>
                                    <td>90 - 100</td>
                                    <td>Excellent</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">B</span></td>
                                    <td>80 - 89</td>
                                    <td>Very Good</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">C</span></td>
                                    <td>70 - 79</td>
                                    <td>Good</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">D</span></td>
                                    <td>60 - 69</td>
                                    <td>Fair</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">F</span></td>
                                    <td>0 - 59</td>
                                    <td>Failed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Grade Computation:</h6>
                        <p>Final Grade = (Prelim × 30%) + (Midterm × 30%) + (Final × 40%)</p>
                        <p class="mt-3"><strong>Passing Grade:</strong> 60.00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>