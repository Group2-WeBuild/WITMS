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
            float: right;
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
                        <p class="text-muted mb-0">Track and manage all warehouse inventory</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="btn-group d-flex flex-column flex-md-row" role="group">
                            <a href="<?= base_url('warehouse-manager/inventory/add') ?>" class="btn btn-primary mb-2 mb-md-0 me-md-2">
                                <i class="bi bi-plus-circle"></i> Add Stock
                            </a>
                            <a href="<?= base_url('warehouse-manager/inventory/low-stock') ?>" class="btn btn-warning mb-2 mb-md-0 me-md-2">
                                <i class="bi bi-exclamation-triangle"></i> Low Stock
                            </a>
                            <a href="<?= base_url('warehouse-manager/inventory/expiring') ?>" class="btn btn-danger">
                                <i class="bi bi-calendar-x"></i> Expiring Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

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
                                            <td><?= number_format($item['quantity'], 2) ?></td>
                                            <td><?= number_format($item['available_quantity'], 2) ?></td>
                                            <td><?= esc($item['unit_abbr'] ?? 'N/A') ?></td>
                                            <td><?= esc($item['location_in_warehouse'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php
                                                $stockLevel = $item['quantity'] ?? 0;
                                                $reorderLevel = $item['reorder_level'] ?? 0;
                                                
                                                if ($stockLevel <= 0):
                                                ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php elseif ($stockLevel <= $reorderLevel): ?>
                                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">In Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= base_url('warehouse-manager/inventory/view/' . $item['id']) ?>" 
                                                       class="btn btn-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('warehouse-manager/inventory/edit/' . $item['id']) ?>" 
                                                       class="btn btn-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?= base_url('warehouse-manager/inventory/adjust/' . $item['id']) ?>" 
                                                       class="btn btn-primary" title="Adjust Quantity">
                                                        <i class="bi bi-arrow-left-right"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable with custom search
            var table = $('#inventoryTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search inventory:",
                    lengthMenu: "Show _MENU_ items per page"
                },
                initComplete: function() {
                    // Hide default search
                    $('.dataTables_filter').hide();
                }
            });

            // Custom search functionality
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
                    
                    // Check if item is expiring
                    var expirationDate = $(rowNode).find('td:eq(7)').text().trim();
                    var isExpiring = false;
                    if (expirationDate && expirationDate !== 'N/A') {
                        var expDate = new Date(expirationDate);
                        var today = new Date();
                        var daysUntilExpiry = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));
                        if (daysUntilExpiry <= 30 && daysUntilExpiry >= 0) {
                            isExpiring = true;
                        }
                    }

                    var matchesSearch = materialName.includes(searchTerm) || materialCode.includes(searchTerm);
                    var matchesWarehouse = !warehouseFilter || warehouse === warehouseFilter;
                    var matchesCategory = !categoryFilter || category === categoryFilter;
                    var matchesStatus = !statusFilter || 
                        (statusFilter === 'Expiring' ? isExpiring : status === statusFilter);

                    if (matchesSearch && matchesWarehouse && matchesCategory && matchesStatus) {
                        this.nodes().to$().show();
                    } else {
                        this.nodes().to$().hide();
                    }
                });
            }

            // Event listeners
            $('#searchInput').on('keyup', filterTable);
            $('#warehouseFilter').on('change', filterTable);
            $('#categoryFilter').on('change', filterTable);
            $('#statusFilter').on('change', filterTable);

            // Clear filters
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
