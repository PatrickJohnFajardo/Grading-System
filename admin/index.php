<?php
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../config/db.php';

requireAdmin();
checkSessionTimeout();

$pageTitle = 'Dashboard';
include '../includes/admin_header.php';
?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Students</h5>
                        <p class="text-muted mb-2">Manage student records</p>
                        <a class="btn btn-primary btn-sm" href="students/index.php">
                            Go to Students
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-book text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Subjects</h5>
                        <p class="text-muted mb-2">Create and edit subjects</p>
                        <a class="btn btn-success btn-sm" href="subjects/index.php">
                            Go to Subjects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-file-earmark-text text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Grades</h5>
                        <p class="text-muted mb-2">Enter, import, export grades</p>
                        <a class="btn btn-warning btn-sm" href="grades/index.php">
                            Go to Grades
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>