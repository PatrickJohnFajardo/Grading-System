<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/custom.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-color-dark) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 193, 7, 0.25); /* yellow tint */
            color: #fff;
        }
        .content-wrapper {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar px-0">
                <div class="p-4 text-white text-center border-bottom border-white border-opacity-25">
                    <h4><i class="bi bi-mortarboard-fill"></i> <?php echo APP_NAME; ?></h4>
                    <small>Admin Panel</small>
                </div>
                
                <nav class="nav flex-column p-3">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['PHP_SELF'], 'admin/index') !== false ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>admin/index.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'students') !== false ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>admin/students/index.php">
                        <i class="bi bi-people"></i> Manage Students
                    </a>
                    
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'subjects') !== false ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>admin/subjects/index.php">
                        <i class="bi bi-book"></i> Manage Subjects
                    </a>
                    
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'grades') !== false ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>admin/grades/index.php">
                        <i class="bi bi-file-earmark-text"></i> Manage Grades
                    </a>
                    
                    <hr class="border-white border-opacity-25">
                    
                    <a class="nav-link" href="<?php echo SITE_URL; ?>admin/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
                
                <div class="p-3 text-white mt-auto" style="position: absolute; bottom: 20px; width: 100%;">
                    <small>
                        <i class="bi bi-person-circle"></i> 
                        <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </small>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><?php echo $pageTitle ?? 'Admin Panel'; ?></h3>
                    <div>
                        <span class="text-muted">
                            <i class="bi bi-calendar"></i> <?php echo date('F d, Y'); ?>
                        </span>
                    </div>
                </div>