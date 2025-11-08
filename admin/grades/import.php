<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$errors = [];
$importResults = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token.";
    } elseif (!isset($_FILES['csv_file'])) {
        $errors[] = "No file uploaded.";
    } else {
        $file = $_FILES['csv_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error.";
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = "File size exceeds maximum limit of 2MB.";
        } elseif (!in_array($file['type'], ALLOWED_FILE_TYPES) && 
                  !in_array(mime_content_type($file['tmp_name']), ALLOWED_FILE_TYPES)) {
            $errors[] = "Invalid file type. Only CSV files are allowed.";
        } else {
            // Process CSV
            $handle = fopen($file['tmp_name'], 'r');
            
            if ($handle === false) {
                $errors[] = "Failed to open file.";
            } else {
                $pdo = getPDO();
                $pdo->beginTransaction();
                
                try {
                    $header = fgetcsv($handle);
                    
                    // Validate header
                    $expectedHeaders = ['student_id', 'subject_code', 'prelim', 'midterm', 'final'];
                    if ($header !== $expectedHeaders) {
                        throw new Exception("Invalid CSV format. Expected headers: " . implode(', ', $expectedHeaders));
                    }
                    
                    $rowNumber = 1;
                    $processed = 0;
                    $updated = 0;
                    $errorsList = [];
                    
                    while (($data = fgetcsv($handle)) !== false) {
                        $rowNumber++;
                        
                        if (count($data) !== 5) {
                            $errorsList[] = "Row $rowNumber: Invalid number of columns";
                            continue;
                        }
                        
                        list($studentId, $subjectCode, $prelim, $midterm, $final) = $data;
                        
                        // Find student
                        $student = queryOne("SELECT id FROM students WHERE student_id = ?", [$studentId]);
                        if (!$student) {
                            $errorsList[] = "Row $rowNumber: Student ID '$studentId' not found";
                            continue;
                        }
                        
                        // Find subject
                        $subject = queryOne("SELECT id FROM subjects WHERE code = ?", [$subjectCode]);
                        if (!$subject) {
                            $errorsList[] = "Row $rowNumber: Subject code '$subjectCode' not found";
                            continue;
                        }
                        
                        // Validate grades
                        $prelim = floatval($prelim);
                        $midterm = floatval($midterm);
                        $final = floatval($final);
                        
                        if ($prelim < 0 || $prelim > 100 || $midterm < 0 || $midterm > 100 || 
                            $final < 0 || $final > 100) {
                            $errorsList[] = "Row $rowNumber: Grades must be between 0 and 100";
                            continue;
                        }
                        
                        // Calculate final grade
                        $result = calculateGrade($prelim, $midterm, $final);
                        
                        // Check if grade exists
                        $existing = queryOne("SELECT id FROM grades WHERE student_id = ? AND subject_id = ?",
                                            [$student['id'], $subject['id']]);
                        
                        if ($existing) {
                            // Update
                            execute("UPDATE grades SET prelim = ?, midterm = ?, final = ?, 
                                    final_grade = ?, letter_grade = ?, remarks = ?, updated_at = NOW()
                                    WHERE student_id = ? AND subject_id = ?",
                                    [$prelim, $midterm, $final, $result['final'], $result['letter'],
                                     $result['remarks'], $student['id'], $subject['id']]);
                            $updated++;
                        } else {
                            // Insert
                            execute("INSERT INTO grades (student_id, subject_id, prelim, midterm, final,
                                    final_grade, letter_grade, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                                    [$student['id'], $subject['id'], $prelim, $midterm, $final,
                                     $result['final'], $result['letter'], $result['remarks']]);
                        }
                        
                        $processed++;
                    }
                    
                    fclose($handle);
                    $pdo->commit();
                    
                    logAuditAction($_SESSION['user_id'], 'IMPORT', 'grades', 0);
                    
                    $importResults = [
                        'processed' => $processed,
                        'updated' => $updated,
                        'inserted' => $processed - $updated,
                        'errors' => $errorsList
                    ];
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    fclose($handle);
                    $errors[] = "Import failed: " . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = "Import Grades";
include '../../includes/admin_header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-upload"></i> Import Grades from CSV</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($
                errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($importResults): ?>
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle"></i> Import Completed</h5>
                    <ul>
                        <li><strong>Rows Processed:</strong> <?php echo $importResults['processed']; ?></li>
                        <li><strong>Records Updated:</strong> <?php echo $importResults['updated']; ?></li>
                        <li><strong>Records Inserted:</strong> <?php echo $importResults['inserted']; ?></li>
                    </ul>
                    
                    <?php if (!empty($importResults['errors'])): ?>
                        <hr>
                        <h6 class="text-danger">Errors:</h6>
                        <ul>
                            <?php foreach ($importResults['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle"></i> CSV Format Requirements:</h6>
                <ul class="mb-0">
                    <li>File must be in CSV format (.csv)</li>
                    <li>Maximum file size: 2MB</li>
                    <li>First row must contain headers (case-sensitive)</li>
                    <li>Required columns: <code>student_id, subject_code, prelim, midterm, final</code></li>
                    <li>Grades must be numeric values between 0 and 100</li>
                    <li>Student ID and Subject Code must exist in the database</li>
                </ul>
            </div>
            
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h6>Sample CSV Format:</h6>
                    <pre class="mb-0">student_id,subject_code,prelim,midterm,final
2024-0001,CS101,85.5,88.0,90.0
2024-0002,CS102,78.0,82.5,85.0
2024-0003,IT101,92.0,95.0,93.5</pre>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                
                <div class="mb-3">
                    <label for="csv_file" class="form-label">Select CSV File</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" 
                           accept=".csv,text/csv" required>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-upload"></i> Upload and Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<?php include '../../includes/footer.php'; ?>