<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Stock - WITMS</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <?= view('templates/mobile_styles') ?>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>
    
    <div class="main-content">
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Issue Stock']) ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-box-arrow-up"></i> Issue Stock</h5>
                        </div>
                        <div class="card-body">
                            <form id="issue-stock-form" method="post" action="<?= base_url('warehouse-staff/issue/process') ?>">
                                <?= csrf_field() ?>
                                
                                <!-- Material Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Material <span class="text-danger">*</span></label>
                                    <select name="material_id" id="material-select" class="form-select" required>
                                        <option value="">Select Material</option>
                                        <?php if (isset($materials)): ?>
                                            <?php foreach ($materials as $material): ?>
                                                <option value="<?= $material['id'] ?>" data-available="<?= $material['available_qty'] ?? 0 ?>">
                                                    <?= esc($material['name']) ?> (<?= esc($material['code']) ?>) - Available: <?= $material['available_qty'] ?? 0 ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="quantity-input" class="form-control" min="1" required>
                                    <small class="text-muted">Available: <span id="available-qty">0</span></small>
                                </div>
                                
                                <!-- Warehouse -->
                                <div class="mb-3">
                                    <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" class="form-select" required>
                                        <option value="">Select Warehouse</option>
                                        <?php if (isset($warehouses)): ?>
                                            <?php foreach ($warehouses as $warehouse): ?>
                                                <option value="<?= $warehouse['id'] ?>">
                                                    <?= esc($warehouse['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <!-- Issue To -->
                                <div class="mb-3">
                                    <label class="form-label">Issue To <span class="text-danger">*</span></label>
                                    <input type="text" name="issued_to" class="form-control" placeholder="Department, Project, or Person" required>
                                </div>
                                
                                <!-- Reference -->
                                <div class="mb-3">
                                    <label class="form-label">Reference/Requisition Number</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Material Requisition Number">
                                </div>
                                
                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-check-circle"></i> Issue Stock
                                    </button>
                                    <a href="<?= base_url('warehouse-staff/dashboard') ?>" class="btn btn-secondary">
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('material-select').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const available = selected.getAttribute('data-available') || 0;
            document.getElementById('available-qty').textContent = available;
            document.getElementById('quantity-input').max = available;
        });
    </script>
</body>
</html>
