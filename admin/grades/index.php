<?php
require_once '../../config/config.php';
require_once '../../config/auth.php';
require_once '../../config/db.php';

requireAdmin();
checkSessionTimeout();

$pageTitle = "Manage Grades";
include '../../includes/admin_header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <h4>Grade Management</h4>
        <p class="text-muted">Select an action below to manage student grades</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-pencil-square text-warning" style="font-size: 3rem;"></i>
                </div>
                <h5>Enter/Edit Grades</h5>
                <p class="text-muted">Manually enter or update student grades for subjects</p>
                <a href="manage.php" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Manage Grades
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-upload text-info" style="font-size: 3rem;"></i>
                </div>
                <h5>Import Grades</h5>
                <p class="text-muted">Bulk upload grades from CSV file</p>
                <a href="import.php" class="btn btn-info">
                    <i class="bi bi-upload"></i> Import CSV
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="bi bi-file-earmark-pdf text-success" style="font-size: 3rem;"></i>
                </div>
                <h5>Export Records</h5>
                <p class="text-muted">Generate student grade reports</p>
                <a href="export.php" class="btn btn-success">
                    <i class="bi bi-download"></i> Export Reports
                </a>
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
                        <h6>Letter Grades:</h6>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Range</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">A</span></td>
                                    <td>90.00 - 100.00</td>
                                    <td>Excellent</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">B</span></td>
                                    <td>80.00 - 89.99</td>
                                    <td>Very Good</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">C</span></td>
                                    <td>70.00 - 79.99</td>
                                    <td>Good</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">D</span></td>
                                    <td>60.00 - 69.99</td>
                                    <td>Fair</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">F</span></td>
                                    <td>0.00 - 59.99</td>
                                    <td>Failed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Grade Computation:</h6>
                        <ul>
                            <li><strong>Prelim:</strong> 30%</li>
                            <li><strong>Midterm:</strong> 30%</li>
                            <li><strong>Final:</strong> 40%</li>
                        </ul>
                        <p class="mt-3">
                            <strong>Formula:</strong><br>
                            <code>Final Grade = (Prelim × 0.30) + (Midterm × 0.30) + (Final × 0.40)</code>
                        </p>
                        <p class="mt-3">
                            <strong>Passing Grade:</strong> 60.00
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>