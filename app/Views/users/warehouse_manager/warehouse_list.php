<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    
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
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .page-header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-1"><i class="bi bi-building"></i> Warehouse Overview</h2>
                    <p class="text-muted mb-0">View your assigned warehouse locations and details</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href=<?= base_url('warehouse-manager/warehouse/map') ?> class="btn btn-info">
                        <i class="bi bi-map"></i> Map View
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
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

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $stats['total_warehouses'] ?? 0 ?></div>
                            <div class="label">Total Warehouses</div>
                        </div>
                        <div class="icon">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $stats['active_warehouses'] ?? 0 ?></div>
                            <div class="label">Active Warehouses</div>
                        </div>
                        <div class="icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $stats['total_capacity'] ?? 0 ?></div>
                            <div class="label">Total Capacity (m²)</div>
                        </div>
                        <div class="icon">
                            <i class="bi bi-arrows-angle-expand"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $stats['managed_warehouses'] ?? 0 ?></div>
                            <div class="label">With Managers</div>
                        </div>
                        <div class="icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warehouses Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Manager</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <tr>
                                    <td><strong><?= esc($warehouse['code']) ?></strong></td>
                                    <td><?= esc($warehouse['name']) ?></td>
                                    <td>
                                        <small>
                                            <?= esc($warehouse['city'] ?? 'N/A') ?>, 
                                            <?= esc($warehouse['province'] ?? 'N/A') ?>
                                            <br>
                                            <span class="text-muted"><?= esc($warehouse['region'] ?? 'N/A') ?></span>
                                        </small>
                                    </td>
                                    <td><?= number_format($warehouse['capacity'] ?? 0, 2) ?> m²</td>
                                    <td>
                                        <span class="status-badge <?= $warehouse['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                            <?= $warehouse['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $warehouse['manager_id'] ? '<i class="bi bi-person-check text-success"></i> Assigned' : '<i class="bi bi-person-x text-muted"></i> Unassigned' ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?=base_url('/warehouse-manager/warehouse/view/' . $warehouse['id'])?>" 
                                           class="btn btn-sm btn-info btn-action" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-3">No warehouses assigned to you.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
