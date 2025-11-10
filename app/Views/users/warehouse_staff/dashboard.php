<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header with Logout -->
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#">WITMS - Warehouse Staff</a>
                        <div class="d-flex align-items-center">
                            <span class="text-white me-3">
                                <i class="bi bi-person-circle"></i> 
                                <?= esc($user['full_name'] ?? 'User') ?>
                            </span>
                            <a href="<?= base_url('auth/logout') ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to logout?')">
                                <i class="bi bi-box-arrow-left me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Dashboard Content -->
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h1 class="mb-3">Warehouse Staff Dashboard</h1>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Welcome to the Warehouse Staff Dashboard. Here you can manage your warehouse operations efficiently.
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Today's Scans</h5>
                                            <h2 class="text-primary">0</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Pending Tasks</h5>
                                            <h2 class="text-warning">0</h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <h5 class="card-title">Low Stock Items</h5>
                                            <h2 class="text-danger">0</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>