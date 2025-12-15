<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
            .page-break { page-break-after: always; }
        }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #198754; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .summary { margin-bottom: 20px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .summary-item { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .summary-item h4 { margin: 0; font-size: 18px; }
        .summary-item p { margin: 5px 0 0 0; color: #666; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-receipt { background-color: #d4edda; color: #155724; }
        .badge-issue { background-color: #f8d7da; color: #721c24; }
        .badge-transfer { background-color: #cce5ff; color: #004085; }
        .badge-adjustment { background-color: #fff3cd; color: #856404; }
        .badge-return { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?></h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <div class="summary">
        <h3>Movement Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <h4><?= count($movements) ?></h4>
                <p>Total Movements</p>
            </div>
            <div class="summary-item">
                <h4><?= count(array_filter($movements, fn($m) => $m['movement_type'] === 'Receipt')) ?></h4>
                <p>Receipts</p>
            </div>
            <div class="summary-item">
                <h4><?= count(array_filter($movements, fn($m) => $m['movement_type'] === 'Issue')) ?></h4>
                <p>Issues</p>
            </div>
            <div class="summary-item">
                <h4><?= count(array_filter($movements, fn($m) => $m['movement_type'] === 'Transfer')) ?></h4>
                <p>Transfers</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ref #</th>
                <th>Date</th>
                <th>Type</th>
                <th>Material</th>
                <th>From</th>
                <th>To</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Performed By</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movements as $movement): ?>
                <tr>
                    <td><code><?= esc($movement['reference_number']) ?></code></td>
                    <td><?= date('M d, Y H:i', strtotime($movement['movement_date'])) ?></td>
                    <td>
                        <span class="badge badge-<?= strtolower($movement['movement_type']) ?>">
                            <?= ucfirst($movement['movement_type']) ?>
                        </span>
                    </td>
                    <td><?= esc($movement['material_name']) ?></td>
                    <td><?= esc($movement['from_warehouse_name'] ?? '-') ?></td>
                    <td><?= esc($movement['to_warehouse_name'] ?? '-') ?></td>
                    <td class="text-right"><?= number_format($movement['quantity']) ?></td>
                    <td><?= esc($movement['unit_abbr'] ?? 'units') ?></td>
                    <td><?= esc(trim(($movement['performed_by_first'] ?? '') . ' ' . ($movement['performed_by_last'] ?? ''))) ?></td>
                    <td class="text-center">
                        <?php 
                        if ($movement['movement_type'] === 'Transfer') {
                            echo $movement['approved_by'] ? 'Approved' : 'Pending';
                        } else {
                            echo 'Completed';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>End of Report - Showing <?= count($movements) ?> movements</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
