<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .page-break { page-break-after: always; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #6c757d; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        .metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .metric-card { padding: 20px; border: 1px solid #ddd; text-align: center; background-color: #f8f9fa; }
        .metric-card h3 { margin: 0; font-size: 28px; color: #495057; }
        .metric-card p { margin: 5px 0 0 0; color: #6c757d; }
        .section { margin-bottom: 30px; }
        .section h3 { color: #495057; border-bottom: 2px solid #dee2e6; padding-bottom: 5px; }
        .progress-bar { width: 100%; height: 20px; background-color: #e9ecef; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background-color: #28a745; transition: width 0.3s ease; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .kpi-list { list-style: none; padding: 0; }
        .kpi-list li { padding: 10px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; }
        .kpi-list li:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?></h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <div class="section">
        <h3>Key Performance Indicators</h3>
        <div class="metrics-grid">
            <div class="metric-card">
                <h3><?= $stats['total_movements'] ?></h3>
                <p>Total Movements</p>
            </div>
            <div class="metric-card">
                <h3><?= $stats['today_movements'] ?></h3>
                <p>Today's Movements</p>
            </div>
            <div class="metric-card">
                <h3><?= $stats['week_movements'] ?></h3>
                <p>This Week</p>
            </div>
            <div class="metric-card">
                <h3><?= $stats['month_movements'] ?></h3>
                <p>This Month</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Inventory Overview</h3>
        <ul class="kpi-list">
            <li>
                <span>Total Items in Inventory</span>
                <strong><?= number_format($stats['inventory_stats']['total_items'] ?? 0) ?></strong>
            </li>
            <li>
                <span>Total Inventory Value</span>
                <strong>₱<?= number_format($stats['inventory_stats']['total_value'] ?? 0, 2) ?></strong>
            </li>
            <li>
                <span>Low Stock Items</span>
                <strong style="color: #dc3545;"><?= number_format($stats['inventory_stats']['low_stock_items'] ?? 0) ?></strong>
            </li>
            <li>
                <span>Items Expiring Soon</span>
                <strong style="color: #ffc107;"><?= number_format($stats['inventory_stats']['expiring_items'] ?? 0) ?></strong>
            </li>
        </ul>
    </div>

    <div class="section">
        <h3>Materials Management</h3>
        <ul class="kpi-list">
            <li>
                <span>Total Materials</span>
                <strong><?= number_format($stats['material_stats']['total_materials'] ?? 0) ?></strong>
            </li>
            <li>
                <span>Active Materials</span>
                <strong><?= number_format($stats['material_stats']['active_materials'] ?? 0) ?></strong>
            </li>
            <li>
                <span>Material Categories</span>
                <strong><?= number_format($stats['material_stats']['total_categories'] ?? 0) ?></strong>
            </li>
            <li>
                <span>Added This Month</span>
                <strong><?= number_format($stats['material_stats']['recently_added'] ?? 0) ?></strong>
            </li>
        </ul>
    </div>

    <div class="section">
        <h3>Activity Trends</h3>
        <div style="margin-bottom: 15px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Daily Average (This Week)</span>
                <strong><?= number_format($stats['week_movements'] / 7, 1) ?> movements/day</strong>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= min(($stats['week_movements'] / 7) * 5, 100) ?>%"></div>
            </div>
        </div>
        <div style="margin-bottom: 15px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Daily Average (This Month)</span>
                <strong><?= number_format($stats['month_movements'] / 30, 1) ?> movements/day</strong>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= min(($stats['month_movements'] / 30) * 5, 100) ?>%"></div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Performance Score</h3>
        <div style="text-align: center; padding: 30px; background-color: #f8f9fa; border-radius: 10px;">
            <h2 style="color: #28a745; font-size: 48px;">
                <?php 
                $score = 100;
                if ($stats['inventory_stats']['low_stock_items'] > 0) $score -= 20;
                if ($stats['inventory_stats']['expiring_items'] > 0) $score -= 15;
                if ($stats['today_movements'] < 5) $score -= 10;
                echo $score;
                ?>%
            </h2>
            <p>Overall Warehouse Performance</p>
        </div>
    </div>

    <div class="footer">
        <p>End of Report</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
