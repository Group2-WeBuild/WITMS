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
        .header h1 { color: #d63384; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .alert { padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?></h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <?php if (empty($lowStock)): ?>
        <div class="alert">
            <strong>Good News!</strong> No items are currently below reorder level.
        </div>
    <?php else: ?>
        <div class="alert">
            <strong>Warning:</strong> <?= count($lowStock) ?> item(s) are below reorder level and require replenishment.
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Material</th>
                    <th>Warehouse</th>
                    <th>Current Qty</th>
                    <th>Reorder Level</th>
                    <th>Shortage</th>
                    <th>Reorder Qty</th>
                    <th>Unit Cost</th>
                    <th>Value Needed</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStock as $item): ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td><?= esc($item['material_name']) ?></td>
                        <td><?= esc($item['warehouse_name']) ?></td>
                        <td class="text-right" style="color: red; font-weight: bold;"><?= number_format($item['quantity'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['reorder_level'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['reorder_level'] - $item['quantity'], 2) ?></td>
                        <td class="text-right"><?= number_format($item['reorder_quantity'], 2) ?></td>
                        <td class="text-right">₱<?= number_format($item['unit_cost'], 2) ?></td>
                        <td class="text-right">₱<?= number_format($item['reorder_quantity'] * $item['unit_cost'], 2) ?></td>
                        <td><?= esc($item['location_in_warehouse'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" class="text-right">Total Investment Needed:</th>
                    <th class="text-right">₱<?= number_format(array_sum(array_map(function($item) { return $item['reorder_quantity'] * $item['unit_cost']; }, $lowStock)), 2) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    <?php endif; ?>

    <div class="footer">
        <p>End of Report</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>
</body>
</html>
