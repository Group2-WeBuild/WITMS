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
                                            <td><?= esc($item['category_name']) ?></td>                                            <td><?= esc($item['warehouse_name']) ?></td>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search inventory:",
                    lengthMenu: "Show _MENU_ items per page"
                }
            });
        });
    </script>
</body>
</html>
