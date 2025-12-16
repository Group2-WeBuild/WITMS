<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Materials Management') ?></title>
    
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
        .stat-card.info { border-left-color: #0dcaf0; }
        
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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Materials Management']) ?>

        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-boxes"></i> Materials Management</h2>
                        <p class="text-muted mb-0">Full CRUD operations for all materials</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMaterialModal">
                            <i class="bi bi-plus-circle"></i> Create Material
                        </button>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <div id="alertContainer"></div>

            <!-- Materials Stats -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <div class="icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <h3><?= number_format($stats['total_materials'] ?? 0) ?></h3>
                        <p>Total Materials</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card success">
                        <div class="icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <h3><?= number_format($stats['active_materials'] ?? 0) ?></h3>
                        <p>Active Materials</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <div class="icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h3><?= number_format($stats['total_categories'] ?? 0) ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <div class="stat-card info">
                        <div class="icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3><?= number_format($stats['recently_added'] ?? 0) ?></h3>
                        <p>Added This Month</p>
                    </div>
                </div>
            </div>

            <!-- Materials Table -->
            <div class="card shadow-sm table-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Materials List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="materialsTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Unit</th>
                                    <th>Unit Cost</th>
                                    <th>Reorder Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($materials)): ?>
                                    <?php foreach ($materials as $material): ?>
                                        <tr>
                                            <td><?= esc($material['id']) ?></td>
                                            <td><code><?= esc($material['code']) ?></code></td>
                                            <td>
                                                <strong><?= esc($material['name']) ?></strong>
                                                <?php if ($material['is_perishable']): ?>
                                                    <br><span class="badge bg-warning text-dark">Perishable</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($material['category_name']) ?></td>
                                            <td><?= esc($material['unit_name']) ?> (<?= esc($material['unit_abbreviation']) ?>)</td>
                                            <td>₱<?= number_format($material['unit_cost'], 2) ?></td>
                                            <td><?= number_format($material['reorder_level'], 2) ?></td>
                                            <td>
                                                <?php if ($material['is_active']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-warning edit-material-btn" 
                                                            data-id="<?= $material['id'] ?>" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger delete-material-btn" 
                                                            data-id="<?= $material['id'] ?>" 
                                                            data-name="<?= esc($material['name']) ?>" 
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-2">No materials found</p>
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

    <!-- Create Material Modal -->
    <div class="modal fade" id="createMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="createMaterialForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createName" class="form-label">Material Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="createName" required maxlength="200">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createCode" class="form-label">Material Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="createCode" required maxlength="50">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createCategoryId" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="createCategoryId" required>
                                    <option value="">-- Select Category --</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createUnitId" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                <select class="form-select" id="createUnitId" required>
                                    <option value="">-- Select Unit --</option>
                                    <?php if (!empty($units)): ?>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?> (<?= esc($unit['abbreviation']) ?>)</option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="createDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="createDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="createReorderLevel" class="form-label">Reorder Level <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="createReorderLevel" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="createReorderQuantity" class="form-label">Reorder Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="createReorderQuantity" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="createUnitCost" class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="createUnitCost" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="createIsPerishable">
                                    <label class="form-check-label" for="createIsPerishable">
                                        Is Perishable
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createShelfLifeDays" class="form-label">Shelf Life (Days)</label>
                                <input type="number" class="form-control" id="createShelfLifeDays" min="1" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Material Modal -->
    <div class="modal fade" id="editMaterialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMaterialForm">
                    <input type="hidden" id="editMaterialId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editName" class="form-label">Material Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editName" required maxlength="200">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editCode" class="form-label">Material Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editCode" required maxlength="50">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editCategoryId" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="editCategoryId" required>
                                    <option value="">-- Select Category --</option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editUnitId" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                                <select class="form-select" id="editUnitId" required>
                                    <option value="">-- Select Unit --</option>
                                    <?php if (!empty($units)): ?>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?> (<?= esc($unit['abbreviation']) ?>)</option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="editReorderLevel" class="form-label">Reorder Level <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editReorderLevel" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editReorderQuantity" class="form-label">Reorder Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editReorderQuantity" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="editUnitCost" class="form-label">Unit Cost <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editUnitCost" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="editIsPerishable">
                                    <label class="form-check-label" for="editIsPerishable">
                                        Is Perishable
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editShelfLifeDays" class="form-label">Shelf Life (Days)</label>
                                <input type="number" class="form-control" id="editShelfLifeDays" min="1" disabled>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editIsActive" checked>
                                <label class="form-check-label" for="editIsActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Material</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMaterialModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger"></i> Delete Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this material?</p>
                    <p class="mb-0"><strong id="deleteMaterialName"></strong></p>
                    <p class="text-danger mt-2"><small>This action cannot be undone. Material must not be used in any inventory items.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMaterialBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#materialsTable').DataTable({
                order: [[1, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search materials:",
                    lengthMenu: "Show _MENU_ materials per page"
                },
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
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

            // Enable/disable shelf life days based on perishable checkbox
            $('#createIsPerishable, #editIsPerishable').on('change', function() {
                var isPerishable = $(this).is(':checked');
                var shelfLifeInput = $(this).closest('.row').find('input[type="number"]');
                shelfLifeInput.prop('disabled', !isPerishable);
                if (!isPerishable) {
                    shelfLifeInput.val('');
                }
            });

            // Create Material
            $('#createMaterialForm').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    name: $('#createName').val(),
                    code: $('#createCode').val(),
                    category_id: $('#createCategoryId').val(),
                    unit_id: $('#createUnitId').val(),
                    description: $('#createDescription').val() || null,
                    reorder_level: $('#createReorderLevel').val(),
                    reorder_quantity: $('#createReorderQuantity').val(),
                    unit_cost: $('#createUnitCost').val(),
                    is_perishable: $('#createIsPerishable').is(':checked'),
                    shelf_life_days: $('#createIsPerishable').is(':checked') ? $('#createShelfLifeDays').val() || null : null
                };

                $.ajax({
                    url: '<?= base_url('it-administrator/materials/create') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#createMaterialModal').modal('hide');
                            $('#createMaterialForm')[0].reset();
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to create material', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to create material';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });

            // Edit Material
            $(document).on('click', '.edit-material-btn', function() {
                var materialId = $(this).data('id');
                
                $.ajax({
                    url: '<?= base_url('it-administrator/materials/get') ?>',
                    type: 'POST',
                    data: { material_id: materialId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var mat = response.material;
                            $('#editMaterialId').val(mat.id);
                            $('#editName').val(mat.name);
                            $('#editCode').val(mat.code);
                            $('#editCategoryId').val(mat.category_id);
                            $('#editUnitId').val(mat.unit_id);
                            $('#editDescription').val(mat.description || '');
                            $('#editReorderLevel').val(mat.reorder_level);
                            $('#editReorderQuantity').val(mat.reorder_quantity);
                            $('#editUnitCost').val(mat.unit_cost);
                            $('#editIsPerishable').prop('checked', mat.is_perishable == 1 || mat.is_perishable === true);
                            $('#editShelfLifeDays').val(mat.shelf_life_days || '');
                            $('#editShelfLifeDays').prop('disabled', !$('#editIsPerishable').is(':checked'));
                            $('#editIsActive').prop('checked', mat.is_active == 1 || mat.is_active === true);
                            $('#editMaterialModal').modal('show');
                        } else {
                            showAlert(response.message || 'Failed to load material', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Failed to load material', 'danger');
                    }
                });
            });

            $('#editMaterialForm').on('submit', function(e) {
                e.preventDefault();
                var formData = {
                    material_id: $('#editMaterialId').val(),
                    name: $('#editName').val(),
                    code: $('#editCode').val(),
                    category_id: $('#editCategoryId').val(),
                    unit_id: $('#editUnitId').val(),
                    description: $('#editDescription').val() || null,
                    reorder_level: $('#editReorderLevel').val(),
                    reorder_quantity: $('#editReorderQuantity').val(),
                    unit_cost: $('#editUnitCost').val(),
                    is_perishable: $('#editIsPerishable').is(':checked'),
                    shelf_life_days: $('#editIsPerishable').is(':checked') ? $('#editShelfLifeDays').val() || null : null,
                    is_active: $('#editIsActive').is(':checked')
                };

                $.ajax({
                    url: '<?= base_url('it-administrator/materials/update') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#editMaterialModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to update material', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to update material';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });

            // Delete Material
            $(document).on('click', '.delete-material-btn', function() {
                var materialId = $(this).data('id');
                var materialName = $(this).data('name');
                $('#deleteMaterialName').text(materialName);
                $('#deleteMaterialModal').data('material-id', materialId);
                $('#deleteMaterialModal').modal('show');
            });

            $('#confirmDeleteMaterialBtn').on('click', function() {
                var materialId = $('#deleteMaterialModal').data('material-id');
                
                $.ajax({
                    url: '<?= base_url('it-administrator/materials/delete') ?>',
                    type: 'POST',
                    data: { material_id: materialId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(response.message, 'success');
                            $('#deleteMaterialModal').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert(response.message || 'Failed to delete material', 'danger');
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to delete material';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert(errorMsg, 'danger');
                    }
                });
            });
        });
    </script>
</body>
</html>

