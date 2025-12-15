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
        .report-card {
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .report-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?= view('templates/top_navbar', ['user' => $user]) ?>
    <?= view('templates/sidebar_navigation', ['user' => $user]) ?>

    <div class="main-content">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2><i class="bi bi-file-earmark-text"></i> Reports</h2>
                <p class="text-muted">Generate and view various warehouse reports</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-primary" onclick="showReportGenerator()">
                    <i class="bi bi-plus-circle"></i> Generate Report
                </button>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-2">Total Items</h6>
                                <h4 class="mb-0"><?= number_format($inventoryStats['total_items'] ?? 0) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-box-seam text-primary fs-2"></i>
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
                                <h6 class="text-muted mb-2">Total Value</h6>
                                <h4 class="mb-0">₱<?= number_format($inventoryStats['total_value'] ?? 0, 2) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-currency-dollar text-success fs-2"></i>
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
                                <h6 class="text-muted mb-2">Low Stock</h6>
                                <h4 class="mb-0"><?= number_format($inventoryStats['low_stock_items'] ?? 0) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-exclamation-triangle text-warning fs-2"></i>
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
                                <h6 class="text-muted mb-2">Materials</h6>
                                <h4 class="mb-0"><?= number_format($materialStats['total_materials'] ?? 0) ?></h4>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-boxes text-info fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Types -->
        <h4 class="mb-3">Available Reports</h4>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('inventory')">
                    <div class="report-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <h5>Inventory Summary</h5>
                    <p class="text-muted">Complete inventory report with quantities and values</p>
                    <small class="text-primary">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('lowstock')">
                    <div class="report-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h5>Low Stock Report</h5>
                    <p class="text-muted">Items below reorder level requiring replenishment</p>
                    <small class="text-warning">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('expiring')">
                    <div class="report-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5>Expiring Items</h5>
                    <p class="text-muted">Items approaching expiration date</p>
                    <small class="text-danger">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('movements')">
                    <div class="report-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <h5>Stock Movements</h5>
                    <p class="text-muted">All stock movements within date range</p>
                    <small class="text-success">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('valuation')">
                    <div class="report-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <h5>Inventory Valuation</h5>
                    <p class="text-muted">Total value of inventory by category</p>
                    <small class="text-info">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="report-card bg-white" onclick="generateReport('performance')">
                    <div class="report-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h5>Warehouse Performance</h5>
                    <p class="text-muted">Key performance indicators and metrics</p>
                    <small class="text-secondary">
                        <i class="bi bi-download"></i> Generate PDF
                    </small>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <h4 class="mb-3">Recent Reports</h4>
        <div class="card">
            <div class="card-body">
                <?php if (!empty($recentReports)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentReports as $report): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= esc($report['name']) ?></h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> 
                                        Generated: <?= date('M d, Y h:i A', strtotime($report['generated_at'])) ?> (PST)
                                    </small>
                                </div>
                                <div>
                                    <a href="<?= $report['url'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?= $report['url'] ?>" target="_blank" class="btn btn-sm btn-primary" onclick="setTimeout(() => window.print(), 500)">
                                        <i class="bi bi-printer"></i> Print
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No reports generated yet</p>
                        <small class="text-muted">Click on any report type above to generate your first report</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Report Generator Modal -->
    <div class="modal fade" id="reportGeneratorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reportForm">
                        <div class="mb-3">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select class="form-select" id="reportType" required>
                                <option value="">Select Report Type</option>
                                <option value="inventory">Inventory Summary</option>
                                <option value="lowstock">Low Stock Report</option>
                                <option value="expiring">Expiring Items</option>
                                <option value="movements">Stock Movements</option>
                                <option value="valuation">Inventory Valuation</option>
                                <option value="performance">Warehouse Performance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dateRange" class="form-label">Date Range</label>
                            <select class="form-select" id="dateRange">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="mb-3" id="customDateRange" style="display:none;">
                            <div class="row">
                                <div class="col-6">
                                    <label for="startDate" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="startDate">
                                </div>
                                <div class="col-6">
                                    <label for="endDate" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="endDate">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="format" class="form-label">Format</label>
                            <select class="form-select" id="format">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="downloadReport()">
                        <i class="bi bi-download"></i> Download Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateReport(type) {
            window.open('<?= base_url('warehouse-manager/reports/generate/') ?>' + type, '_blank');
        }

        function showReportGenerator() {
            const modal = new bootstrap.Modal(document.getElementById('reportGeneratorModal'));
            modal.show();
        }

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const reportType = document.getElementById('reportType').value;
            if (reportType) {
                generateReport(reportType);
                const modal = bootstrap.Modal.getInstance(document.getElementById('reportGeneratorModal'));
                if (modal) modal.hide();
            }
        });

        function downloadReport() {
            const type = document.getElementById('reportType').value;
            const dateRange = document.getElementById('dateRange').value;
            const format = document.getElementById('format').value;
            
            if (!type) {
                alert('Please select a report type');
                return;
            }
            
            generateReport(type);
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportGeneratorModal'));
            if (modal) modal.hide();
        }
    </script>
</body>
</html>