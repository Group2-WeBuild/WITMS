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
    
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 14px;
        }
        
        .quick-action-card {
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
            background: white;
            height: 100%;
        }
        
        .quick-action-card:hover {
            border-color: #0d6efd;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2);
        }
        
        .quick-action-card i {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .recent-activity {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Dashboard']) ?>
        
        <div class="container-fluid">            <!-- Welcome Message -->
            <div class="mb-4">
                <h2 class="mb-2">Welcome back, <?= esc($user['full_name'] ?? 'User') ?>! </h2>
                <p class="text-muted">
                    Here's what's happening in your warehouse today.
                    <small class="ms-2"><i class="bi bi-info-circle"></i> Data updates in real-time</small>
                </p>
            </div>

            <!-- Assigned Warehouses -->
            <?php if (!empty($assigned_warehouses)): ?>
            <div class="mb-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-building"></i> Your Assigned Warehouses</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($assigned_warehouses as $assignment): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border <?= $assignment['is_primary'] ? 'border-warning' : 'border-secondary' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">
                                                <i class="bi bi-building"></i> <?= esc($assignment['warehouse_name']) ?>
                                            </h6>
                                            <?php if ($assignment['is_primary']): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="bi bi-star-fill"></i> Primary
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <strong>Code:</strong> <?= esc($assignment['warehouse_code']) ?>
                                        </p>
                                        <div class="d-flex gap-2">
                                            <span class="badge <?= $assignment['warehouse_is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= $assignment['warehouse_is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                            <a href="<?= base_url('warehouse-manager/warehouse/view/' . $assignment['warehouse_id']) ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="mb-4">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>No Warehouse Assignments</strong> - You are not currently assigned to any warehouses. 
                    Please contact your administrator.
                </div>
            </div>
            <?php endif; ?>

                              <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card bg-white">
                        <div class="icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <p class="text-uppercase">Total Staff</p>
                        <h3 class="text-primary"><?= number_format($stats['total_warehouse_personnel'] ?? 0) ?></h3>
                        <small class="text-muted">Active warehouse personnel</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card bg-white">
                        <div class="icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <p class="text-uppercase">Stock Items</p>
                        <h3 class="text-success"><?= number_format($stats['total_items'] ?? 0) ?></h3>
                        <small class="text-muted">Total inventory items</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card bg-white">
                        <div class="icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <p class="text-uppercase">Materials</p>
                        <h3 class="text-warning"><?= number_format($stats['total_materials'] ?? 0) ?></h3>
                        <small class="text-muted">Material catalog items</small>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card bg-white">
                        <div class="icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <p class="text-uppercase">Low Stock</p>
                        <h3 class="text-danger"><?= number_format($stats['low_stock_items'] ?? 0) ?></h3>
                        <small class="text-muted">Needs reordering</small>
                    </div>
                </div>            </div>

            <!-- Additional Stats Row -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6 class="text-muted text-uppercase">Total Quantity</h6>
                            <h4 class="text-info mb-0"><?= number_format($stats['total_quantity'] ?? 0, 2) ?></h4>
                            <small class="text-muted">Units in stock</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted text-uppercase">Inventory Value</h6>
                            <h4 class="text-success mb-0">₱<?= number_format($stats['total_value'] ?? 0, 2) ?></h4>
                            <small class="text-muted">Total stock value</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h6 class="text-muted text-uppercase">Perishable Items</h6>
                            <h4 class="text-warning mb-0"><?= number_format($stats['perishable_materials'] ?? 0) ?></h4>
                            <small class="text-muted">Require monitoring</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h6 class="text-muted text-uppercase">Expiring Soon</h6>
                            <h4 class="text-danger mb-0"><?= number_format($stats['expiring_soon'] ?? 0) ?></h4>
                            <small class="text-muted">Within 30 days</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-4">
                <h4 class="mb-3">Quick Actions</h4>
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('warehouse-manager/inventory/add') ?>" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-plus-circle text-primary"></i>
                                <h6 class="mt-2">Add Stock</h6>
                                <small class="text-muted">Receive new inventory</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('warehouse-manager/materials/add') ?>" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-boxes text-success"></i>
                                <h6 class="mt-2">Add Material</h6>
                                <small class="text-muted">Create new material</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('warehouse-manager/inventory/low-stock') ?>" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                <h6 class="mt-2">Low Stock Items</h6>
                                <small class="text-muted">View alerts</small>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="<?= base_url('warehouse-manager/reports') ?>" class="text-decoration-none">
                            <div class="quick-action-card">
                                <i class="bi bi-file-earmark-text text-info"></i>
                                <h6 class="mt-2">Reports</h6>
                                <small class="text-muted">View analytics</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-8 col-md-12 mb-4">
                    <div class="recent-activity">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary d-none d-md-inline-block">View All</a>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No recent activity to display.
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="recent-activity">
                        <h5 class="mb-3"><i class="bi bi-bell"></i> Notifications</h5>
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-inbox me-2"></i>
                            No new notifications.
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