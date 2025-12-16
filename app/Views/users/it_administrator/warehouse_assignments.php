<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Warehouse Assignments') ?></title>
    
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
        .user-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            background: white;
        }
        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .warehouse-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
            margin: 0.25rem;
        }
        .primary-badge {
            background-color: #0d6efd;
            color: white;
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
        .stat-card.info { border-left-color: #0dcaf0; }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Warehouse Assignments']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-building"></i> Warehouse Assignments</h2>
                        <p class="text-muted mb-0">Assign users to warehouses and manage access control</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="bi bi-plus-circle"></i> Assign User to Warehouse
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <p>Total Users</p>
                        <h3><?= count($users ?? []) ?></h3>
                        <div class="icon bg-primary text-white">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <p>Active Warehouses</p>
                        <h3><?= count($warehouses ?? []) ?></h3>
                        <div class="icon bg-success text-white">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <p>Assigned Users</p>
                        <h3><?= count($assignments ?? []) ?></h3>
                        <div class="icon bg-info text-white">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <p>Unassigned Users</p>
                        <h3><?= count($users ?? []) - count($assignments ?? []) ?></h3>
                        <div class="icon bg-warning text-white">
                            <i class="bi bi-person-x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users with Assignments -->
            <div class="card table-card">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> User Warehouse Assignments</h5>
                        </div>
                        <div class="col-auto">
                            <div class="input-group" style="width: 250px;">
                                <input type="text" class="form-control" placeholder="Search users..." id="searchUsers">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($users)): ?>
                        <div class="row">
                            <?php foreach ($users as $userItem): ?>
                                <?php 
                                    $userAssignments = $assignments[$userItem['id']] ?? [];
                                    $hasAssignments = !empty($userAssignments);
                                ?>
                                <div class="col-lg-6 col-xl-4 mb-3 user-item">
                                    <div class="user-card">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <?= esc(trim($userItem['first_name'] . ' ' . ($userItem['middle_name'] ?? '') . ' ' . $userItem['last_name'])) ?>
                                                    <?php if (!$hasAssignments): ?>
                                                        <span class="badge bg-warning ms-2">Unassigned</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="text-muted mb-2 small"><?= esc($userItem['email']) ?></p>
                                                <span class="badge bg-secondary">
                                                    <?= esc($userItem['role_name'] ?? 'No Role') ?>
                                                </span>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="openAssignModal(<?= $userItem['id'] ?>, '<?= esc(trim($userItem['first_name'] . ' ' . $userItem['last_name'])) ?>')">
                                                            <i class="bi bi-plus-circle"></i> Assign Warehouse
                                                        </a>
                                                    </li>
                                                    <?php if ($hasAssignments): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="viewAssignments(<?= $userItem['id'] ?>)">
                                                                <i class="bi bi-eye"></i> View Assignments
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <?php if ($hasAssignments): ?>
                                            <div class="mt-3">
                                                <small class="text-muted d-block mb-2"><strong>Assigned Warehouses:</strong></small>
                                                <div>
                                                    <?php foreach ($userAssignments as $assignment): ?>
                                                        <span class="badge warehouse-badge <?= $assignment['is_primary'] ? 'primary-badge' : 'bg-secondary' ?>" 
                                                              title="<?= $assignment['is_primary'] ? 'Primary Warehouse' : '' ?>">
                                                            <?= esc($assignment['warehouse_name']) ?>
                                                            <?php if ($assignment['is_primary']): ?>
                                                                <i class="bi bi-star-fill"></i>
                                                            <?php endif; ?>
                                                            <?php if (!$assignment['is_active']): ?>
                                                                <span class="badge bg-danger ms-1">Inactive</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-3">
                                                <small class="text-muted">No warehouse assignments</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No users found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign User to Warehouse Modal -->
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-building"></i> Assign User to Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignForm">
                        <input type="hidden" id="assignUserId">
                        <div class="mb-3">
                            <label for="assignUser" class="form-label">User <span class="text-danger">*</span></label>
                            <select class="form-select" id="assignUser" required>
                                <option value="">Select User</option>
                                <?php foreach ($users as $userItem): ?>
                                    <option value="<?= $userItem['id'] ?>" 
                                            data-name="<?= esc(trim($userItem['first_name'] . ' ' . $userItem['last_name'])) ?>"
                                            data-role="<?= esc($userItem['role_name'] ?? '') ?>">
                                        <?= esc(trim($userItem['first_name'] . ' ' . $userItem['last_name'])) ?> 
                                        (<?= esc($userItem['email']) ?>) - <?= esc($userItem['role_name'] ?? 'No Role') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assignWarehouse" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="assignWarehouse" required>
                                <option value="">Select Warehouse</option>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse['id'] ?>">
                                        <?= esc($warehouse['name']) ?> (<?= esc($warehouse['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assignRole" class="form-label">Role (Optional)</label>
                            <select class="form-select" id="assignRole">
                                <option value="">Use User's Default Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Override user's role for this specific warehouse</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isPrimary">
                                <label class="form-check-label" for="isPrimary">
                                    Set as Primary Warehouse
                                </label>
                            </div>
                            <small class="form-text text-muted">Primary warehouse will be used as default for this user</small>
                        </div>
                        <div class="mb-3">
                            <label for="assignNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="assignNotes" rows="3" placeholder="Add any notes about this assignment..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitAssignment()">
                        <i class="bi bi-check-circle"></i> Assign
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Assignments Modal -->
    <div class="modal fade" id="viewAssignmentsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-list-ul"></i> Warehouse Assignments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewAssignmentsContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        const users = <?= json_encode($users ?? []) ?>;
        const warehouses = <?= json_encode($warehouses ?? []) ?>;
        const assignments = <?= json_encode($assignments ?? []) ?>;

        function openAssignModal(userId = null, userName = null) {
            const modal = new bootstrap.Modal(document.getElementById('assignModal'));
            const form = document.getElementById('assignForm');
            form.reset();
            
            if (userId) {
                document.getElementById('assignUserId').value = userId;
                document.getElementById('assignUser').value = userId;
                document.getElementById('assignUser').disabled = true;
            } else {
                document.getElementById('assignUserId').value = '';
                document.getElementById('assignUser').disabled = false;
            }
            
            modal.show();
        }

        function submitAssignment() {
            const form = document.getElementById('assignForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const userId = document.getElementById('assignUser').value;
            const warehouseId = document.getElementById('assignWarehouse').value;
            const roleId = document.getElementById('assignRole').value;
            const isPrimary = document.getElementById('isPrimary').checked;
            const notes = document.getElementById('assignNotes').value;

            if (!userId || !warehouseId) {
                alert('Please select both user and warehouse');
                return;
            }

            const formData = {
                user_id: userId,
                warehouse_id: warehouseId,
                role_id: roleId || null,
                is_primary: isPrimary ? '1' : '0',
                notes: notes
            };

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-assignments/assign') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('User assigned to warehouse successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to assign user'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function viewAssignments(userId) {
            const userAssignments = assignments[userId] || [];
            const user = users.find(u => u.id == userId);
            
            if (!user) {
                alert('User not found');
                return;
            }

            let content = `
                <h6 class="mb-3">${user.first_name} ${user.last_name} - Warehouse Assignments</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Code</th>
                                <th>Role</th>
                                <th>Primary</th>
                                <th>Status</th>
                                <th>Assigned Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (userAssignments.length > 0) {
                userAssignments.forEach(assignment => {
                    content += `
                        <tr>
                            <td>${assignment.warehouse_name || 'N/A'}</td>
                            <td>${assignment.warehouse_code || 'N/A'}</td>
                            <td>${assignment.role_name || 'Default'}</td>
                            <td>${assignment.is_primary ? '<span class="badge bg-primary"><i class="bi bi-star-fill"></i> Primary</span>' : '-'}</td>
                            <td>${assignment.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</td>
                            <td>${assignment.assigned_at ? new Date(assignment.assigned_at).toLocaleDateString() : 'N/A'}</td>
                            <td>
                                ${assignment.is_active ? `
                                    <button class="btn btn-sm btn-danger" onclick="removeAssignment(${userId}, ${assignment.warehouse_id})">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                ` : `
                                    <button class="btn btn-sm btn-success" onclick="reactivateAssignment(${userId}, ${assignment.warehouse_id})">
                                        <i class="bi bi-arrow-clockwise"></i> Reactivate
                                    </button>
                                `}
                                ${!assignment.is_primary ? `
                                    <button class="btn btn-sm btn-primary" onclick="setPrimary(${userId}, ${assignment.warehouse_id})">
                                        <i class="bi bi-star"></i> Set Primary
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                });
            } else {
                content += '<tr><td colspan="7" class="text-center">No assignments found</td></tr>';
            }

            content += `
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('viewAssignmentsContent').innerHTML = content;
            const modal = new bootstrap.Modal(document.getElementById('viewAssignmentsModal'));
            modal.show();
        }

        function removeAssignment(userId, warehouseId) {
            if (!confirm('Are you sure you want to remove this assignment?')) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-assignments/remove') ?>',
                method: 'POST',
                data: {
                    user_id: userId,
                    warehouse_id: warehouseId
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Assignment removed successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to remove assignment'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function setPrimary(userId, warehouseId) {
            $.ajax({
                url: '<?= base_url('it-administrator/warehouse-assignments/set-primary') ?>',
                method: 'POST',
                data: {
                    user_id: userId,
                    warehouse_id: warehouseId
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Primary warehouse set successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to set primary warehouse'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        // Search functionality
        document.getElementById('searchUsers')?.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');
            
            userItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>

