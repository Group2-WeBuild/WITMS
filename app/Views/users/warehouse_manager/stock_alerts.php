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
        .alert-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert-item {
            border-left: 4px solid;
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: transform 0.2s;
        }
        .alert-item:hover {
            transform: translateX(5px);
        }
        .alert-critical {
            border-left-color: #dc3545;
        }
        .alert-warning {
            border-left-color: #ffc107;
        }
        .alert-info {
            border-left-color: #0dcaf0;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-exclamation-triangle"></i> Stock Alerts</h2>
                <p class="text-muted">Monitor inventory levels and expiration dates</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-outline-primary" onclick="refreshAlerts()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Alert Summary -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="alert-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Critical Alerts</h6>
                            <h4 class="mb-0 text-danger"><?= count(array_filter($lowStockItems ?? [], fn($item) => $item['quantity'] == 0)) ?></h4>
                            <small class="text-muted">Out of stock</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-x-circle text-danger fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="alert-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Low Stock</h6>
                            <h4 class="mb-0 text-warning"><?= count($lowStockItems ?? []) ?></h4>
                            <small class="text-muted">Below reorder level</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-exclamation-triangle text-warning fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="alert-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Expiring Soon</h6>
                            <h4 class="mb-0 text-info"><?= count($expiringItems ?? []) ?></h4>
                            <small class="text-muted">Within 30 days</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-clock-history text-info fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="alert-card bg-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Alerts</h6>
                            <h4 class="mb-0"><?= (count($lowStockItems ?? []) + count($expiringItems ?? [])) ?></h4>
                            <small class="text-muted">Active alerts</small>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-bell text-secondary fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-warning"></i> Low Stock Items</h5>
                <button class="btn btn-sm btn-outline-warning" onclick="exportAlerts('lowstock')">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($lowStockItems)): ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <div class="alert-item alert-<?= $item['quantity'] == 0 ? 'critical' : 'warning' ?>">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <strong><?= esc($item['material_name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= esc($item['category_name']) ?> • <?= esc($item['warehouse_name']) ?></small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-<?= $item['quantity'] == 0 ? 'danger' : 'warning' ?>">
                                        <?= number_format($item['quantity']) ?> <?= esc($item['unit_abbr']) ?>
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted">Reorder at</small><br>
                                    <strong><?= number_format($item['reorder_level']) ?> <?= esc($item['unit_abbr']) ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted">Suggested</small><br>
                                    <strong><?= number_format($item['reorder_quantity']) ?> <?= esc($item['unit_abbr']) ?></strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-sm btn-primary" onclick="contactProcurement(<?= $item['material_id'] ?>)">
                                        <i class="bi bi-person-plus"></i> Contact Procurement
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success fs-1"></i>
                        <p class="text-muted mt-2">No low stock alerts</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Expiring Items -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history text-info"></i> Items Expiring Soon</h5>
                <button class="btn btn-sm btn-outline-info" onclick="exportAlerts('expiring')">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
            <div class="card-body">
                <?php if (!empty($expiringItems)): ?>
                    <?php foreach ($expiringItems as $item): ?>
                        <?php 
                        $daysLeft = (new DateTime($item['expiration_date']))->diff(new DateTime())->days;
                        $alertClass = $daysLeft <= 7 ? 'critical' : ($daysLeft <= 15 ? 'warning' : 'info');
                        ?>
                        <div class="alert-item alert-<?= $alertClass ?>">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <strong><?= esc($item['material_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">Batch: <?= esc($item['batch_number']) ?> • <?= esc($item['warehouse_name']) ?></small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge bg-<?= $daysLeft <= 7 ? 'danger' : ($daysLeft <= 15 ? 'warning' : 'info') ?>">
                                        <?= $daysLeft ?> days left
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted">Expiry Date</small><br>
                                    <strong><?= date('M d, Y', strtotime($item['expiration_date'])) ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <small class="text-muted">Quantity</small><br>
                                    <strong><?= number_format($item['quantity']) ?> <?= esc($item['unit_abbr']) ?></strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-sm btn-warning" onclick="createDiscount(<?= $item['id'] ?>)">
                                        <i class="bi bi-tag"></i> Discount
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success fs-1"></i>
                        <p class="text-muted mt-2">No items expiring soon</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function refreshAlerts() {
            location.reload();
        }

        function contactProcurement(materialId) {
            // Show confirmation dialog
            if (confirm('Are you sure you want to send an email to the procurement officer?')) {
                // Send AJAX request to contact procurement
                fetch('<?= base_url('/warehouse-manager/stock-alerts/contact-procurement') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'materialId=' + encodeURIComponent(materialId) + '&<?= csrf_token() ?>=<?= csrf_hash() ?>'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email sent to procurement officer successfully!');
                    } else {
                        alert('Failed to send email: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending the email.');
                });
            }
        }

        function createDiscount(inventoryId) {
            // Open modal to create discount for expiring items
            alert(`Creating discount for inventory item #${inventoryId}`);
        }

        function exportAlerts(type) {
            // Simulate export functionality
            const filename = type === 'lowstock' ? 'low-stock-alerts.csv' : 'expiring-items.csv';
            alert(`Exporting ${filename}...`);
        }

        // Auto-refresh alerts every 5 minutes
        setInterval(() => {
            console.log('Refreshing alerts...');
            // In production, this would make an AJAX call to refresh data
        }, 300000);
    </script>
</body>
</html>