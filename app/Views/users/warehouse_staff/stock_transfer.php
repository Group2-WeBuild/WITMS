<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer - WITMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Stock Transfer']) ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-arrow-left-right"></i> Stock Transfer</h5>
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
                                <h6 class="mb-2"><i class="bi bi-info-circle text-info me-2"></i>Stock Transfer Workflow</h6>
                                <ol class="mb-0 small">
                                    <li>Select source and destination warehouses</li>
                                    <li>Choose the material to transfer</li>
                                    <li>Enter quantity (must be available in source warehouse)</li>
                                    <li>Add reference number and reason for tracking</li>
                                </ol>
                            </div>
                            
                            <form method="post" action="<?= base_url('warehouse-staff/transfer/process') ?>">
                                <?= csrf_field() ?>
                                
                                <!-- Warehouse Selection Row -->
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                        <select name="from_warehouse_id" id="from-warehouse" class="form-select" required>
                                            <option value="">Select Source</option>
                                            <?php if (isset($warehouses)): ?>
                                                <?php foreach ($warehouses as $warehouse): ?>
                                                    <option value="<?= $warehouse['id'] ?>" <?= old('from_warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                                        <?= esc($warehouse['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end justify-content-center pb-2">
                                        <i class="bi bi-arrow-right fs-4 text-info"></i>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                                        <select name="to_warehouse_id" id="to-warehouse" class="form-select" required>
                                            <option value="">Select Destination</option>
                                            <?php if (isset($allWarehouses)): ?>
                                                <?php foreach ($allWarehouses as $warehouse): ?>
                                                    <option value="<?= $warehouse['id'] ?>" <?= old('to_warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                                        <?= esc($warehouse['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    
                                    </div>
                                </div>
                                
                                <!-- Material Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Material <span class="text-danger">*</span></label>
                                    <select name="material_id" id="material-select" class="form-select" required disabled>
                                        <option value="">-- Select Source Warehouse First --</option>
                                    </select>
                                    <div id="material-loading" class="text-muted small mt-1" style="display:none;">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Loading materials...
                                    </div>
                                </div>
                                
                                <!-- Quantity with Available Stock Display -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity" id="quantity-input" class="form-control" min="0.01" step="0.01" value="<?= old('quantity') ?>" required>
                                            <small class="text-muted">Must be greater than 0</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Available in Source</label>
                                            <div id="available-stock-display" class="form-control bg-light text-center">
                                                <span class="text-muted">Select material first</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Transfer Reference Number</label>
                                    <input type="text" name="reference" id="transfer-reference" class="form-control" placeholder="Will be auto-generated on save" readonly>
                                    <small class="text-muted">Transfer reference number is auto-generated.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Reason for Transfer</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Stock balancing, Project requirement, etc."><?= old('notes') ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-info">
                                        <i class="bi bi-check-circle"></i> Transfer Stock
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
        const fromWarehouse = document.getElementById('from-warehouse');
        const toWarehouse = document.getElementById('to-warehouse');
        const materialSelect = document.getElementById('material-select');
        const materialLoading = document.getElementById('material-loading');
        const availableStockDisplay = document.getElementById('available-stock-display');
        const quantityInput = document.getElementById('quantity-input');
        
        // Load materials when source warehouse is selected
        fromWarehouse.addEventListener('change', function() {
            const warehouseId = this.value;
            
            // Prevent same warehouse selection
            Array.from(toWarehouse.options).forEach(opt => opt.disabled = false);
            if (warehouseId) {
                const matchingOption = toWarehouse.querySelector(`option[value="${warehouseId}"]`);
                if (matchingOption) matchingOption.disabled = true;
            }
            
            // Reset material select
            materialSelect.innerHTML = '<option value="">-- Select Material --</option>';
            materialSelect.disabled = true;
            availableStockDisplay.innerHTML = '<span class="text-muted">Select material first</span>';
            quantityInput.removeAttribute('max');
            
            if (!warehouseId) {
                materialSelect.innerHTML = '<option value="">-- Select Source Warehouse First --</option>';
                return;
            }
            
            // Show loading
            materialLoading.style.display = 'block';
            
            // Fetch materials for source warehouse
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
                        const available = parseFloat(material.available_qty) || 0;
                        const unit = material.unit || 'units';
                        const option = document.createElement('option');
                        option.value = material.id;
                        option.dataset.available = available;
                        option.dataset.unit = unit;
                        option.textContent = `${material.name} (${material.code}) - Available: ${available.toFixed(2)} ${unit}`;
                        
                        if (available <= 0) {
                            option.textContent += ' [NO STOCK]';
                            option.style.color = '#dc3545';
                        }
                        
                        materialSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                materialLoading.style.display = 'none';
                console.error('Error loading materials:', error);
            });
        });
        
        // Prevent same warehouse for destination
        toWarehouse.addEventListener('change', function() {
            const selectedValue = this.value;
            Array.from(fromWarehouse.options).forEach(opt => opt.disabled = false);
            if (selectedValue) {
                const matchingOption = fromWarehouse.querySelector(`option[value="${selectedValue}"]`);
                if (matchingOption) matchingOption.disabled = true;
            }
        });
        
        // Update available stock display when material is selected
        materialSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const available = parseFloat(selected.dataset.available) || 0;
            const unit = selected.dataset.unit || 'units';
            
            if (this.value) {
                if (available > 0) {
                    availableStockDisplay.innerHTML = `<strong class="text-success">${available.toFixed(2)}</strong> <small>${unit}</small>`;
                    quantityInput.max = available;
                } else {
                    availableStockDisplay.innerHTML = '<strong class="text-danger">0.00</strong> <small>NO STOCK</small>';
                    quantityInput.max = 0;
                }
            } else {
                availableStockDisplay.innerHTML = '<span class="text-muted">Select material first</span>';
                quantityInput.removeAttribute('max');
            }
        });
        
        // Validate quantity cannot be negative
        quantityInput.addEventListener('change', function() {
            const value = parseFloat(this.value);
            if (value <= 0 || isNaN(value)) {
                alert('Quantity must be greater than 0');
                this.value = '';
                this.focus();
            }
        });
        
        // Validate quantity on form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const quantity = parseFloat(quantityInput.value) || 0;
            
            // Check if quantity is negative or zero
            if (quantity <= 0 || isNaN(quantity)) {
                e.preventDefault();
                alert('Quantity must be greater than 0');
                quantityInput.focus();
                return false;
            }
            
            // Check if quantity exceeds available stock
            if (materialSelect.value) {
                const selected = materialSelect.options[materialSelect.selectedIndex];
                const available = parseFloat(selected.dataset.available) || 0;
                
                if (quantity > available) {
                    e.preventDefault();
                    alert('Quantity cannot exceed available stock (' + available.toFixed(2) + ')');
                    quantityInput.focus();
                    return false;
                }
            }
        });
    </script>
</body>
</html>
