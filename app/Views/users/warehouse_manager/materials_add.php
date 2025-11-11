<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Add Material') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Add Material']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-plus-circle"></i> Add New Material</h1>
                <a href="<?= base_url('warehouse-manager/materials') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Materials
                </a>
            </div>

            <!-- Add Material Form -->
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
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

                            <form action="<?= base_url('warehouse-manager/materials/store') ?>" method="post">
                                <?= csrf_field() ?>
                                
                                <!-- Basic Information -->
                                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Material Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" required 
                                               placeholder="e.g., Steel Rebar 12mm">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Material Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control" required 
                                               placeholder="e.g., MAT-001">
                                        <small class="text-muted">Unique identifier for the material</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                            <?php if (!empty($categories)): ?>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?= $category['id'] ?>">
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
                                                    <option value="<?= $unit['id'] ?>">
                                                        <?= esc($unit['name']) ?> (<?= esc($unit['abbreviation']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>                                <div class="mb-3">
                                    <label class="form-label">QR Code</label>
                                    <input type="text" name="qrcode" class="form-control" 
                                           placeholder="Optional - leave empty to auto-generate">
                                    <small class="text-muted">System will auto-generate if left empty</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" 
                                              placeholder="Detailed description of the material..."></textarea>
                                </div>

                                <!-- Inventory Settings -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Inventory Settings</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Unit Cost (₱) <span class="text-danger">*</span></label>
                                        <input type="number" name="unit_cost" class="form-control" step="0.01" min="0" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Reorder Level <span class="text-danger">*</span></label>
                                        <input type="number" name="reorder_level" class="form-control" step="0.01" min="0" required>
                                        <small class="text-muted">Minimum stock before reorder</small>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Reorder Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="reorder_quantity" class="form-control" step="0.01" min="0" required>
                                        <small class="text-muted">Quantity to order when restocking</small>
                                    </div>
                                </div>

                                <!-- Perishable Settings -->
                                <h6 class="border-bottom pb-2 mb-3 mt-4">Perishable Settings</h6>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_perishable" 
                                               id="isPerishable" value="1" onchange="toggleShelfLife()">
                                        <label class="form-check-label" for="isPerishable">
                                            This material is perishable (has expiration date)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3" id="shelfLifeGroup" style="display: none;">
                                    <label class="form-label">Shelf Life (Days)</label>
                                    <input type="number" name="shelf_life_days" class="form-control" min="1">
                                    <small class="text-muted">Number of days before expiration</small>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle"></i> Add Material
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
        function toggleShelfLife() {
            const checkbox = document.getElementById('isPerishable');
            const shelfLifeGroup = document.getElementById('shelfLifeGroup');
            
            if (checkbox.checked) {
                shelfLifeGroup.style.display = 'block';
            } else {
                shelfLifeGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>
