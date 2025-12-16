<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Edit Inventory') ?></title>
    
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Edit Inventory']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-pencil-square"></i> Edit Inventory Item</h1>
                <a href="<?= base_url('warehouse-manager/inventory') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>

            <!-- Edit Inventory Form -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Inventory Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($validation)): ?>
                                <div class="alert alert-danger">
                                    <?= $validation->listErrors() ?>
                                </div>
                            <?php endif; ?>

                            <!-- Display Current Material Info (Read-only) -->
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="bi bi-info-circle"></i> Current Item Details</h6>
                                <p class="mb-1"><strong>Material:</strong> <?= esc($inventory['material_name'] ?? 'N/A') ?></p>
                                <p class="mb-1"><strong>Warehouse:</strong> <?= esc($inventory['warehouse_name'] ?? 'N/A') ?></p>
                                <p class="mb-1"><strong>Current Quantity:</strong> <?= esc($inventory['quantity'] ?? '0') ?> <?= esc($inventory['unit_abbr'] ?? '') ?></p>
                                <p class="mb-0"><small class="text-muted">Note: Material and warehouse cannot be changed. To move stock, use stock transfer feature.</small></p>
                            </div>

                            <form action="<?= base_url('warehouse-manager/inventory/update/' . ($inventory['id'] ?? '')) ?>" method="post">
                                <?= csrf_field() ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batch Number <span class="text-muted">(Auto-generated)</span></label>
                                        <input type="text" class="form-control" 
                                               value="<?= esc($inventory['batch_number'] ?? 'Will be auto-generated on save') ?>" 
                                               readonly
                                               style="background-color: #e9ecef; cursor: not-allowed;">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i> Batch number is automatically generated when saved.
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Location in Warehouse</label>
                                        <input type="text" name="location_in_warehouse" class="form-control" 
                                               value="<?= esc($inventory['location_in_warehouse'] ?? '') ?>"
                                               placeholder="e.g., Aisle A, Rack 5">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Expiration Date</label>
                                    <input type="date" name="expiration_date" class="form-control"
                                           value="<?= esc($inventory['expiration_date'] ?? '') ?>">
                                    <small class="text-muted">For perishable items only</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="bi bi-check-circle"></i> Update Inventory
                                    </button>
                                    <a href="<?= base_url('warehouse-manager/inventory') ?>" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </a>
                                </div>
                            </form>
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
