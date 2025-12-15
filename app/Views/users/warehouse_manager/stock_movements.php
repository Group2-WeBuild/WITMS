<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .movement-type-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-arrow-left-right"></i> Stock Movements</h2>
                <p class="text-muted">Track all inventory movements across warehouses</p>
            </div>
            <div>
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>

        <!-- Movement Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Movements</h6>
                            <h4 class="mb-0"><?= number_format($stats['total_movements'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-arrow-left-right text-primary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Today's Movements</h6>
                            <h4 class="mb-0"><?= number_format($stats['today_movements'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-calendar-day text-success fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">This Week</h6>
                            <h4 class="mb-0"><?= number_format($stats['week_movements'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-calendar-week text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">This Month</h6>
                            <h4 class="mb-0"><?= number_format($stats['month_movements'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-calendar-month text-warning fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movements Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Movements</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Reference #</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Material</th>
                                <th>From Warehouse</th>
                                <th>To Warehouse</th>
                                <th>Quantity</th>
                                <th>Performed By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movements)): ?>
                                <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td>
                                            <code><?= esc($movement['reference_number']) ?></code>
                                        </td>
                                        <td><?= date('M d, Y H:i', strtotime($movement['movement_date'])) ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = [
                                                'receipt' => 'bg-success',
                                                'issue' => 'bg-danger',
                                                'transfer' => 'bg-primary',
                                                'adjustment' => 'bg-warning',
                                                'return' => 'bg-info'
                                            ];
                                            $badgeClass = $badgeClass[$movement['movement_type']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge movement-type-badge <?= $badgeClass ?>">
                                                <?= ucfirst($movement['movement_type']) ?>
                                            </span>
                                        </td>
                                        <td><?= esc($movement['material_name']) ?></td>
                                        <td><?= esc($movement['from_warehouse_name'] ?? '-') ?></td>
                                        <td><?= esc($movement['to_warehouse_name'] ?? '-') ?></td>
                                        <td class="text-end">
                                            <strong><?= number_format($movement['quantity']) ?></strong>
                                            <small class="text-muted"><?= esc($movement['unit_abbr'] ?? 'units') ?></small>
                                        </td>
                                        <td><?= esc(trim(($movement['performed_by_first'] ?? '') . ' ' . ($movement['performed_by_last'] ?? '')) ?: 'System') ?></td>
                                        <td>
                                            <?php 
                                            if ($movement['movement_type'] === 'Transfer') {
                                                if ($movement['approved_by']) {
                                                    echo '<span class="badge bg-success">Approved</span>';
                                                } else {
                                                    echo '<span class="badge bg-warning">Pending</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-primary">Completed</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No stock movements found</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>