<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Edit Material') ?></title>
    
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Edit Material']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-pencil-square"></i> Edit Material</h1>
                <a href="<?= base_url('warehouse-manager/materials') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Materials
                </a>
            </div>

            <!-- Edit Material Form -->
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-boxes"></i> Material Information</h5>
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

                            <form action="<?= base_url('warehouse-manager/materials/update/' . ($material['id'] ?? '')) ?>" method="post">
                                <?= csrf_field() ?>
                                
                                <!-- Basic Information -->
                                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Material Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" required 
                                               pattern="[A-Za-z\s]+"
                                               value="<?= esc($material['name'] ?? '') ?>"
                                               placeholder="e.g., Steel Rebar 12mm">
                                        <small class="text-muted">Only letters and spaces allowed (no special characters)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Material Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" id="code" class="form-control" required 
                                               pattern="^[A-Z0-9]+(-[A-Z0-9]+)*$"
                                               value="<?= esc($material['code'] ?? '') ?>"
                                               placeholder="e.g., MAT101 or MAT-101"
                                               style="text-transform: uppercase;">
                                        <small class="text-muted">Uppercase format: MAT101 or MAT-101</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>" 
                                                            <?= ($material['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                                        <?= esc($category['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                        <select name="unit_id" class="form-select" required>
                                            <option value="">Select Unit</option>
                                            <?php if (!empty($units)): ?>
                                                <?php foreach ($units as $unit): ?>
                                                    <option value="<?= $unit['id'] ?>"
                                                            <?= ($material['unit_id'] ?? '') == $unit['id'] ? 'selected' : '' ?>>
                                                        <?= esc($unit['name']) ?> (<?= esc($unit['abbreviation']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">QR Code</label>
                                        <input type="text" name="qrcode" class="form-control" readonly
                                               value="<?= esc($material['qrcode'] ?? '') ?>"
                                               placeholder="Auto-generated by system">
                                        <small class="text-muted">QR code is auto-generated by the system</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Unit Cost (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_cost" id="unit_cost" class="form-control" step="0.01" min="0.01" required
                                               value="<?= esc($material['unit_cost'] ?? '') ?>"
                                               placeholder="0.00">
                                        <small class="text-muted">Must be greater than 0</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" 
                                              placeholder="Detailed description of the material..."><?= esc($material['description'] ?? '') ?></textarea>
                                </div>

                                <!-- Inventory Settings -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Inventory Settings</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                                        <input type="number" name="reorder_level" id="reorder_level" class="form-control" step="0.01" min="0.01" required
                                               value="<?= esc($material['reorder_level'] ?? '') ?>"
                                               placeholder="Minimum quantity before reorder">
                                        <small class="text-muted">Alert when stock falls below this level (must be greater than 0)</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Reorder Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="reorder_quantity" id="reorder_quantity" class="form-control" step="0.01" min="0.01" required
                                               value="<?= esc($material['reorder_quantity'] ?? '') ?>"
                                               placeholder="Suggested order quantity">
                                        <small class="text-muted">Recommended quantity to order (must be greater than 0)</small>
                                    </div>
                                </div>

                                <!-- Perishability -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Perishability</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="is_perishable" 
                                                   id="isPerishable" value="1"
                                                   <?= ($material['is_perishable'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="isPerishable">
                                                This material is perishable
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Shelf Life (Days)</label>
                                        <input type="number" name="shelf_life_days" class="form-control" min="0"
                                               value="<?= esc($material['shelf_life_days'] ?? '') ?>"
                                               placeholder="Number of days before expiration">
                                        <small class="text-muted">Only for perishable items</small>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="bi bi-check-circle"></i> Update Material
                                    </button>
                                    <a href="<?= base_url('warehouse-manager/materials') ?>" class="btn btn-secondary">
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
    
    <script>
        // Material name validation - only letters and spaces
        document.getElementById('name').addEventListener('input', function(e) {
            // Remove any special characters, keep only letters and spaces
            this.value = this.value.replace(/[^A-Za-z\s]/g, '');
        });

        // Material code validation - auto uppercase and format
        document.getElementById('code').addEventListener('input', function(e) {
            // Convert to uppercase
            this.value = this.value.toUpperCase();
            // Remove invalid characters (keep only A-Z, 0-9, and hyphens)
            this.value = this.value.replace(/[^A-Z0-9\-]/g, '');
        });

        // Validate numeric fields must be greater than 0
        ['unit_cost', 'reorder_level', 'reorder_quantity'].forEach(function(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('change', function() {
                    const value = parseFloat(this.value);
                    if (value <= 0) {
                        alert('This field must be greater than 0');
                        this.value = '';
                        this.focus();
                    }
                });
            }
        });

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const code = document.getElementById('code').value;
            const unitCost = parseFloat(document.getElementById('unit_cost').value);
            const reorderLevel = parseFloat(document.getElementById('reorder_level').value);
            const reorderQuantity = parseFloat(document.getElementById('reorder_quantity').value);

            // Validate name pattern
            if (!/^[A-Za-z\s]+$/.test(name)) {
                e.preventDefault();
                alert('Material name can only contain letters and spaces (no special characters)');
                document.getElementById('name').focus();
                return false;
            }

            // Validate code pattern
            if (!/^[A-Z0-9]+(-[A-Z0-9]+)*$/.test(code)) {
                e.preventDefault();
                alert('Material code must be in uppercase format like MAT101 or MAT-101');
                document.getElementById('code').focus();
                return false;
            }

            // Validate numeric fields
            if (unitCost <= 0 || reorderLevel <= 0 || reorderQuantity <= 0) {
                e.preventDefault();
                alert('Unit Cost, Reorder Level, and Reorder Quantity must be greater than 0');
                return false;
            }
        });
    </script>
</body>
</html>
