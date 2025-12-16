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
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.3;
        }
        .quick-action-btn {
            padding: 1rem;
            text-align: center;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .quick-action-btn i {
            font-size: 1.5rem;
        }
    </style>
</head>
<body class="bg-light">
    
    <!-- Include Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>
    
    <!-- Include Top Navigation Bar -->
    <?= view('templates/top_navbar', ['user' => $user, 'page_title' => 'Warehouse Staff Dashboard']) ?>
    
    <!-- Main Content Area -->
    <div class="main-content">
        <div class="container-fluid">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-muted mb-3"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <a href="<?= base_url('warehouse-staff/receive') ?>" class="quick-action-btn btn btn-success d-block text-white">
                        <i class="bi bi-box-arrow-in-down d-block mb-1"></i>
                        Receive Stock
                    </a>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <a href="<?= base_url('warehouse-staff/issue') ?>" class="quick-action-btn btn btn-warning d-block">
                        <i class="bi bi-box-arrow-up d-block mb-1"></i>
                        Issue Stock
                    </a>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <a href="<?= base_url('warehouse-staff/transfer') ?>" class="quick-action-btn btn btn-info d-block text-white">
                        <i class="bi bi-arrow-left-right d-block mb-1"></i>
                        Transfer Stock
                    </a>
                </div>
                <div class="col-6 col-md-3 mb-3">
                    <a href="<?= base_url('warehouse-staff/scan-item') ?>" class="quick-action-btn btn btn-primary d-block text-white">
                        <i class="bi bi-qr-code-scan d-block mb-1"></i>
                        Scan Item
                    </a>
                </div>
            </div>
            
            <!-- Inventory Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-muted mb-3"><i class="bi bi-box-seam me-2"></i>Inventory Overview <small id="last-refresh" class="text-muted float-end" style="font-size: 0.7rem;"></small></h5>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card primary h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Items</h6>
                                <h3 class="mb-0 text-primary" id="stat-total-items"><?= number_format($totalItems ?? 0) ?></h3>
                                <small class="text-muted">Unique materials</small>
                            </div>
                            <i class="bi bi-boxes stat-icon text-primary"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Stock</h6>
                                <h3 class="mb-0 text-success" id="stat-total-stock"><?= number_format($totalStock ?? 0, 0) ?></h3>
                                <small class="text-muted">All warehouses</small>
                            </div>
                            <i class="bi bi-stack stat-icon text-success"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card danger h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Low Stock</h6>
                                <h3 class="mb-0 text-danger" id="stat-low-stock"><?= number_format($lowStockCount ?? 0) ?></h3>
                                <small class="text-muted">Need reorder</small>
                            </div>
                            <i class="bi bi-exclamation-triangle stat-icon text-danger"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card stat-card info h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">My Tasks Today</h6>
                                <h3 class="mb-0 text-info" id="stat-my-tasks"><?= number_format($myTodayActivities ?? 0) ?></h3>
                                <small class="text-muted">Movements made</small>
                            </div>
                            <i class="bi bi-person-check stat-icon text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Today's & Monthly Activity Summary -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-calendar-day me-2"></i>Today's Activity</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="p-2 rounded bg-success bg-opacity-10">
                                        <h4 class="text-success mb-0" id="stat-today-receipts"><?= $todaysReceipts ?? 0 ?></h4>
                                        <small class="text-muted">Receive</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded bg-warning bg-opacity-10">
                                        <h4 class="text-warning mb-0" id="stat-today-issues"><?= $todaysIssues ?? 0 ?></h4>
                                        <small class="text-muted">Issues</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded bg-info bg-opacity-10">
                                        <h4 class="text-info mb-0" id="stat-today-transfers"><?= $todaysTransfers ?? 0 ?></h4>
                                        <small class="text-muted">Transfers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-calendar-month me-2"></i>This Month (<?= date('F') ?>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="p-2 rounded bg-success bg-opacity-10">
                                        <h4 class="text-success mb-0" id="stat-month-receipts"><?= $monthlyReceipts ?? 0 ?></h4>
                                        <small class="text-muted">Receive</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded bg-warning bg-opacity-10">
                                        <h4 class="text-warning mb-0" id="stat-month-issues"><?= $monthlyIssues ?? 0 ?></h4>
                                        <small class="text-muted">Issues</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded bg-info bg-opacity-10">
                                        <h4 class="text-info mb-0" id="stat-month-transfers"><?= $monthlyTransfers ?? 0 ?></h4>
                                        <small class="text-muted">Transfers</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Activities -->
                <div class="col-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Stock Movements</h6>
                            <a href="<?= base_url('warehouse-staff/activity') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentActivities)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reference</th>
                                                <th>Material</th>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Warehouse</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <tr>
                                                    <td><code><?= esc($activity['reference_number'] ?? '-') ?></code></td>
                                                    <td>
                                                        <?= esc($activity['material_name'] ?? '-') ?>
                                                        <br><small class="text-muted"><?= esc($activity['material_code'] ?? '') ?></small>
                                                    </td>
                                                    <td>
                                                        <?php
                                                            $badgeClass = match($activity['movement_type'] ?? '') {
                                                                'Receipt' => 'bg-success',
                                                                'Issue' => 'bg-warning text-dark',
                                                                'Transfer' => 'bg-info',
                                                                'Adjustment' => 'bg-secondary',
                                                                'Return' => 'bg-primary',
                                                                default => 'bg-secondary'
                                                            };
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= esc($activity['movement_type'] ?? '-') ?></span>
                                                    </td>
                                                    <td><strong><?= number_format($activity['quantity'] ?? 0, 2) ?></strong></td>
                                                    <td>
                                                        <?php if ($activity['movement_type'] === 'Transfer'): ?>
                                                            <small><?= esc($activity['from_warehouse_name'] ?? '?') ?> → <?= esc($activity['to_warehouse_name'] ?? '?') ?></small>
                                                        <?php elseif ($activity['movement_type'] === 'Receipt'): ?>
                                                            <small>→ <?= esc($activity['to_warehouse_name'] ?? '-') ?></small>
                                                        <?php elseif ($activity['movement_type'] === 'Issue'): ?>
                                                            <small><?= esc($activity['from_warehouse_name'] ?? '-') ?> →</small>
                                                        <?php else: ?>
                                                            <small>-</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><small><?= date('M d, H:i', strtotime($activity['created_at'])) ?></small></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mb-0 mt-2">No recent activities</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Real-time dashboard updates
        const REFRESH_INTERVAL = 30000; // 30 seconds
        
        function updateDashboardStats() {
            fetch('<?= base_url('warehouse-staff/ajax/dashboard-stats') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.stats) {
                    const s = data.stats;
                    
                    // Update Inventory Overview cards
                    document.getElementById('stat-total-items').textContent = s.totalItems;
                    document.getElementById('stat-total-stock').textContent = s.totalStock;
                    document.getElementById('stat-low-stock').textContent = s.lowStockCount;
                    document.getElementById('stat-my-tasks').textContent = s.myTodayActivities;
                    
                    // Update Today's Activity
                    document.getElementById('stat-today-receipts').textContent = s.todaysReceipts;
                    document.getElementById('stat-today-issues').textContent = s.todaysIssues;
                    document.getElementById('stat-today-transfers').textContent = s.todaysTransfers;
                    
                    // Update Monthly Activity
                    document.getElementById('stat-month-receipts').textContent = s.monthlyReceipts;
                    document.getElementById('stat-month-issues').textContent = s.monthlyIssues;
                    document.getElementById('stat-month-transfers').textContent = s.monthlyTransfers;
                    
                    // Update last refresh time
                    document.getElementById('last-refresh').textContent = 'Last updated: ' + data.timestamp;
                }
            })
            .catch(error => console.error('Dashboard refresh error:', error));
        }
        
        // Auto-refresh every 30 seconds
        setInterval(updateDashboardStats, REFRESH_INTERVAL);
        
        // Also refresh when page becomes visible again
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateDashboardStats();
            }
        });
    </script>
</body>
</html>