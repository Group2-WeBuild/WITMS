<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'IT Administrator Dashboard') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Include Mobile Styles -->
    <?= view('templates/mobile_styles') ?>
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding-right: 20px;
            padding-left: 20px;
            padding-bottom: 30px;
        }
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
        }
        .stat-card .icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.2;
        }
        .alert-item {
            border-left: 4px solid;
            padding: 12px;
            margin-bottom: 10px;
            background: white;
            border-radius: 5px;
        }
        .alert-item.critical { border-left-color: #dc3545; }
        .alert-item.error { border-left-color: #fd7e14; }
        .alert-item.warning { border-left-color: #ffc107; }
        .alert-item.alert { border-left-color: #721c24; }
        .alert-item.emergency { border-left-color: #000; }
        .table-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'IT Administrator Dashboard']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <h2 class="mb-1"><i class="bi bi-speedometer2"></i> IT Administrator Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?= esc($user['first_name'] ?? 'Administrator') ?>! Here's an overview of your system.</p>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary position-relative">
                        <p>Total Users</p>
                        <h3><?= number_format($userStats['total_users'] ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success position-relative">
                        <p>Active Users</p>
                        <h3><?= number_format($userStats['active_users'] ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info position-relative">
                        <p>Active Sessions (Today)</p>
                        <h3><?= number_format($activeSessions ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-activity"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning position-relative">
                        <p>System Alerts</p>
                        <h3><?= number_format($alertCount ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success position-relative">
                        <p>Total Warehouses</p>
                        <h3><?= number_format($warehouseStats['total_warehouses'] ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info position-relative">
                        <p>Active Warehouses</p>
                        <h3><?= number_format($warehouseStats['active_warehouses'] ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-building-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary position-relative">
                        <p>Total Departments</p>
                        <h3><?= number_format($departmentStats['total_departments'] ?? 0) ?></h3>
                        <div class="icon">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card <?= ($backupStatus['status'] ?? 'warning') == 'success' ? 'success' : (($backupStatus['status'] ?? 'warning') == 'danger' ? 'danger' : 'warning') ?> position-relative">
                        <p>Backup Status</p>
                        <h3>
                            <?php if (($backupStatus['status'] ?? 'warning') == 'success'): ?>
                                <i class="bi bi-check-circle text-success"></i>
                            <?php elseif (($backupStatus['status'] ?? 'warning') == 'danger'): ?>
                                <i class="bi bi-x-circle text-danger"></i>
                            <?php else: ?>
                                <i class="bi bi-exclamation-circle text-warning"></i>
                            <?php endif; ?>
                        </h3>
                        <small class="text-muted"><?= esc($backupStatus['message'] ?? 'Unknown') ?></small>
                        <div class="icon">
                            <i class="bi bi-database"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- System Alerts -->
                <div class="col-lg-6 mb-4">
                    <div class="card table-card">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Recent System Alerts</h5>
                                <a href="<?= base_url('it-administrator/logs') ?>" class="btn btn-sm btn-outline-primary">
                                    View All Logs <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (!empty($systemAlerts)): ?>
                                <?php foreach ($systemAlerts as $alert): ?>
                                    <div class="alert-item <?= esc($alert['level']) ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-<?= $alert['level'] == 'critical' || $alert['level'] == 'emergency' ? 'danger' : ($alert['level'] == 'error' ? 'warning' : 'warning') ?>">
                                                <?= strtoupper($alert['level']) ?>
                                            </span>
                                            <small class="text-muted"><?= esc($alert['timestamp']) ?></small>
                                        </div>
                                        <div class="text-truncate" style="max-width: 100%;" title="<?= esc($alert['message']) ?>">
                                            <?= esc(substr($alert['message'], 0, 150)) ?><?= strlen($alert['message']) > 150 ? '...' : '' ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-check-circle fs-1 text-success"></i>
                                    <p class="text-muted mt-2">No recent alerts</p>
                                    <p class="text-muted small">System is running smoothly</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Statistics -->
                <div class="col-lg-6 mb-4">
                    <div class="card table-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-graph-up"></i> System Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-0 text-primary"><?= number_format($assignmentStats['users_with_assignments'] ?? 0) ?></h4>
                                        <small class="text-muted">Users with Warehouse Assignments</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-0 text-warning"><?= number_format($assignmentStats['users_without_assignments'] ?? 0) ?></h4>
                                        <small class="text-muted">Users without Assignments</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-0 text-info"><?= number_format($warehouseStats['warehouses_with_managers'] ?? 0) ?></h4>
                                        <small class="text-muted">Warehouses with Managers</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-0 text-danger"><?= number_format($warehouseStats['warehouses_without_managers'] ?? 0) ?></h4>
                                        <small class="text-muted">Warehouses without Managers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="mb-0 text-success"><?= number_format($backupStatus['backup_count'] ?? 0) ?></h4>
                                        <small class="text-muted">Total Backup Files</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('it-administrator/users') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-people"></i> Manage Users
                                </a>
                                <a href="<?= base_url('it-administrator/warehouse-assignments') ?>" class="btn btn-outline-info">
                                    <i class="bi bi-diagram-3"></i> Warehouse Assignments
                                </a>
                                <a href="<?= base_url('it-administrator/backup') ?>" class="btn btn-outline-success">
                                    <i class="bi bi-database"></i> Database Backup
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Distribution -->
            <?php if (!empty($userStats['role_counts'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> User Role Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($userStats['role_counts'] as $roleKey => $count): ?>
                                    <?php if ($count > 0): ?>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h5 class="mb-0"><?= number_format($count) ?></h5>
                                                <small class="text-muted"><?= ucwords(str_replace('_', ' ', $roleKey)) ?></small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Auto-refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>
