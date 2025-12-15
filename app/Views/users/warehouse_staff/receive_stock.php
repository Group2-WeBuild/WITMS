<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Stock - WITMS</title>
    
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Receive Stock']) ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-box-arrow-in-down"></i> Receive Stock</h5>
                        </div>
                        <div class="card-body">
                            <form id="receive-stock-form" method="post" action="<?= base_url('warehouse-staff/receive/process') ?>">
                                <?= csrf_field() ?>
                                
                                <!-- Material Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Material <span class="text-danger">*</span></label>
                                    <select name="material_id" class="form-select" required>
                                        <option value="">Select Material</option>
                                        <?php if (isset($materials)): ?>
                                            <?php foreach ($materials as $material): ?>
                                                <option value="<?= $material['id'] ?>">
                                                    <?= esc($material['name']) ?> (<?= esc($material['code']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" class="form-control" min="1" required>
                                </div>
                                
                                <!-- Warehouse -->
                                <div class="mb-3">
                                    <label class="form-label">Warehouse <span class="text-danger">*</span></label>
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
                                
                                <!-- Location -->
                                <div class="mb-3">
                                    <label class="form-label">Location/Bin</label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g., Aisle 3, Rack B">
                                </div>
                                
                                <!-- Reference -->
                                <div class="mb-3">
                                    <label class="form-label">Reference/PO Number</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Purchase Order or Delivery Note">
                                </div>
                                
                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Receive Stock
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
</body>
</html>
