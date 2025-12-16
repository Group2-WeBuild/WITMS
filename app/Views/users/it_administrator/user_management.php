<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'User Management') ?></title>
    
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
        .role-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'User Management']) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1"><i class="bi bi-people"></i> User Management</h2>
                        <p class="text-muted mb-0">Manage system users, roles, and permissions</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="bi bi-person-plus"></i> Create User
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card primary">
                        <p>Total Users</p>
                        <h3><?= $stats['total_users'] ?? 0 ?></h3>
                        <div class="icon bg-primary text-white">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card success">
                        <p>Active Users</p>
                        <h3><?= $stats['active_users'] ?? 0 ?></h3>
                        <div class="icon bg-success text-white">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card warning">
                        <p>Inactive Users</p>
                        <h3><?= $stats['inactive_users'] ?? 0 ?></h3>
                        <div class="icon bg-warning text-white">
                            <i class="bi bi-person-x"></i>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stat-card info">
                        <p>IT Administrators</p>
                        <h3><?= $stats['role_counts']['it_administrator'] ?? 0 ?></h3>
                        <div class="icon bg-info text-white">
                            <i class="bi bi-shield-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card table-card">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Users</h5>
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
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $userItem): ?>
                                    <?php 
                                        $isCurrentUser = ($userItem['id'] == $current_user_id);
                                        $isITAdmin = ($userItem['role_name'] == 'IT Administrator');
                                        $canEdit = !$isCurrentUser; // Cannot edit self
                                        $canDelete = !$isCurrentUser && !$isITAdmin; // Cannot delete self or IT Admins
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc(trim($userItem['first_name'] . ' ' . ($userItem['middle_name'] ?? '') . ' ' . $userItem['last_name'])) ?></strong>
                                            <?php if ($isCurrentUser): ?>
                                                <span class="badge bg-info ms-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($userItem['email']) ?></td>
                                        <td>
                                            <span class="badge role-badge bg-<?= $isITAdmin ? 'danger' : 'secondary' ?>">
                                                <?= esc($userItem['role_name'] ?? 'No Role') ?>
                                            </span>
                                        </td>
                                        <td><?= esc($userItem['department_name'] ?? 'N/A') ?></td>
                                        <td><?= esc($userItem['phone_number'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($userItem['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($userItem['last_login']): ?>
                                                <?= date('M j, Y H:i', strtotime($userItem['last_login'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <?php if ($canEdit): ?>
                                                <button class="btn btn-sm btn-primary" onclick="editUser(<?= $userItem['id'] ?>)" title="Edit User">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled title="Cannot edit your own account">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($canDelete): ?>
                                                <?php if ($userItem['is_active']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="softDeleteUser(<?= $userItem['id'] ?>, '<?= esc(trim($userItem['first_name'] . ' ' . $userItem['last_name'])) ?>')" title="Deactivate User">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" onclick="reactivateUser(<?= $userItem['id'] ?>, '<?= esc(trim($userItem['first_name'] . ' ' . $userItem['last_name'])) ?>')" title="Reactivate User">
                                                        <i class="bi bi-arrow-clockwise"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled title="<?= $isCurrentUser ? 'Cannot delete your own account' : 'IT Administrator accounts cannot be deleted' ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="createFirstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createMiddleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="createMiddleName">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="createLastName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="createEmail" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="createPhone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createRole" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="createRole" required>
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>"><?= esc($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="createDepartment" class="form-label">Department</label>
                                <select class="form-select" id="createDepartment">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= esc($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="createPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="createPassword" required>
                                <small class="form-text text-muted">Must contain uppercase, lowercase, number, and special character</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="createIsActive" checked>
                                <label class="form-check-label" for="createIsActive">
                                    Active Account
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreateUser()">
                        <i class="bi bi-check-circle"></i> Create User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editUserContent">
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
            $('#usersTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 25,
                responsive: true
            });

            // Search functionality
            $('#searchUsers').on('keyup', function() {
                $('#usersTable').DataTable().search(this.value).draw();
            });
        });

        function submitCreateUser() {
            const form = document.getElementById('createUserForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                first_name: document.getElementById('createFirstName').value,
                middle_name: document.getElementById('createMiddleName').value,
                last_name: document.getElementById('createLastName').value,
                email: document.getElementById('createEmail').value,
                phone_number: document.getElementById('createPhone').value,
                role_id: document.getElementById('createRole').value,
                department_id: document.getElementById('createDepartment').value || null,
                password: document.getElementById('createPassword').value,
                is_active: document.getElementById('createIsActive').checked ? '1' : '0'
            };

            $.ajax({
                url: '<?= base_url('it-administrator/users/create') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('User created successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to create user'));
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

        function editUser(userId) {
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            document.getElementById('editUserContent').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            $.ajax({
                url: '<?= base_url('it-administrator/users/get') ?>',
                method: 'POST',
                data: { user_id: userId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        const user = response.user;
                        const roles = <?= json_encode($roles) ?>;
                        const departments = <?= json_encode($departments) ?>;

                        let rolesOptions = '<option value="">Select Role</option>';
                        roles.forEach(role => {
                            rolesOptions += `<option value="${role.id}" ${role.id == user.role_id ? 'selected' : ''}>${role.name}</option>`;
                        });

                        let departmentsOptions = '<option value="">Select Department</option>';
                        departments.forEach(dept => {
                            departmentsOptions += `<option value="${dept.id}" ${dept.id == user.department_id ? 'selected' : ''}>${dept.name}</option>`;
                        });

                        document.getElementById('editUserContent').innerHTML = `
                            <form id="editUserForm">
                                <input type="hidden" id="editUserId" value="${user.id}">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editFirstName" value="${user.first_name || ''}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editMiddleName" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="editMiddleName" value="${user.middle_name || ''}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="editLastName" value="${user.last_name || ''}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="editEmail" value="${user.email || ''}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editPhone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="editPhone" value="${user.phone_number || ''}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editRole" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select" id="editRole" required>
                                            ${rolesOptions}
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editDepartment" class="form-label">Department</label>
                                        <select class="form-select" id="editDepartment">
                                            ${departmentsOptions}
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editPassword" class="form-label">New Password (Leave blank to keep current)</label>
                                        <input type="password" class="form-control" id="editPassword">
                                        <small class="form-text text-muted">Only fill if you want to change the password</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editIsActive" ${user.is_active ? 'checked' : ''}>
                                        <label class="form-check-label" for="editIsActive">
                                            Active Account
                                        </label>
                                    </div>
                                </div>
                            </form>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="submitEditUser()">
                                    <i class="bi bi-check-circle"></i> Update User
                                </button>
                            </div>
                        `;
                    } else {
                        alert('Error: ' + (response.message || 'Failed to load user'));
                        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                }
            });
        }

        function submitEditUser() {
            const form = document.getElementById('editUserForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = {
                user_id: document.getElementById('editUserId').value,
                first_name: document.getElementById('editFirstName').value,
                middle_name: document.getElementById('editMiddleName').value,
                last_name: document.getElementById('editLastName').value,
                email: document.getElementById('editEmail').value,
                phone_number: document.getElementById('editPhone').value,
                role_id: document.getElementById('editRole').value,
                department_id: document.getElementById('editDepartment').value || null,
                password: document.getElementById('editPassword').value,
                is_active: document.getElementById('editIsActive').checked ? '1' : '0'
            };

            $.ajax({
                url: '<?= base_url('it-administrator/users/update') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('User updated successfully!');
                        bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to update user'));
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

        function softDeleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to deactivate ${userName}?`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/users/soft-delete') ?>',
                method: 'POST',
                data: { user_id: userId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('User deactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to deactivate user'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        }

        function reactivateUser(userId, userName) {
            if (!confirm(`Are you sure you want to reactivate ${userName}?`)) {
                return;
            }

            $.ajax({
                url: '<?= base_url('it-administrator/users/reactivate') ?>',
                method: 'POST',
                data: { user_id: userId },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('User reactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to reactivate user'));
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

