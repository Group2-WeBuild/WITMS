<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Activity - WITMS</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <?= view('templates/mobile_styles') ?>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-content {
            margin-left: 250px;
            padding-top: 90px;
            padding: 20px;
        }
        
        .activity-item {
            border-left: 3px solid #007bff;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .activity-item.receive {
            border-left-color: #28a745;
        }
        
        .activity-item.issue {
            border-left-color: #ffc107;
        }
        
        .activity-item.transfer {
            border-left-color: #17a2b8;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?= view('templates/sidebar_navigation', ['user' => $user ?? []]) ?>
    
    <div class="main-content">
        <?= view('templates/top_navbar', ['user' => $user ?? [], 'page_title' => 'My Activity']) ?>
        
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> My Activity Log</h5>
                        </div>
                        <div class="card-body">
                            <!-- Filter Options -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <select class="form-select" id="filter-type">
                                        <option value="">All Activities</option>
                                        <option value="receive">Receive Stock</option>
                                        <option value="issue">Issue Stock</option>
                                        <option value="transfer">Stock Transfer</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" id="filter-date" placeholder="Filter by date">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                                        <i class="bi bi-funnel"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Activity List -->
                            <div id="activity-list">
                                <?php if (isset($activities) && count($activities) > 0): ?>
                                    <?php foreach ($activities as $activity): ?>
                                        <?php $movementType = $activity['movement_type'] ?? ''; ?>
                                        <div class="activity-item <?= strtolower($movementType) ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php if ($movementType == 'Receipt'): ?>
                                                            <i class="bi bi-box-arrow-in-down text-success"></i> Received Stock
                                                        <?php elseif ($movementType == 'Issue'): ?>
                                                            <i class="bi bi-box-arrow-up text-warning"></i> Issued Stock
                                                        <?php elseif ($movementType == 'Transfer'): ?>
                                                            <i class="bi bi-arrow-left-right text-info"></i> Stock Transfer
                                                        <?php else: ?>
                                                            <i class="bi bi-arrow-repeat text-secondary"></i> <?= esc($movementType) ?>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <p class="mb-1">
                                                        <strong><?= esc($activity['material_name']) ?></strong> 
                                                        (<?= esc($activity['material_code']) ?>)
                                                    </p>
                                                    <p class="mb-1">
                                                        Quantity: <strong><?= $activity['quantity'] ?></strong> 
                                                        <?= esc($activity['unit'] ?? 'units') ?>
                                                    </p>
                                                    <?php if (!empty($activity['warehouse_name'])): ?>
                                                        <p class="mb-1">
                                                            <small class="text-muted">
                                                                Warehouse: <?= esc($activity['warehouse_name']) ?>
                                                            </small>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($activity['reference_number'])): ?>
                                                        <p class="mb-1">
                                                            <small class="text-muted">
                                                                Ref: <?= esc($activity['reference_number']) ?>
                                                            </small>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">
                                                        <?= date('M d, Y', strtotime($activity['created_at'])) ?><br>
                                                        <?= date('h:i A', strtotime($activity['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-inbox display-1 text-muted"></i>
                                        <p class="text-muted mt-3">No activity records found</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilters() {
            const type = document.getElementById('filter-type').value;
            const date = document.getElementById('filter-date').value;
            
            let url = '<?= base_url('warehouse-staff/activity') ?>?';
            if (type) url += 'type=' + type + '&';
            if (date) url += 'date=' + date;
            
            window.location.href = url;
        }
    </script>
</body>
</html>
