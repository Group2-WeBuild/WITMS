<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Material Details') ?></title>
    
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Material Details']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-eye"></i> Material Details</h1>
                <div>
                    <a href="<?= base_url('warehouse-manager/materials/edit/' . ($material['id'] ?? '')) ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php if (($material['is_active'] ?? 1) == 1): ?>
                        <a href="<?= base_url('warehouse-manager/materials/deactivate/' . ($material['id'] ?? '')) ?>" 
                           class="btn btn-danger" onclick="return confirm('Deactivate this material?')">
                            <i class="bi bi-x-circle"></i> Deactivate
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('warehouse-manager/materials/activate/' . ($material['id'] ?? '')) ?>" 
                           class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Activate
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('warehouse-manager/materials') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Materials
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Details -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-boxes"></i> Material Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Material Name:</strong>
                                    <p class="mb-0 fs-5"><?= esc($material['name'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Material Code:</strong>
                                    <p class="mb-0"><code class="fs-6"><?= esc($material['code'] ?? 'N/A') ?></code></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Category:</strong>
                                    <p class="mb-0"><?= esc($material['category_name'] ?? 'N/A') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Unit of Measure:</strong>
                                    <p class="mb-0">
                                        <?= esc($material['unit_name'] ?? 'N/A') ?> 
                                        (<?= esc($material['unit_abbr'] ?? '') ?>)
                                    </p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>QR Code:</strong>
                                    <p class="mb-0"><code><?= esc($material['qrcode'] ?? 'N/A') ?></code></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Unit Cost:</strong>
                                    <p class="mb-0 fs-5 text-success">₱<?= number_format($material['unit_cost'] ?? 0, 2) ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <strong>Description:</strong>
                                    <p class="mb-0"><?= esc($material['description'] ?? 'No description available') ?></p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-3">Inventory Settings</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Reorder Level:</strong>
                                    <p class="mb-0"><?= number_format($material['reorder_level'] ?? 0, 2) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Reorder Quantity:</strong>
                                    <p class="mb-0"><?= number_format($material['reorder_quantity'] ?? 0, 2) ?></p>
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-3">Perishability</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Is Perishable:</strong>
                                    <p class="mb-0">
                                        <?php if (($material['is_perishable'] ?? 0) == 1): ?>
                                            <span class="badge bg-warning">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Shelf Life:</strong>
                                    <p class="mb-0">
                                        <?php if (!empty($material['shelf_life_days'])): ?>
                                            <?= esc($material['shelf_life_days']) ?> days
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <p class="mb-0">
                                        <?php if (($material['is_active'] ?? 1) == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Last Updated:</strong>
                                    <p class="mb-0 text-muted">
                                        <small><?= date('M d, Y h:i A', strtotime($material['updated_at'] ?? 'now')) ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Levels Across Warehouses -->
                    <?php if (!empty($material['inventory'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-building"></i> Stock Levels by Warehouse</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Warehouse</th>
                                            <th>Quantity</th>
                                            <th>Value</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($material['inventory'] as $inv): ?>
                                        <tr>
                                            <td><?= esc($inv['warehouse_name']) ?></td>
                                            <td><?= number_format($inv['quantity'], 2) ?> <?= esc($material['unit_abbr'] ?? '') ?></td>
                                            <td>₱<?= number_format($inv['quantity'] * ($material['unit_cost'] ?? 0), 2) ?></td>
                                            <td><?= esc($inv['location_in_warehouse'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php if ($inv['quantity'] <= ($material['reorder_level'] ?? 0)): ?>
                                                    <span class="badge bg-warning">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">OK</span>
                                                <?php endif; ?>
                                            </td>
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
                    <!-- Summary Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-graph-up"></i> Summary</h6>
                        </div>
                        <div class="card-body">
                            <?php 
                            $totalQuantity = 0;
                            $totalValue = 0;
                            if (!empty($material['inventory'])) {
                                foreach ($material['inventory'] as $inv) {
                                    $totalQuantity += $inv['quantity'];
                                    $totalValue += $inv['quantity'] * ($material['unit_cost'] ?? 0);
                                }
                            }
                            ?>
                            <div class="mb-3">
                                <small class="text-muted">Total Stock</small>
                                <h4 class="mb-0"><?= number_format($totalQuantity, 2) ?> <small><?= esc($material['unit_abbr'] ?? '') ?></small></h4>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Total Value</small>
                                <h4 class="mb-0 text-success">₱<?= number_format($totalValue, 2) ?></h4>
                            </div>
                            <div>
                                <small class="text-muted">Warehouses</small>
                                <h4 class="mb-0"><?= count($material['inventory'] ?? []) ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h6>
                        </div>
                        <div class="card-body text-center">
                            <div id="qrCodeContainer">
                                <p class="text-muted">Click the button below to generate QR code</p>
                                <button type="button" class="btn btn-success" id="generateMaterialQR" data-id="<?= $material['id'] ?? '' ?>">
                                    <i class="bi bi-qr-code-scan"></i> Generate QR Code
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('warehouse-manager/materials/edit/' . ($material['id'] ?? '')) ?>" class="btn btn-warning">
                                    <i class="bi bi-pencil"></i> Edit Material
                                </a>
                                <a href="<?= base_url('warehouse-manager/inventory/add') ?>" class="btn btn-primary">
                                    <i class="bi bi-plus"></i> Add Stock
                                </a>
                                <button class="btn btn-secondary" onclick="window.print()">
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#generateMaterialQR').on('click', function() {
                var materialId = $(this).data('id');
                var btn = $(this);
                
                btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Generating...');
                
                $.ajax({
                    url: '<?= base_url("warehouse-manager/materials/qr-generate/") ?>' + materialId,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#qrCodeContainer').html(`
                                <img src="${response.qr_code}" alt="QR Code" class="img-fluid mb-3" style="max-width: 250px;">
                                <p class="text-muted mb-3">Scan this QR code to view material details</p>
                                <div class="d-grid gap-2">
                                    <a href="${response.download_url}" class="btn btn-primary" download>
                                        <i class="bi bi-download"></i> Download QR Code
                                    </a>
                                    <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                        <i class="bi bi-arrow-clockwise"></i> Generate New
                                    </button>
                                </div>
                            `);
                        } else {
                            alert('Error: ' + response.message);
                            btn.prop('disabled', false).html('<i class="bi bi-qr-code-scan"></i> Generate QR Code');
                        }
                    },
                    error: function() {
                        alert('Failed to generate QR code. Please try again.');
                        btn.prop('disabled', false).html('<i class="bi bi-qr-code-scan"></i> Generate QR Code');
                    }
                });
            });
        });
    </script>
</body>
</html>
