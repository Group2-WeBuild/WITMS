<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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
        .staff-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .staff-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .role-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-online { background-color: #28a745; }
        .status-offline { background-color: #6c757d; }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2><i class="bi bi-people"></i> Staff Management</h2>
                <p class="text-muted">Manage warehouse personnel and assign work tasks</p>
            </div>
        </div>

        <!-- Staff Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Staff</h6>
                                <h4 class="mb-0"><?= count($staff) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-people text-primary fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Active Today</h6>
                                <h4 class="mb-0"><?= count(array_filter($staff, fn($s) => $s['last_login'] && date('Y-m-d', strtotime($s['last_login'])) == date('Y-m-d'))) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-person-check text-success fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">On Leave</h6>
                                <h4 class="mb-0">0</h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-calendar-x text-warning fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">New This Month</h6>
                                <h4 class="mb-0"><?= count(array_filter($staff, fn($s) => strtotime($s['created_at']) > strtotime('-1 month'))) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-person-plus text-info fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff List -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Warehouse Staff</h5>
                    </div>
                    <div class="col-auto">
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control" placeholder="Search staff..." id="searchStaff">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($staff)): ?>
                    <div class="row">
                        <?php foreach ($staff as $member): ?>
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="staff-card bg-white">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= esc(trim($member['first_name'] . ' ' . $member['middle_name'] . ' ' . $member['last_name'])) ?></h6>
                                            <p class="text-muted mb-2"><?= esc($member['email']) ?></p>
                                            <span class="badge role-badge bg-<?= $member['role_name'] == 'Warehouse Manager' ? 'primary' : ($member['role_name'] == 'Warehouse Staff' ? 'success' : 'info') ?>">
                                                <?= esc($member['role_name']) ?>
                                            </span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="viewStaff(<?= $member['id'] ?>)" data-bs-toggle="modal" data-bs-target="#viewStaffModal"><i class="bi bi-eye"></i> View Profile</a></li>
                                                <?php if ($member['id'] != session()->get('user_id')): ?>
                                                    <li><a class="dropdown-item" href="#" onclick="assignWork(<?= $member['id'] ?>, '<?= esc(trim($member['first_name'] . ' ' . $member['last_name'])) ?>')" data-bs-toggle="modal" data-bs-target="#assignWorkModal"><i class="bi bi-clipboard-check"></i> Assign Work</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="row text-muted small">
                                        <div class="col-6">
                                            <i class="bi bi-telephone"></i> <?= esc($member['phone_number'] ?? 'Not set') ?>
                                        </div>
                                        <div class="col-6">
                                            <i class="bi bi-calendar"></i> Joined <?= date('M Y', strtotime($member['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="status-indicator <?= $member['last_login'] && date('Y-m-d', strtotime($member['last_login'])) == date('Y-m-d') ? 'status-online' : 'status-offline' ?>"></span>
                                        <small class="text-muted">
                                            <?= $member['last_login'] ? 'Last seen ' . date('M j, Y H:i', strtotime($member['last_login'])) : 'Never logged in' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No staff found</p>
                        <p class="text-muted">Contact IT Administrator to add staff members</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addStaffForm">
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="middleName" class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName">
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" required>
                                <option value="">Select Role</option>
                                <option value="warehouse_manager">Warehouse Manager</option>
                                <option value="warehouse_staff">Warehouse Staff</option>
                                <option value="inventory_auditor">Inventory Auditor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department">
                                <option value="">Select Department</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="logistics">Logistics</option>
                                <option value="inventory">Inventory</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addStaff()">
                        <i class="bi bi-plus-circle"></i> Add Staff
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Staff Modal -->
    <div class="modal fade" id="viewStaffModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Staff Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewStaffContent">
                    <!-- Content will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Work Modal -->
    <div class="modal fade" id="assignWorkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Work</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignWorkForm">
                        <input type="hidden" id="assignStaffId">
                        <div class="mb-3">
                            <label class="form-label">Assigning work to: <strong id="assignStaffName"></strong></label>
                        </div>
                        <div class="mb-3">
                            <label for="taskType" class="form-label">Task Type</label>
                            <select class="form-select" id="taskType" required>
                                <option value="">Select Task Type</option>
                                <option value="stock_count">Stock Count</option>
                                <option value="receive_shipment">Receive Shipment</option>
                                <option value="stock_transfer">Stock Transfer</option>
                                <option value="inventory_check">Inventory Check</option>
                                <option value="quality_control">Quality Control</option>
                                <option value="stock_arrangement">Stock Arrangement</option>
                                <option value="expiry_check">Expiry Check</option>
                                <option value="cleaning">Warehouse Cleaning</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Task Description</label>
                            <textarea class="form-control" id="taskDescription" rows="3" placeholder="Provide details about the task..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="taskLocation" placeholder="e.g., Aisle 3, Rack B-12">
                        </div>
                        <div class="mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDeadline" class="form-label">Deadline</label>
                            <input type="datetime-local" class="form-control" id="taskDeadline">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitWorkAssignment()">
                        <i class="bi bi-check-circle"></i> Assign Work
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const staffData = <?= json_encode($staff) ?>;
        
        function viewStaff(id) {
            const staff = staffData.find(s => s.id == id);
            if (staff) {
                const content = `
                    <div class="text-center mb-3">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-person fs-1"></i>
                        </div>
                    </div>
                    <table class="table">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>${staff.first_name} ${staff.middle_name || ''} ${staff.last_name}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>${staff.email}</td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>${staff.phone_number || 'Not set'}</td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td><span class="badge bg-primary">${staff.role_name}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>${staff.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>'}</td>
                        </tr>
                        <tr>
                            <td><strong>Joined:</strong></td>
                            <td>${new Date(staff.created_at).toLocaleDateString()}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Login:</strong></td>
                            <td>${staff.last_login ? new Date(staff.last_login).toLocaleString() : 'Never'}</td>
                        </tr>
                    </table>
                `;
                document.getElementById('viewStaffContent').innerHTML = content;
            }
        }

        function assignWork(id, name) {
            document.getElementById('assignStaffId').value = id;
            document.getElementById('assignStaffName').textContent = name;
            // Reset form
            document.getElementById('assignWorkForm').reset();
            // Set default deadline to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('taskDeadline').value = tomorrow.toISOString().slice(0, 16);
        }

        function submitWorkAssignment() {
            const form = document.getElementById('assignWorkForm');
            if (form.checkValidity()) {
                const formData = new FormData(form);
                const id = document.getElementById('assignStaffId').value;
                
                // For now, just show success message
                const taskType = document.getElementById('taskType').value;
                const taskDescription = document.getElementById('taskDescription').value;
                const staffName = document.getElementById('assignStaffName').textContent;
                
                alert(`Work assignment sent to ${staffName}!\n\nTask: ${taskType}\nDescription: ${taskDescription}`);
                
                bootstrap.Modal.getInstance(document.getElementById('assignWorkModal')).hide();
                form.reset();
                
                // In a real implementation, you would send this to the server:
                // fetch(`/warehouse-manager/assign-work/${id}`, {
                //     method: 'POST',
                //     body: formData
                // });
            } else {
                form.reportValidity();
            }
        }

        function showAddStaffModal() {
            const modal = new bootstrap.Modal(document.getElementById('addStaffModal'));
            modal.show();
        }

        function addStaff() {
            const form = document.getElementById('addStaffForm');
            if (form.checkValidity()) {
                // Simulate adding staff
                alert('Staff member added successfully!');
                bootstrap.Modal.getInstance(document.getElementById('addStaffModal')).hide();
                form.reset();
                location.reload();
            } else {
                form.reportValidity();
            }
        }

        // Search functionality
        document.getElementById('searchStaff').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.staff-card');
            
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.parentElement.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>