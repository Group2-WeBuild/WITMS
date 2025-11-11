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
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'Materials Catalog']) ?>        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1"><i class="bi bi-boxes"></i> Materials Catalog</h2>
                        <p class="text-muted mb-0">Manage materials, categories, and units</p>
                    </div>
                    <div>
                        <a href="<?= base_url('warehouse-manager/materials/add') ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Material
                        </a>
                        <a href="<?= base_url('warehouse-manager/materials/categories') ?>" class="btn btn-secondary">
                            <i class="bi bi-folder"></i> Categories
                        </a>
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
            <?php endif; ?>            <!-- Materials Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <div class="icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <h3><?= number_format($stats['total_materials'] ?? 0) ?></h3>
                        <p>Total Materials</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <div class="icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <h3><?= number_format($stats['active_materials'] ?? 0) ?></h3>
                        <p>Active Materials</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <div class="icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h3><?= number_format($stats['total_categories'] ?? 0) ?></h3>
                        <p>Categories</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card info">
                        <div class="icon bg-info bg-opacity-10 text-info">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3><?= number_format($stats['perishable_materials'] ?? 0) ?></h3>
                        <p>Perishable Items</p>
                    </div>
                </div>
            </div>            <!-- Materials Table -->
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
                                                    <a href="<?= base_url('warehouse-manager/materials/view/' . $material['id']) ?>" 
                                                       class="btn btn-info" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('warehouse-manager/materials/edit/' . $material['id']) ?>" 
                                                       class="btn btn-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($material['is_active']): ?>
                                                        <a href="<?= base_url('warehouse-manager/materials/deactivate/' . $material['id']) ?>" 
                                                           class="btn btn-danger" title="Deactivate"
                                                           onclick="return confirm('Deactivate this material?')">
                                                            <i class="bi bi-x-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('warehouse-manager/materials/activate/' . $material['id']) ?>" 
                                                           class="btn btn-success" title="Activate">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#materialsTable').DataTable({
                order: [[1, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search materials:",
                    lengthMenu: "Show _MENU_ materials per page"
                }
            });
        });
    </script>
</body>
</html>
