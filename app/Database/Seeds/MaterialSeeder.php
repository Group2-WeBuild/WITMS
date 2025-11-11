<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\MaterialModel;
use App\Models\MaterialCategoryModel;
use App\Models\UnitsOfMeasureModel;

class MaterialSeeder extends Seeder
{
    public function run()
    {
        $materialModel = new MaterialModel();
        $categoryModel = new MaterialCategoryModel();
        $unitModel = new UnitsOfMeasureModel();

        // Get categories and units
        $categories = $categoryModel->findAll();
        $units = $unitModel->findAll();

        if (empty($categories) || empty($units)) {
            echo "⚠ Warning: Please run MaterialCategorySeeder and UnitsOfMeasureSeeder first!\n";
            return;
        }

        // Create category and unit maps
        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['name']] = $cat['id'];
        }

        $unitMap = [];
        foreach ($units as $unit) {
            $unitMap[$unit['abbreviation']] = $unit['id'];
        }

        // Sample materials data
        $materials = [
            // Construction Materials            
            [
                'name' => 'Steel Rebar 12mm',
                'code' => 'STEEL-12MM-001',
                'qrcode' => 'QR-STL12MM001',
                'category_id' => $categoryMap['Steel'] ?? 1,
                'unit_id' => $unitMap['pcs'] ?? 1,
                'description' => 'High-grade steel rebar for construction',
                'reorder_level' => 100.00,
                'reorder_quantity' => 500.00,
                'unit_cost' => 250.50,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Steel Rebar 16mm',
                'code' => 'STEEL-16MM-001',
                'qrcode' => 'QR-STL16MM001',
                'category_id' => $categoryMap['Steel'] ?? 1,
                'unit_id' => $unitMap['pcs'] ?? 1,
                'description' => 'Heavy-duty steel rebar 16mm diameter',
                'reorder_level' => 80.00,
                'reorder_quantity' => 400.00,
                'unit_cost' => 385.75,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Cement Portland Type 1',
                'code' => 'CEM-PT1-001',
                'qrcode' => 'QR-CEMPT1001',
                'category_id' => $categoryMap['Cement'] ?? 2,
                'unit_id' => $unitMap['bag'] ?? 2,
                'description' => 'Portland Type 1 Cement - 40kg bags',
                'reorder_level' => 200.00,
                'reorder_quantity' => 1000.00,
                'unit_cost' => 245.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Sand (Fine)',
                'code' => 'SAND-FINE-001',
                'qrcode' => 'QR-SNDFINE001',
                'category_id' => $categoryMap['Aggregates'] ?? 3,
                'unit_id' => $unitMap['m3'] ?? 3,
                'description' => 'Fine construction sand',
                'reorder_level' => 10.00,
                'reorder_quantity' => 50.00,
                'unit_cost' => 850.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Gravel 3/4"',
                'code' => 'GRAV-34-001',
                'qrcode' => 'QR-GRAV34001',
                'category_id' => $categoryMap['Aggregates'] ?? 3,
                'unit_id' => $unitMap['m3'] ?? 3,
                'description' => '3/4 inch gravel for concrete',
                'reorder_level' => 10.00,
                'reorder_quantity' => 50.00,
                'unit_cost' => 920.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Plywood 4x8 (1/2")',
                'code' => 'PLY-48-12-001',
                'qrcode' => 'QR-PLY4812001',
                'category_id' => $categoryMap['Wood Products'] ?? 4,
                'unit_id' => $unitMap['sheet'] ?? 4,
                'description' => '4x8 feet plywood, 1/2 inch thick',
                'reorder_level' => 50.00,
                'reorder_quantity' => 200.00,
                'unit_cost' => 485.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Marine Plywood 4x8',
                'code' => 'PLY-MAR-48-001',
                'qrcode' => 'QR-PLYMAR48001',
                'category_id' => $categoryMap['Wood Products'] ?? 4,
                'unit_id' => $unitMap['sheet'] ?? 4,
                'description' => 'Marine grade plywood 4x8 feet',
                'reorder_level' => 30.00,
                'reorder_quantity' => 100.00,
                'unit_cost' => 1250.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],            [
                'name' => 'Paint (White) 4L',
                'code' => 'PAINT-WHT-4L-001',
                'qrcode' => 'QR-PAINTWHT4L',
                'category_id' => $categoryMap['Paints'] ?? 5,
                'unit_id' => $unitMap['gal'] ?? 5,
                'description' => 'White latex paint 4 liters',
                'reorder_level' => 50.00,
                'reorder_quantity' => 200.00,
                'unit_cost' => 685.00,
                'is_perishable' => 1,
                'shelf_life_days' => 730,
                'is_active' => 1
            ],
            [
                'name' => 'Electrical Wire 2.0mm',
                'code' => 'WIRE-20MM-001',
                'qrcode' => 'QR-WIRE20MM001',
                'category_id' => $categoryMap['Electrical'] ?? 6,
                'unit_id' => $unitMap['m'] ?? 6,
                'description' => 'Electrical wire 2.0mm diameter',
                'reorder_level' => 500.00,
                'reorder_quantity' => 2000.00,
                'unit_cost' => 12.50,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
            [
                'name' => 'PVC Pipe 1/2"',
                'code' => 'PVC-PIPE-12-001',
                'qrcode' => 'QR-PVCPIPE12',
                'category_id' => $categoryMap['Plumbing'] ?? 7,
                'unit_id' => $unitMap['m'] ?? 6,
                'description' => 'PVC pipe 1/2 inch diameter',
                'reorder_level' => 100.00,
                'reorder_quantity' => 500.00,
                'unit_cost' => 45.00,
                'is_perishable' => false,
                'shelf_life_days' => null,
                'is_active' => 1
            ],
        ];

        $successCount = 0;
        $errorCount = 0;

        foreach ($materials as $material) {
            try {
                $result = $materialModel->createMaterial($material);
                
                if ($result) {
                    $successCount++;
                    echo "✓ Created: {$material['name']} ({$material['code']})\n";
                } else {
                    $errorCount++;
                    $errors = $materialModel->errors();
                    echo "✗ Failed: {$material['name']} - " . json_encode($errors) . "\n";
                }
            } catch (\Exception $e) {
                $errorCount++;
                echo "✗ Exception for {$material['name']}: {$e->getMessage()}\n";
            }
        }

        echo "\n=== Material Seeding Complete ===\n";
        echo "✓ Success: {$successCount}\n";
        echo "✗ Failed: {$errorCount}\n";
    }
}
