<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Add Stock') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Include Mobile Styles -->
    <?= view('templates/mobile_styles') ?>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Add Stock']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3"><i class="bi bi-plus-circle"></i> Add Stock to Inventory</h1>
                <a href="<?= base_url('warehouse-manager/inventory') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>

            <!-- Add Stock Form -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock Information</h5>
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

                            <form action="<?= base_url('warehouse-manager/inventory/store') ?>" method="post" id="addStockForm">
                                <?= csrf_field() ?>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Material <span class="text-danger">*</span></label>
                                        <select name="material_id" id="material_id" class="form-select" required>
                                            <option value="">Select Material</option>
                                            <?php if (!empty($materials)): ?>
                                                <?php foreach ($materials as $material): ?>
                                                    <option value="<?= $material['id'] ?>" 
                                                            data-code="<?= esc($material['code']) ?>"
                                                            data-name="<?= esc($material['name']) ?>">
                                                        <?= esc($material['name']) ?> (<?= esc($material['code']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <div id="materialQuantityInfo" class="mt-2" style="display: none;">
                                            <small class="text-info">
                                                <i class="bi bi-info-circle"></i> 
                                                <span id="materialQuantityText"></span>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                        <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                                            <option value="">Select Warehouse</option>
                                            <?php if (!empty($warehouses)): ?>
                                                <?php foreach ($warehouses as $warehouse): ?>
                                                    <option value="<?= $warehouse['id'] ?>" data-code="<?= esc($warehouse['code']) ?>">
                                                        <?= esc($warehouse['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity" class="form-control" step="0.01" min="0.01" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Batch Number</label>
                                        <input type="text" name="batch_number" class="form-control" placeholder="Will be auto-generated on save" readonly>
                                        <small class="text-muted">Batch number is auto-generated.</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Location in Warehouse</label>
                                        <input type="text" name="location_in_warehouse" class="form-control" placeholder="e.g., Aisle A, Rack 5">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expiration Date</label>
                                        <input type="date" name="expiration_date" id="expiration_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                        <small class="text-muted">For perishable items only. Must be a future date.</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about this stock..."></textarea>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle"></i> Add Stock
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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Set minimum date for expiration date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = tomorrow.toISOString().split('T')[0];
            $('#expiration_date').attr('min', minDate);
            
            // Validate expiration date on change
            $('#expiration_date').on('change', function() {
                const selectedDate = new Date($(this).val());
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if ($(this).val() && selectedDate <= today) {
                    alert('Expiration date must be in the future. Please select a future date.');
                    $(this).val('');
                    $(this).focus();
                }
            });
            
            // Fetch material quantity when material and warehouse are selected
            function fetchMaterialQuantity() {
                const materialId = $('#material_id').val();
                const warehouseId = $('#warehouse_id').val();
                const materialQuantityInfo = $('#materialQuantityInfo');
                const materialQuantityText = $('#materialQuantityText');
                
                if (!materialId || !warehouseId) {
                    materialQuantityInfo.hide();
                    return;
                }
                
                // Show loading
                materialQuantityText.html('<i class="bi bi-hourglass-split"></i> Loading quantity...');
                materialQuantityInfo.show();
                
                $.ajax({
                    url: '<?= base_url('warehouse-manager/inventory/get-material-quantity') ?>',
                    method: 'POST',
                    data: {
                        material_id: materialId,
                        warehouse_id: warehouseId
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            const materialName = $('#material_id option:selected').data('name') || 'Material';
                            const quantity = parseFloat(response.quantity || 0);
                            const availableQty = parseFloat(response.available_quantity || 0);
                            const unit = response.unit || '';
                            
                            let text = `<strong>${materialName}</strong> - `;
                            text += `Total Quantity: <strong>${quantity.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${unit}</strong>`;
                            if (availableQty !== quantity) {
                                text += ` | Available: <strong>${availableQty.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${unit}</strong>`;
                            }
                            
                            materialQuantityText.html(text);
                        } else {
                            materialQuantityText.html('<span class="text-muted">No existing inventory found for this material in this warehouse.</span>');
                        }
                    },
                    error: function() {
                        materialQuantityText.html('<span class="text-danger">Error loading quantity information.</span>');
                    }
                });
            }
            
            // Trigger quantity fetch when material or warehouse changes
            $('#material_id, #warehouse_id').on('change', fetchMaterialQuantity);
            
            // Form validation for expiration date
            $('#addStockForm').on('submit', function(e) {
                const expirationDate = $('#expiration_date').val();
                
                if (expirationDate) {
                    const selectedDate = new Date(expirationDate);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate <= today) {
                        e.preventDefault();
                        alert('Expiration date must be in the future. Please select a future date or leave it empty.');
                        $('#expiration_date').focus();
                        return false;
                    }
                }
            });
        });
    </script>
</body>
</html>
