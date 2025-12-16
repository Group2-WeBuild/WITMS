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
                                <h6 class="mb-2"><i class="bi bi-info-circle text-warning me-2"></i>Issue Stock Workflow</h6>
                                <ol class="mb-0 small">
                                    <li>Select the material to issue from inventory</li>
                                    <li>Choose the source warehouse</li>
                                    <li>Enter quantity (must not exceed available stock)</li>
                                    <li>Select the department receiving the materials</li>
                                </ol>
                            </div>
                            
                            <form id="issue-stock-form" method="post" action="<?= base_url('warehouse-staff/issue/process') ?>">
                                <?= csrf_field() ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <!-- Warehouse -->
                                        <div class="mb-3">
                                            <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                                            <select name="warehouse_id" id="warehouse-select" class="form-select" required>
                                                <option value="">Select Warehouse First</option>
                                                <?php if (isset($warehouses)): ?>
                                                    <?php foreach ($warehouses as $warehouse): ?>
                                                        <option value="<?= $warehouse['id'] ?>" <?= old('warehouse_id') == $warehouse['id'] ? 'selected' : '' ?>>
                                                            <?= esc($warehouse['name']) ?> - <?= esc($warehouse['location'] ?? '') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- Issue To -->
                                        <div class="mb-3">
                                            <label class="form-label">Issue To Department <span class="text-danger">*</span></label>
                                            <select name="issued_to" class="form-select" required>
                                                <option value="">Select Department</option>
                                                <?php if (isset($departments)): ?>
                                                    <?php foreach ($departments as $department): ?>
                                                        <option value="<?= esc($department['name']) ?>" <?= old('issued_to') == $department['name'] ? 'selected' : '' ?>>
                                                            <?= esc($department['name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
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
                                
                                <!-- Quantity with Available Stock Display -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" name="quantity" id="quantity-input" class="form-control" min="0.01" step="0.01" value="<?= old('quantity') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Available Stock</label>
                                            <div id="available-stock-display" class="form-control bg-light text-center">
                                                <span class="text-muted">Select material first</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Reference -->
                                <div class="mb-3">
                                    <label class="form-label">Requisition Number</label>
                                    <div class="input-group">
                                        <input type="text" name="reference" id="requisition-number" class="form-control" placeholder="Auto-generated" value="<?= old('reference') ?>" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="generate-requisition-btn" title="Generate new requisition number">
                                            <i class="bi bi-arrow-clockwise"></i> Generate
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Requisition number is auto-generated. Click Generate to create a new one.</small>
                                </div>
                                
                                <!-- Notes -->
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Purpose or additional details"><?= old('notes') ?></textarea>
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
        const warehouseSelect = document.getElementById('warehouse-select');
        const materialSelect = document.getElementById('material-select');
        const materialLoading = document.getElementById('material-loading');
        const availableStockDisplay = document.getElementById('available-stock-display');
        const quantityInput = document.getElementById('quantity-input');
        
        // Load materials when warehouse is selected
        warehouseSelect.addEventListener('change', function() {
            const warehouseId = this.value;
            
            // Reset material select
            materialSelect.innerHTML = '<option value="">-- Select Material --</option>';
            materialSelect.disabled = true;
            availableStockDisplay.innerHTML = '<span class="text-muted">Select material first</span>';
            quantityInput.removeAttribute('max');
            
            if (!warehouseId) {
                materialSelect.innerHTML = '<option value="">-- Select Warehouse First --</option>';
                return;
            }
            
            // Show loading
            materialLoading.style.display = 'block';
            
            // Fetch materials for this warehouse
            fetch('<?= base_url('warehouse-staff/ajax/materials-by-warehouse') ?>?warehouse_id=' + warehouseId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
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
                        
                        // Highlight if no stock
                        if (available <= 0) {
                            option.textContent += ' [OUT OF STOCK]';
                            option.classList.add('text-danger');
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
                    availableStockDisplay.innerHTML = '<strong class="text-danger">0.00</strong> <small>OUT OF STOCK</small>';
                    quantityInput.max = 0;
                }
            } else {
                availableStockDisplay.innerHTML = '<span class="text-muted">Select material first</span>';
                quantityInput.removeAttribute('max');
            }
        });
        
        // Validate quantity on form submit
        document.getElementById('issue-stock-form').addEventListener('submit', function(e) {
            const selected = materialSelect.options[materialSelect.selectedIndex];
            const available = parseFloat(selected.dataset.available) || 0;
            const quantity = parseFloat(quantityInput.value) || 0;
            
            if (quantity > available) {
                e.preventDefault();
                alert('Quantity cannot exceed available stock (' + available.toFixed(2) + ')');
                quantityInput.focus();
            }
        });

        // Generate requisition number on page load and when button is clicked
        const requisitionNumberInput = document.getElementById('requisition-number');
        const generateRequisitionBtn = document.getElementById('generate-requisition-btn');
        
        function generateRequisitionNumber() {
            generateRequisitionBtn.disabled = true;
            generateRequisitionBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch('<?= base_url('warehouse-staff/ajax/generate-requisition') ?>', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                generateRequisitionBtn.disabled = false;
                generateRequisitionBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Generate';
                
                if (data.success && data.requisition_number) {
                    requisitionNumberInput.value = data.requisition_number;
                } else {
                    alert('Error generating requisition number: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                generateRequisitionBtn.disabled = false;
                generateRequisitionBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Generate';
                console.error('Error generating requisition number:', error);
                alert('Error generating requisition number. Please try again.');
            });
        }
        
        // Generate on page load
        generateRequisitionNumber();
        
        // Generate on button click
        generateRequisitionBtn.addEventListener('click', generateRequisitionNumber);
    </script>
</body>
</html>
