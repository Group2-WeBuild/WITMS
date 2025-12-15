<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Adjust Inventory') ?></title>
    
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
        .adjustment-card {
            border-left: 4px solid #ffc107;
        }
        .quantity-display {
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Adjust Inventory']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-arrow-left-right"></i> Adjust Inventory Quantity</h1>
                <a href="<?= base_url('warehouse-manager/inventory') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>

            <!-- Current Item Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Item Information</h5>
                            <p class="mb-1"><strong>Material:</strong> <?= esc($inventory['material_name'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong>Warehouse:</strong> <?= esc($inventory['warehouse_name'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong>Batch Number:</strong> <?= esc($inventory['batch_number'] ?? 'N/A') ?></p>
                            <p class="mb-1"><strong>Location:</strong> <?= esc($inventory['location_in_warehouse'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Current Quantity</h5>
                            <div class="quantity-display"><?= number_format($inventory['quantity'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adjustment Form -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card adjustment-card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Inventory Adjustment</h5>
                        </div>
                        <div class="card-body">
                            <form id="adjustmentForm">
                                <input type="hidden" name="inventory_id" value="<?= $inventory['id'] ?>">
                                
                                <!-- Adjustment Type -->
                                <div class="mb-3">
                                    <label for="adjustment_type" class="form-label">Adjustment Type</label>
                                    <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                                        <option value="increase">Increase Quantity</option>
                                        <option value="decrease">Decrease Quantity</option>
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity to Adjust</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quantity" name="quantity" 
                                               step="1" min="1" value="1" required>
                                        <span class="input-group-text">units</span>
                                    </div>
                                    <div class="form-text">
                                        <span id="newQuantityDisplay">New quantity will be: <strong><?= number_format($inventory['quantity'] ?? 0, 0) ?></strong></span>
                                    </div>
                                </div>

                                <!-- Reason -->
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Adjustment</label>
                                    <select class="form-select" id="reason" name="reason" required>
                                        <option value="">Select a reason</option>
                                        <option value="Stock Count Correction">Stock Count Correction</option>
                                        <option value="Damage/Loss">Damage/Loss</option>
                                        <option value="Theft">Theft</option>
                                        <option value="Return from Customer">Return from Customer</option>
                                        <option value="System Error">System Error</option>
                                        <option value="Found Stock">Found Stock</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <!-- Notes -->
                                <div class="mb-4">
                                    <label for="notes" class="form-label">Additional Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Provide additional details about this adjustment..."></textarea>
                                </div>

                                <!-- Warning Message -->
                                <div class="alert alert-warning d-none" id="warningMessage">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <span id="warningText"></span>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="bi bi-check-circle"></i> Process Adjustment
                                    </button>
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
        const currentQuantity = <?= $inventory['quantity'] ?? 0 ?>;
        const quantityInput = document.getElementById('quantity');
        const newQuantityDisplay = document.getElementById('newQuantityDisplay');
        const warningMessage = document.getElementById('warningMessage');
        const warningText = document.getElementById('warningText');
        const adjustmentTypeSelect = document.getElementById('adjustment_type');

        function updateNewQuantity() {
            const quantity = parseInt(quantityInput.value) || 0;
            const adjustmentType = adjustmentTypeSelect.value;
            let newQuantity;

            if (adjustmentType === 'increase') {
                newQuantity = currentQuantity + quantity;
            } else {
                newQuantity = currentQuantity - quantity;
                if (newQuantity < 0) {
                    warningMessage.classList.remove('d-none');
                    warningText.textContent = `Warning: This will result in negative quantity (${newQuantity})`;
                } else {
                    warningMessage.classList.add('d-none');
                }
            }

            newQuantityDisplay.innerHTML = `New quantity will be: <strong>${newQuantity}</strong>`;
        }

        quantityInput.addEventListener('input', updateNewQuantity);
        adjustmentTypeSelect.addEventListener('change', updateNewQuantity);

        document.getElementById('adjustmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const inventoryId = formData.get('inventory_id');
            
            // Confirm before processing
            if (!confirm('Are you sure you want to process this inventory adjustment? This action cannot be undone.')) {
                return;
            }

            fetch(`<?= base_url('warehouse-manager/inventory/adjust-process/') ?>${inventoryId}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Inventory adjusted successfully!');
                    window.location.href = '<?= base_url('warehouse-manager/inventory') ?>';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing the adjustment.');
            });
        });
    </script>
</body>
</html>
