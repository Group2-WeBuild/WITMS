<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
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
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .info-row {
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
            margin-top: 5px;
        }
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 600;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .stat-box .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="bi bi-building"></i> Warehouse Details</h2>
                        <p class="text-muted mb-0"><?= esc($warehouse['code']) ?></p>
                    </div>
                    <div>
                        <a href="<?=base_url('/warehouse-manager/warehouse-management')?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="info-card">
                            <h4 class="mb-4"><i class="bi bi-info-circle"></i> Basic Information</h4>
                            
                            <div class="info-row">
                                <div class="info-label">Warehouse Name</div>
                                <div class="info-value"><?= esc($warehouse['name']) ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Warehouse Code</div>
                                <div class="info-value"><strong><?= esc($warehouse['code']) ?></strong></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Status</div>
                                <div class="info-value">
                                    <span class="status-badge <?= $warehouse['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $warehouse['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Capacity</div>
                                <div class="info-value"><?= number_format($warehouse['capacity'], 2) ?> m²</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Manager Status</div>
                                <div class="info-value">
                                    <?= $warehouse['manager_id'] ? '<i class="bi bi-person-check text-success"></i> Assigned' : '<i class="bi bi-person-x text-muted"></i> Unassigned' ?>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div class="info-card">
                            <h4 class="mb-4"><i class="bi bi-geo-alt"></i> Location Information</h4>
                            
                            <div class="info-row">
                                <div class="info-label">Complete Address</div>
                                <div class="info-value"><?= esc($formattedAddress) ?></div>
                            </div>

                            <?php if (!empty($warehouse['street_address'])): ?>
                            <div class="info-row">
                                <div class="info-label">Street Address</div>
                                <div class="info-value"><?= esc($warehouse['street_address']) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($warehouse['barangay'])): ?>
                            <div class="info-row">
                                <div class="info-label">Barangay</div>
                                <div class="info-value"><?= esc($warehouse['barangay']) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="info-row">
                                <div class="info-label">City/Municipality</div>
                                <div class="info-value"><?= esc($warehouse['city'] ?? 'N/A') ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Province</div>
                                <div class="info-value"><?= esc($warehouse['province'] ?? 'N/A') ?></div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Region</div>
                                <div class="info-value"><?= esc($warehouse['region'] ?? 'N/A') ?></div>
                            </div>

                            <?php if (!empty($warehouse['postal_code'])): ?>
                            <div class="info-row">
                                <div class="info-label">Postal Code</div>
                                <div class="info-value"><?= esc($warehouse['postal_code']) ?></div>
                            </div>
                            <?php endif; ?>

                            <div class="info-row">
                                <div class="info-label">Country</div>
                                <div class="info-value"><?= esc($warehouse['country'] ?? 'Philippines') ?></div>
                            </div>
                        </div>

                        <!-- GPS Coordinates -->
                        <?php if (!empty($warehouse['latitude']) && !empty($warehouse['longitude'])): ?>
                        <div class="info-card">
                            <h4 class="mb-4"><i class="bi bi-pin-map"></i> GPS Coordinates</h4>
                            
                            <div class="info-row">
                                <div class="info-label">Latitude</div>
                                <div class="info-value"><?= number_format($warehouse['latitude'], 8) ?>°</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Longitude</div>
                                <div class="info-value"><?= number_format($warehouse['longitude'], 8) ?>°</div>
                            </div>

                            <div class="info-row">
                                <div class="info-label">Google Maps</div>
                                <div class="info-value">
                                    <a href="https://www.google.com/maps?q=<?= $warehouse['latitude'] ?>,<?= $warehouse['longitude'] ?>" 
                                       target="_blank" class="btn btn-primary btn-sm">
                                        <i class="bi bi-map"></i> View on Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Stats -->
                        <div class="info-card">
                            <h4 class="mb-4"><i class="bi bi-graph-up"></i> Statistics</h4>
                            
                            <div class="stat-box mb-3">
                                <div class="number"><?= $inventoryCount ?></div>
                                <div class="label">Inventory Items</div>
                            </div>

                            <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <div class="number"><?= number_format($warehouse['capacity'], 0) ?></div>
                                <div class="label">Capacity (m²)</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="info-card">
                            <h4 class="mb-4"><i class="bi bi-lightning"></i> Quick Actions</h4>
                            
                            <div class="d-grid gap-2">
                                <a href="<?=base_url('/warehouse-manager/warehouse/map')?>" 
                                   class="btn btn-info">
                                    <i class="bi bi-map"></i> View on Map
                                </a>

                                <a href="<?=base_url('/warehouse-manager/inventory?warehouse_id=' . $warehouse['id'])?>" 
                                   class="btn btn-secondary">
                                    <i class="bi bi-box-seam"></i> View Inventory
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
