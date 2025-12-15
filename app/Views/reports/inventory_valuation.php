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
        .header h1 { color: #0dcaf0; margin-bottom: 5px; }
        .header p { color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 11px; }
        .summary { margin-bottom: 20px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .summary-item { padding: 15px; border: 1px solid #ddd; text-align: center; background-color: #f8f9fa; }
        .summary-item h4 { margin: 0; font-size: 24px; color: #0dcaf0; }
        .summary-item p { margin: 5px 0 0 0; color: #666; }
        .category-header { background-color: #e9ecef; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= esc($title) ?></h1>
        <p>Generated on: <?= $generated_at ?> by: <?= esc($generated_by) ?></p>
    </div>

    <div class="summary">
        <h3>Valuation Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <h4>₱<?= number_format(array_sum(array_column($valuation, 'total_value')), 2) ?></h4>
                <p>Total Inventory Value</p>
            </div>
            <div class="summary-item">
                <h4><?= count($valuation) ?></h4>
                <p>Unique Materials</p>
            </div>
            <div class="summary-item">
                <h4>₱<?= number_format(array_sum(array_column($valuation, 'total_value')) / array_sum(array_column($valuation, 'total_quantity')), 2) ?></h4>
                <p>Average Unit Cost</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Material</th>
                <th>Code</th>
                <th>Total Quantity</th>
                <th>Unit</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Value %</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalValue = array_sum(array_column($valuation, 'total_value'));
            $currentCategory = null;
            foreach ($valuation as $item): 
                if ($currentCategory !== $item['category_name']) {
                    $currentCategory = $item['category_name'];
            ?>
                <tr class="category-header">
                    <td colspan="8"><?= esc($item['category_name']) ?></td>
                </tr>
            <?php } ?>
                <tr>
                    <td></td>
                    <td><?= esc($item['material_name']) ?></td>
                    <td><code><?= esc($item['material_code']) ?></code></td>
                    <td class="text-right"><?= number_format($item['total_quantity'], 2) ?></td>
                    <td><?= esc($item['unit_name']) ?></td>
                    <td class="text-right">₱<?= number_format($item['unit_cost'], 2) ?></td>
                    <td class="text-right">₱<?= number_format($item['total_value'], 2) ?></td>
                    <td class="text-right"><?= number_format(($item['total_value'] / $totalValue) * 100, 1) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="6" class="text-right">Grand Total:</td>
                <td class="text-right">₱<?= number_format($totalValue, 2) ?></td>
                <td class="text-right">100.0%</td>
            </tr>
        </tfoot>
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
