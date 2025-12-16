<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Department Management') ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- Include Mobile Styles -->
    <?= view('templates/mobile_styles') ?>
    
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding-right: 20px;
            padding-left: 20px;
            padding-bottom: 30px;
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
            background: white;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            float: right;
        }
        .form-section {
            margin-bottom: 30px;
        }
        .form-section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Department Management']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-building"></i> Department Management</h2>
                        <p class="text-muted mb-0">Create and manage departments across warehouses and central office</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDepartmentModal">
                            <i class="bi bi-plus-circle"></i> Create Department
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <p>Total Departments</p>
                        <h3><?= $stats['total_departments'] ?? 0 ?></h3>
                        <div class="icon bg-primary text-white">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <p>Active Departments</p>
                        <h3><?= $stats['active_departments'] ?? 0 ?></h3>
                        <div class="icon bg-success text-white">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <p>Central Office</p>
                        <h3><?= $stats['central_office_departments'] ?? 0 ?></h3>
                        <div class="icon bg-warning text-white">
                            <i class="bi bi-house"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <p>Warehouse Departments</p>
                        <h3><?= $stats['warehouse_departments'] ?? 0 ?></h3>
                        <div class="icon bg-info text-white">
                            <i class="bi bi-warehouse"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments Table -->
            <div class="card table-card">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Departments</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Search departments..." id="searchDepartments">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="departmentsTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Warehouse</th>
                                    <th>Department Head</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($dept['name']) ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($dept['description'] ?: 'No description') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($dept['warehouse_id']): ?>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-warehouse"></i> <?= esc($dept['warehouse_name'] ?? 'N/A') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-house"></i> Central Office
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($dept['department_head'] ?: 'Not assigned') ?></td>
                                        <td>
                                            <?php if ($dept['contact_email']): ?>
                                                <small><i class="bi bi-envelope"></i> <?= esc($dept['contact_email']) ?></small><br>
                                            <?php endif; ?>
                                            <?php if ($dept['contact_phone']): ?>
                                                <small><i class="bi bi-telephone"></i> <?= esc($dept['contact_phone']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!$dept['contact_email'] && !$dept['contact_phone']): ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($dept['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-primary" onclick="editDepartment(<?= $dept['id'] ?>)" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($dept['is_active']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="softDeleteDepartment(<?= $dept['id'] ?>, '<?= esc($dept['name']) ?>')" title="Deactivate">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" onclick="reactivateDepartment(<?= $dept['id'] ?>, '<?= esc($dept['name']) ?>')" title="Reactivate">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Department Modal -->
    <div class="modal fade" id="createDepartmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Create New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createDepartmentForm">
                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-info-circle"></i> Basic Information
                            </div>
                            <div class="mb-3">
                                <label for="createName" class="form-label required">Department Name</label>
                                <input type="text" class="form-control" id="createName" required>
                            </div>
                            <div class="mb-3">
                                <label for="createDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="createDescription" rows="3" placeholder="Brief description of the department..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="createWarehouse" class="form-label">Warehouse (Optional)</label>
                                <select class="form-select" id="createWarehouse">
                                    <option value="">-- Central Office (No Warehouse) --</option>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>">
                                            <?= esc($warehouse['name']) ?> (<?= esc($warehouse['code']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Leave empty for Central Office departments</small>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title">
                                <i class="bi bi-person"></i> Department Head & Contact
                            </div>
                            <div class="mb-3">
                                <label for="createDepartmentHead" class="form-label">Department Head</label>
                                <input type="text" class="form-control" id="createDepartmentHead" placeholder="Name of department head">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="createContactEmail" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="createContactEmail" placeholder="department@example.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="createContactPhone" class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" id="createContactPhone" placeholder="+63 XXX XXX XXXX">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="createIsActive" checked>
                                <label class="form-check-label" for="createIsActive">
                                    Active Department
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreateDepartment()">
                        <i class="bi bi-check-circle"></i> Create Department
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editDepartmentContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
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
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#departmentsTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 25,
                responsive: true
            });

            // Search functionality
            $('#searchDepartments').on('keyup', function() {
                $('#departmentsTable').DataTable().search(this.value).draw();
            });
        });

        function submitCreateDepartment() {
            const form = document.getElementById('createDepartmentForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                name: document.getElementById('createName').value,
                description: document.getElementById('createDescription').value || null,
                warehouse_id: document.getElementById('createWarehouse').value || null,
                department_head: document.getElementById('createDepartmentHead').value || null,
                contact_email: document.getElementById('createContactEmail').value || null,
                contact_phone: document.getElementById('createContactPhone').value || null,
                is_active: document.getElementById('createIsActive').checked ? '1' : '0'
            };

            $.ajax({
                url: '<?= base_url('it-administrator/departments/create') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Department created successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('createDepartmentModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to create department'));
                        if (response.errors) {
                            console.error('Validation errors:', response.errors);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function editDepartment(departmentId) {
            const modal = new bootstrap.Modal(document.getElementById('editDepartmentModal'));
            document.getElementById('editDepartmentContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            $.ajax({
                url: '<?= base_url('it-administrator/departments/get') ?>',
                method: 'POST',
                data: { department_id: departmentId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        const dept = response.department;
                        const warehouses = <?= json_encode($warehouses ?? []) ?>;

                        let warehousesOptions = '<option value="">-- Central Office (No Warehouse) --</option>';
                        warehouses.forEach(warehouse => {
                            warehousesOptions += `<option value="${warehouse.id}" ${warehouse.id == dept.warehouse_id ? 'selected' : ''}>${warehouse.name} (${warehouse.code})</option>`;
                        });

                        document.getElementById('editDepartmentContent').innerHTML = `
                            <form id="editDepartmentForm">
                                <input type="hidden" id="editDepartmentId" value="${dept.id}">
                                
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-info-circle"></i> Basic Information
                                    </div>
                                    <div class="mb-3">
                                        <label for="editName" class="form-label required">Department Name</label>
                                        <input type="text" class="form-control" id="editName" value="${dept.name || ''}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="editDescription" rows="3">${dept.description || ''}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editWarehouse" class="form-label">Warehouse (Optional)</label>
                                        <select class="form-select" id="editWarehouse">
                                            ${warehousesOptions}
                                        </select>
                                        <small class="form-text text-muted">Leave empty for Central Office departments</small>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="bi bi-person"></i> Department Head & Contact
                                    </div>
                                    <div class="mb-3">
                                        <label for="editDepartmentHead" class="form-label">Department Head</label>
                                        <input type="text" class="form-control" id="editDepartmentHead" value="${dept.department_head || ''}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="editContactEmail" class="form-label">Contact Email</label>
                                            <input type="email" class="form-control" id="editContactEmail" value="${dept.contact_email || ''}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="editContactPhone" class="form-label">Contact Phone</label>
                                            <input type="tel" class="form-control" id="editContactPhone" value="${dept.contact_phone || ''}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editIsActive" ${dept.is_active ? 'checked' : ''}>
                                        <label class="form-check-label" for="editIsActive">
                                            Active Department
                                        </label>
                                    </div>
                                </div>
                            </form>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="submitEditDepartment()">
                                    <i class="bi bi-check-circle"></i> Update Department
                                </button>
                            </div>
                        `;
                    } else {
                        alert('Error: ' + (response.message || 'Failed to load department'));
                        bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal')).hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal')).hide();
                }
            });
        }

        function submitEditDepartment() {
            const form = document.getElementById('editDepartmentForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                department_id: document.getElementById('editDepartmentId').value,
                name: document.getElementById('editName').value,
                description: document.getElementById('editDescription').value || null,
                warehouse_id: document.getElementById('editWarehouse').value || null,
                department_head: document.getElementById('editDepartmentHead').value || null,
                contact_email: document.getElementById('editContactEmail').value || null,
                contact_phone: document.getElementById('editContactPhone').value || null,
                is_active: document.getElementById('editIsActive').checked ? '1' : '0'
            };

            $.ajax({
                url: '<?= base_url('it-administrator/departments/update') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Department updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('editDepartmentModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to update department'));
                        if (response.errors) {
                            console.error('Validation errors:', response.errors);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function softDeleteDepartment(departmentId, departmentName) {
            if (!confirm(`Are you sure you want to deactivate "${departmentName}"?\n\nThis will set the department as inactive.`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/departments/soft-delete') ?>',
                method: 'POST',
                data: { department_id: departmentId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Department deactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to deactivate department'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function reactivateDepartment(departmentId, departmentName) {
            if (!confirm(`Are you sure you want to reactivate "${departmentName}"?`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/departments/reactivate') ?>',
                method: 'POST',
                data: { department_id: departmentId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Department reactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to reactivate department'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }
    </script>
</body>
</html>

