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
        .header h1 { color: #333; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .summary { margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?></h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-row">
            <span>Total Items:</span>
            <strong><?= count($inventory) ?></strong>
        </div>
        <div class="summary-row">
            <span>Total Value:</span>
            <strong>₱<?= number_format(array_sum(array_column($inventory, 'total_value')), 2) ?></strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Warehouse</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Batch</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inventory as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= esc($item['material_name']) ?></td>
                    <td><?= esc($item['warehouse_name']) ?></td>
                    <td class="text-right"><?= number_format($item['quantity'], 2) ?></td>
                    <td class="text-right">₱<?= number_format($item['unit_cost'], 2) ?></td>
                    <td class="text-right">₱<?= number_format($item['quantity'] * $item['unit_cost'], 2) ?></td>
                    <td><?= esc($item['batch_number'] ?? '-') ?></td>
                    <td><?= esc($item['location_in_warehouse'] ?? '-') ?></td>
                    <td class="text-center">
                        <?php if ($item['quantity'] <= $item['reorder_level']): ?>
                            <span style="color: red;">Low Stock</span>
                        <?php else: ?>
                            <span style="color: green;">In Stock</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>End of Report</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
