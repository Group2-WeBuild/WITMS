<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Inventory Management') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 120px;
            position: relative;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .stat-card h3 {
            font-size: 28px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        
        .table-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar Navigation -->
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Inventory Management']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-box-seam"></i> Inventory Management</h2>
                        <p class="text-muted mb-0">Full CRUD operations for all warehouse inventory</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInventoryModal">
                            <i class="bi bi-plus-circle"></i> Add Stock
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Inventory Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <div class="icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h3><?= number_format($stats['total_items'] ?? 0) ?></h3>
                        <p>Total Items</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card success">
                        <div class="icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h3>₱<?= number_format($stats['total_value'] ?? 0, 2) ?></h3>
                        <p>Total Value</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <div class="icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h3><?= number_format($stats['low_stock'] ?? 0) ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card danger">
                        <div class="icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h3><?= number_format($stats['expiring'] ?? 0) ?></h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="card shadow-sm table-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Inventory List</h5>
                </div>
                <div class="card-body">
                    <!-- Search and Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Search Inventory</label>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by material name or code...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Warehouse</label>
                            <select id="warehouseFilter" class="form-select">
                                <option value="">All Warehouses</option>
                                <?php if (!empty($warehouses)): ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= esc($warehouse['name']) ?>"><?= esc($warehouse['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Category</label>
                            <select id="categoryFilter" class="form-select">
                                <option value="">All Categories</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= esc($category['name']) ?>"><?= esc($category['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="In Stock">In Stock</option>
                                <option value="Low Stock">Low Stock</option>
                                <option value="Out of Stock">Out of Stock</option>
                                <option value="Expiring">Expiring</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button id="clearFilters" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle"></i> Clear Filters
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="inventoryTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Material</th>
                                    <th>Category</th>
                                    <th>Warehouse</th>
                                    <th>Quantity</th>
                                    <th>Reserved</th>
                                    <th>Available</th>
                                    <th>Unit</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($inventory)): ?>
                                    <?php foreach ($inventory as $item): ?>
                                        <tr>
                                            <td><?= esc($item['id']) ?></td>
                                            <td>
                                                <strong><?= esc($item['material_name']) ?></strong><br>
                                                <small class="text-muted"><?= esc($item['material_code']) ?></small>
                                            </td>
                                            <td><?= esc($item['category_name']) ?></td>
                                            <td><?= esc($item['warehouse_name']) ?></td>
                                            <td><strong><?= number_format($item['quantity'], 2) ?></strong></td>
                                            <td>
                                                <?php 
                                                $reserved = $item['reserved_quantity'] ?? 0;
                                                if ($reserved > 0): 
                                                ?>
                                                    <span class="text-warning"><?= number_format($reserved, 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= number_format($reserved, 2) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $quantity = floatval($item['quantity'] ?? 0);
                                                $reserved = floatval($item['reserved_quantity'] ?? 0);
                                                if ($reserved > $quantity) {
                                                    $reserved = $quantity;
                                                }
                                                $calculatedAvailable = max(0, $quantity - $reserved);
                                                ?>
                                                <strong class="text-primary">
                                                    <?= number_format($calculatedAvailable, 2) ?>
                                                </strong>
                                            </td>
                                            <td><?= esc($item['unit_abbr'] ?? 'N/A') ?></td>
                                            <td><?= esc($item['location_in_warehouse'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $quantity = floatval($item['quantity'] ?? 0);
                                                $reserved = floatval($item['reserved_quantity'] ?? 0);
                                                if ($reserved > $quantity) {
                                                    $reserved = $quantity;
                                                }
                                                $availableStock = max(0, $quantity - $reserved);
                                                $reorderLevel = $item['reorder_level'] ?? 0;
                                                
                                                if ($availableStock <= 0):
                                                ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php elseif ($availableStock <= $reorderLevel): ?>
                                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-warning edit-inventory-btn" 
                                                            data-id="<?= $item['id'] ?>" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger delete-inventory-btn" 
                                                            data-id="<?= $item['id'] ?>" 
                                                            data-name="<?= esc($item['material_name']) ?>" 
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-2">No inventory items found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Inventory Modal -->
    <div class="modal fade" id="createInventoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Stock to Inventory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createInventoryForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="createMaterialId" class="form-label">Material <span class="text-danger">*</span></label>
                            <select class="form-select" id="createMaterialId" required>
                                <option value="">-- Select Material --</option>
                                <?php if (!empty($materials)): ?>
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?= $material['id'] ?>"><?= esc($material['name']) ?> (<?= esc($material['code']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="createWarehouseId" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="createWarehouseId" required>
                                <option value="">-- Select Warehouse --</option>
                                <?php if (!empty($warehouses)): ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>"><?= esc($warehouse['name']) ?> (<?= esc($warehouse['code']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="createQuantity" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createReservedQuantity" class="form-label">Reserved Quantity</label>
                                <input type="number" class="form-control" id="createReservedQuantity" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createBatchNumber" class="form-label">Batch Number</label>
                                <input type="text" class="form-control" id="createBatchNumber" maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createLocation" class="form-label">Location in Warehouse</label>
                                <input type="text" class="form-control" id="createLocation" maxlength="100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="createExpirationDate" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="createExpirationDate">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div class="modal fade" id="editInventoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editInventoryForm">
                    <input type="hidden" id="editInventoryId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editMaterialId" class="form-label">Material <span class="text-danger">*</span></label>
                            <select class="form-select" id="editMaterialId" required>
                                <option value="">-- Select Material --</option>
                                <?php if (!empty($materials)): ?>
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?= $material['id'] ?>"><?= esc($material['name']) ?> (<?= esc($material['code']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editWarehouseId" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="editWarehouseId" required>
                                <option value="">-- Select Warehouse --</option>
                                <?php if (!empty($warehouses)): ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>"><?= esc($warehouse['name']) ?> (<?= esc($warehouse['code']) ?>)</option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editQuantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editQuantity" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editReservedQuantity" class="form-label">Reserved Quantity</label>
                                <input type="number" class="form-control" id="editReservedQuantity" step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editBatchNumber" class="form-label">Batch Number</label>
                                <input type="text" class="form-control" id="editBatchNumber" maxlength="50">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editLocation" class="form-label">Location in Warehouse</label>
                                <input type="text" class="form-control" id="editLocation" maxlength="100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editExpirationDate" class="form-label">Expiration Date</label>
                            <input type="date" class="form-control" id="editExpirationDate">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Inventory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteInventoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Delete Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this inventory item?</p>
                    <p class="mb-0"><strong id="deleteInventoryName"></strong></p>
                    <p class="text-danger mt-2"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#inventoryTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search inventory:",
                    lengthMenu: "Show _MENU_ items per page"
                },
                initComplete: function() {
                    $('.dataTables_filter').hide();
                }
            });

            // Show alert function
            function showAlert(message, type) {
                var alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alertContainer').html(alertHtml);
                setTimeout(function() {
                    $('.alert').fadeOut();
                }, 5000);
            }

            // Create Inventory
            $('#createInventoryForm').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    material_id: $('#createMaterialId').val(),
                    warehouse_id: $('#createWarehouseId').val(),
                    quantity: $('#createQuantity').val(),
                    reserved_quantity: $('#createReservedQuantity').val() || 0,
                    batch_number: $('#createBatchNumber').val() || null,
                    location_in_warehouse: $('#createLocation').val() || null,
                    expiration_date: $('#createExpirationDate').val() || null
                };

                $.ajax({
                    url: '<?= base_url('it-administrator/inventory/create') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#createInventoryModal').modal('hide');
                            $('#createInventoryForm')[0].reset();
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to create inventory item', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to create inventory item';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });

            // Edit Inventory
            $(document).on('click', '.edit-inventory-btn', function() {
                var inventoryId = $(this).data('id');
                
                $.ajax({
                    url: '<?= base_url('it-administrator/inventory/get') ?>',
                    type: 'POST',
                    data: { inventory_id: inventoryId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var inv = response.inventory;
                            $('#editInventoryId').val(inv.id);
                            $('#editMaterialId').val(inv.material_id);
                            $('#editWarehouseId').val(inv.warehouse_id);
                            $('#editQuantity').val(inv.quantity);
                            $('#editReservedQuantity').val(inv.reserved_quantity || 0);
                            $('#editBatchNumber').val(inv.batch_number || '');
                            $('#editLocation').val(inv.location_in_warehouse || '');
                            if (inv.expiration_date) {
                                var expDate = new Date(inv.expiration_date);
                                $('#editExpirationDate').val(expDate.toISOString().split('T')[0]);
                            } else {
                                $('#editExpirationDate').val('');
                            }
                            $('#editInventoryModal').modal('show');
                        } else {
                            showAlert(response.message || 'Failed to load inventory item', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Failed to load inventory item', 'danger');
                    }
                });
            });

            $('#editInventoryForm').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    inventory_id: $('#editInventoryId').val(),
                    material_id: $('#editMaterialId').val(),
                    warehouse_id: $('#editWarehouseId').val(),
                    quantity: $('#editQuantity').val(),
                    reserved_quantity: $('#editReservedQuantity').val() || 0,
                    batch_number: $('#editBatchNumber').val() || null,
                    location_in_warehouse: $('#editLocation').val() || null,
                    expiration_date: $('#editExpirationDate').val() || null
                };

                $.ajax({
                    url: '<?= base_url('it-administrator/inventory/update') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#editInventoryModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to update inventory item', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to update inventory item';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });

            // Delete Inventory
            $(document).on('click', '.delete-inventory-btn', function() {
                var inventoryId = $(this).data('id');
                var inventoryName = $(this).data('name');
                $('#deleteInventoryName').text(inventoryName);
                $('#deleteInventoryModal').data('inventory-id', inventoryId);
                $('#deleteInventoryModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                var inventoryId = $('#deleteInventoryModal').data('inventory-id');
                
                $.ajax({
                    url: '<?= base_url('it-administrator/inventory/delete') ?>',
                    type: 'POST',
                    data: { inventory_id: inventoryId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#deleteInventoryModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to delete inventory item', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to delete inventory item';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });

            // Filter functionality
            function filterTable() {
                var searchTerm = $('#searchInput').val().toLowerCase();
                var warehouseFilter = $('#warehouseFilter').val().toLowerCase();
                var categoryFilter = $('#categoryFilter').val().toLowerCase();
                var statusFilter = $('#statusFilter').val();

                table.rows().every(function() {
                    var row = this.data();
                    var rowNode = this.node();
                    var materialName = $(row[1]).text().toLowerCase();
                    var materialCode = $(row[1]).find('small').text().toLowerCase();
                    var warehouse = row[3].toLowerCase();
                    var category = row[2].toLowerCase();
                    var status = $(rowNode).find('.badge').text().trim();

                    var matchesSearch = materialName.includes(searchTerm) || materialCode.includes(searchTerm);
                    var matchesWarehouse = !warehouseFilter || warehouse === warehouseFilter;
                    var matchesCategory = !categoryFilter || category === categoryFilter;
                    var matchesStatus = !statusFilter || status === statusFilter;

                    if (matchesSearch && matchesWarehouse && matchesCategory && matchesStatus) {
                        this.nodes().to$().show();
                    } else {
                        this.nodes().to$().hide();
                    }
                });
            }

            $('#searchInput').on('keyup', filterTable);
            $('#warehouseFilter').on('change', filterTable);
            $('#categoryFilter').on('change', filterTable);
            $('#statusFilter').on('change', filterTable);

            $('#clearFilters').on('click', function() {
                $('#searchInput').val('');
                $('#warehouseFilter').val('');
                $('#categoryFilter').val('');
                $('#statusFilter').val('');
                filterTable();
            });
        });
    </script>
</body>
</html>

