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
        .header h1 { color: #dc3545; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .alert { padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; margin-bottom: 20px; }
        .days-30 { background-color: #fff3cd; }
        .days-7 { background-color: #f8d7da; }
        .expired { background-color: #d1ecf1; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?> (Next 30 Days)</h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <?php if (empty($expiring)): ?>
        <div class="alert">
            <strong>Good News!</strong> No items are expiring in the next 30 days.
        </div>
    <?php else: ?>
        <div class="alert">
            <strong>Attention:</strong> <?= count($expiring) ?> item(s) will expire within the next 30 days.
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Material</th>
                    <th>Warehouse</th>
                    <th>Quantity</th>
                    <th>Batch Number</th>
                    <th>Expiration Date</th>
                    <th>Days Until Expiry</th>
                    <th>Unit Cost</th>
                    <th>Value at Risk</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expiring as $item): ?>
                    <?php 
                    $daysUntil = (strtotime($item['expiration_date']) - strtotime(date('Y-m-d'))) / 86400;
                    $rowClass = '';
                    if ($daysUntil < 0) $rowClass = 'expired';
                    elseif ($daysUntil <= 7) $rowClass = 'days-7';
                    elseif ($daysUntil <= 30) $rowClass = 'days-30';
                    ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $item['id'] ?></td>
                        <td><?= esc($item['material_name']) ?></td>
                        <td><?= esc($item['warehouse_name']) ?></td>
                        <td class="text-right"><?= number_format($item['quantity'], 2) ?></td>
                        <td><?= esc($item['batch_number'] ?? '-') ?></td>
                        <td><?= date('M d, Y', strtotime($item['expiration_date'])) ?></td>
                        <td class="text-center">
                            <?php if ($daysUntil < 0): ?>
                                <span style="color: red; font-weight: bold;">EXPIRED</span>
                            <?php else: ?>
                                <?= round($daysUntil) ?> days
                            <?php endif; ?>
                        </td>
                        <td class="text-right">₱<?= number_format($item['unit_cost'], 2) ?></td>
                        <td class="text-right">₱<?= number_format($item['quantity'] * $item['unit_cost'], 2) ?></td>
                        <td><?= esc($item['location_in_warehouse'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="7" class="text-right">Total Value at Risk:</th>
                    <th class="text-right">₱<?= number_format(array_sum(array_map(function($item) { return $item['quantity'] * $item['unit_cost']; }, $expiring)), 2) ?></th>
                    <th colspan="2"></th>
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
