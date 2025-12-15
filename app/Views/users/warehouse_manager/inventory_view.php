<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Inventory Details') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Include Mobile Styles -->
    <?= view('templates/mobile_styles') ?>
    
    <style>
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding-right: 20px;
            padding-left: 20px;
            padding-bottom: 30px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Inventory Details']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-eye"></i> Inventory Item Details</h1>
                <div>
                    <a href="<?= base_url('warehouse-manager/inventory/edit/' . ($inventory['id'] ?? '')) ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="<?= base_url('warehouse-manager/inventory') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Inventory
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Details -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Item Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Material Name:</strong>
                                    <p class="mb-0"><?= esc($inventory['material_name'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Material Code:</strong>
                                    <p class="mb-0"><code><?= esc($inventory['material_code'] ?? 'N/A') ?></code></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Category:</strong>
                                    <p class="mb-0"><?= esc($inventory['category_name'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Warehouse:</strong>
                                    <p class="mb-0"><?= esc($inventory['warehouse_name'] ?? 'N/A') ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Quantity:</strong>
                                    <p class="mb-0 fs-4 text-primary">
                                        <?= number_format($inventory['quantity'] ?? 0, 2) ?> 
                                        <small class="text-muted"><?= esc($inventory['unit_abbr'] ?? '') ?></small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Unit Cost:</strong>
                                    <p class="mb-0 fs-5">₱<?= number_format($inventory['unit_cost'] ?? 0, 2) ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Total Value:</strong>
                                    <p class="mb-0 fs-5 text-success">
                                        ₱<?= number_format(($inventory['quantity'] ?? 0) * ($inventory['unit_cost'] ?? 0), 2) ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Batch Number:</strong>
                                    <p class="mb-0"><?= esc($inventory['batch_number'] ?? 'N/A') ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Location:</strong>
                                    <p class="mb-0"><?= esc($inventory['location_in_warehouse'] ?? 'Not specified') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Expiration Date:</strong>
                                    <p class="mb-0">
                                        <?php if (!empty($inventory['expiration_date'])): ?>
                                            <?= date('M d, Y', strtotime($inventory['expiration_date'])) ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Last Updated:</strong>
                                    <p class="mb-0 text-muted">
                                        <small><?= date('M d, Y h:i A', strtotime($inventory['last_updated'] ?? $inventory['updated_at'] ?? 'now')) ?></small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>QR Code:</strong>
                                    <p class="mb-0"><code><?= esc($inventory['qrcode'] ?? 'N/A') ?></code></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Stock Movements -->
                    <?php if (!empty($movements)): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-arrows-move"></i> Recent Stock Movements</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Reference</th>
                                            <th>Performed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($movements as $movement): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($movement['movement_date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $movement['movement_type'] === 'in' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($movement['movement_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= number_format($movement['quantity'], 2) ?></td>
                                            <td><?= esc($movement['reference_number'] ?? 'N/A') ?></td>
                                            <td><?= esc($movement['performed_by_name'] ?? 'System') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar Info -->
                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Status</h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            $reorderLevel = $inventory['reorder_level'] ?? 0;
                            $quantity = $inventory['quantity'] ?? 0;
                            ?>
                            
                            <?php if ($quantity <= $reorderLevel): ?>
                                <div class="alert alert-warning mb-2">
                                    <i class="bi bi-exclamation-triangle"></i> <strong>Low Stock!</strong><br>
                                    Below reorder level (<?= $reorderLevel ?>)
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success mb-2">
                                    <i class="bi bi-check-circle"></i> <strong>Stock OK</strong><br>
                                    Above reorder level
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($inventory['expiration_date'])): ?>
                                <?php 
                                $expiryDate = strtotime($inventory['expiration_date']);
                                $daysUntilExpiry = floor(($expiryDate - time()) / (60 * 60 * 24));
                                ?>
                                <?php if ($daysUntilExpiry <= 30 && $daysUntilExpiry > 0): ?>
                                    <div class="alert alert-danger mb-0">
                                        <i class="bi bi-calendar-x"></i> <strong>Expiring Soon!</strong><br>
                                        <?= $daysUntilExpiry ?> days remaining
                                    </div>
                                <?php elseif ($daysUntilExpiry <= 0): ?>
                                    <div class="alert alert-danger mb-0">
                                        <i class="bi bi-x-circle"></i> <strong>Expired!</strong>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('warehouse-manager/inventory/edit/' . ($inventory['id'] ?? '')) ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Edit Details
                                </a>
                                <button class="btn btn-primary" onclick="window.print()">
                                    <i class="bi bi-printer"></i> Print
                                </button>
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
