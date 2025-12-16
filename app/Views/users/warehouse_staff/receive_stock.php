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
                            <?php if (session()->getFlashdata('errors')): ?>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                            <li><?= esc($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Workflow Steps -->
                            <div class="alert alert-light border mb-4">
                                <h6 class="mb-2"><i class="bi bi-info-circle text-primary me-2"></i>Receive Stock Workflow</h6>
                                <ol class="mb-0 small">
                                    <li>Select the material being received</li>
                                    <li>Enter the quantity and destination warehouse</li>
                                    <li>Add batch number for traceability (optional)</li>
                                    <li>Reference PO/Delivery note for records</li>
                                </ol>
                            </div>
                            
                            <form id="receive-stock-form" method="post" action="<?= base_url('warehouse-staff/receive/process') ?>">
                                <?= csrf_field() ?>
                                
                                <!-- Warehouse Selection First -->
                                <div class="mb-3">
                                    <label class="form-label">Destination Warehouse <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" id="warehouse-select" class="form-select" required>
                                        <option value="">Select Warehouse</option>
                                        <?php if (isset($warehouses)): ?>
                                            <?php foreach ($warehouses as $warehouse): ?>
                                                <option value="<?= $warehouse['id'] ?>" <?= old('warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                                    <?= esc($warehouse['name']) ?> - <?= esc($warehouse['location'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <!-- Material Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Material <span class="text-danger">*</span></label>
                                    <select name="material_id" id="material-select" class="form-select" required disabled>
                                        <option value="">-- Select Warehouse First --</option>
                                    </select>
                                    <div id="material-loading" class="text-muted small mt-1" style="display:none;">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Loading materials...
                                    </div>
                                </div>
                                
                                <!-- Quantity and Current Stock -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity to Receive <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity" id="quantity-input" class="form-control" min="0.01" step="0.01" value="<?= old('quantity') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Current Stock</label>
                                            <div id="current-stock-display" class="form-control bg-light text-center">
                                                <span class="text-muted">--</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">After Receipt</label>
                                            <div id="after-receipt-display" class="form-control bg-light text-center">
                                                <span class="text-muted">--</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Batch Number -->
                                <div class="mb-3">
                                    <label class="form-label">Batch Number</label>
                                    <input type="text" name="batch_number" class="form-control" placeholder="e.g., BATCH-2024-001" value="<?= old('batch_number') ?>">
                                </div>
                                
                                <!-- Reference -->
                                <div class="mb-3">
                                    <label class="form-label">Reference/PO Number</label>
                                    <input type="text" name="reference" class="form-control" placeholder="Purchase Order or Delivery Note Number" value="<?= old('reference') ?>">
                                </div>
                                
                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information about this receipt"><?= old('notes') ?></textarea>
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
    <script>
        const warehouseSelect = document.getElementById('warehouse-select');
        const materialSelect = document.getElementById('material-select');
        const materialLoading = document.getElementById('material-loading');
        const currentStockDisplay = document.getElementById('current-stock-display');
        const afterReceiptDisplay = document.getElementById('after-receipt-display');
        const quantityInput = document.getElementById('quantity-input');
        
        let currentMaterialData = null;
        
        // Load materials when warehouse is selected
        warehouseSelect.addEventListener('change', function() {
            const warehouseId = this.value;
            
            // Reset
            materialSelect.innerHTML = '<option value="">-- Select Material --</option>';
            materialSelect.disabled = true;
            currentStockDisplay.innerHTML = '<span class="text-muted">--</span>';
            afterReceiptDisplay.innerHTML = '<span class="text-muted">--</span>';
            currentMaterialData = null;
            
            if (!warehouseId) {
                materialSelect.innerHTML = '<option value="">-- Select Warehouse First --</option>';
                return;
            }
            
            materialLoading.style.display = 'block';
            
            fetch('<?= base_url('warehouse-staff/ajax/materials-by-warehouse') ?>?warehouse_id=' + warehouseId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                materialLoading.style.display = 'none';
                
                if (data.success && data.materials) {
                    materialSelect.disabled = false;
                    materialSelect.innerHTML = '<option value="">-- Select Material --</option>';
                    
                    data.materials.forEach(material => {
                        const currentQty = parseFloat(material.available_qty) || 0;
                        const unit = material.unit || 'units';
                        const option = document.createElement('option');
                        option.value = material.id;
                        option.dataset.currentQty = currentQty;
                        option.dataset.unit = unit;
                        option.textContent = `${material.name} (${material.code}) - Current: ${currentQty.toFixed(2)} ${unit}`;
                        materialSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                materialLoading.style.display = 'none';
                console.error('Error:', error);
            });
        });
        
        // Update displays when material is selected
        materialSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            
            if (this.value) {
                currentMaterialData = {
                    currentQty: parseFloat(selected.dataset.currentQty) || 0,
                    unit: selected.dataset.unit || 'units'
                };
                
                currentStockDisplay.innerHTML = `<strong>${currentMaterialData.currentQty.toFixed(2)}</strong> <small>${currentMaterialData.unit}</small>`;
                updateAfterReceipt();
            } else {
                currentMaterialData = null;
                currentStockDisplay.innerHTML = '<span class="text-muted">--</span>';
                afterReceiptDisplay.innerHTML = '<span class="text-muted">--</span>';
            }
        });
        
        // Update "After Receipt" when quantity changes
        quantityInput.addEventListener('input', updateAfterReceipt);
        
        function updateAfterReceipt() {
            if (currentMaterialData) {
                const receiveQty = parseFloat(quantityInput.value) || 0;
                const newTotal = currentMaterialData.currentQty + receiveQty;
                afterReceiptDisplay.innerHTML = `<strong class="text-success">${newTotal.toFixed(2)}</strong> <small>${currentMaterialData.unit}</small>`;
            } else {
                afterReceiptDisplay.innerHTML = '<span class="text-muted">--</span>';
            }
        }
    </script>
</body>
</html>
