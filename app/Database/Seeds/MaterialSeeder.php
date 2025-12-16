<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\MaterialModel;
use App\Models\MaterialCategoryModel;
use App\Models\UnitsOfMeasureModel;
use App\Libraries\QRCodeLibrary;

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

        // Sample materials data (only 2 materials)
        $materials = [
            [
                'name' => 'Steel Rebar 12mm',
                'code' => 'STEEL-12MM-001',
                'qrcode' => null, // Will be generated using QRCodeLibrary
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
                'name' => 'Cement Portland Type 1',
                'code' => 'CEM-PT1-001',
                'qrcode' => null, // Will be generated using QRCodeLibrary
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
        ];

        $qrLibrary = new QRCodeLibrary();
        $successCount = 0;
        $errorCount = 0;

        foreach ($materials as $material) {
            try {
                // Create material first
                $result = $materialModel->createMaterial($material);
                
                if ($result) {
                    $materialId = $result;
                    
                    // Get the created material to generate QR code
                    $createdMaterial = $materialModel->find($materialId);
                    
                    if ($createdMaterial) {
                        // Generate QR code using QRCodeLibrary
                        $timestamp = microtime(true);
                        $filename = 'material_' . $materialId . '_' . str_replace('.', '_', $timestamp);
                        $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
                        
                        // Ensure directory exists
                        $qrPath = WRITEPATH . 'qrcodes/';
                        if (!is_dir($qrPath)) {
                            if (!mkdir($qrPath, 0755, true)) {
                                throw new \RuntimeException('Failed to create QR code directory');
                            }
                        }
                        
                        // Generate QR code directly to file
                        $qrLibrary->generateMaterialQR(
                            $createdMaterial['id'],
                            $createdMaterial['code'],
                            $createdMaterial['name'],
                            $filepath
                        );
                        
                        // Update material with QR code filename (optional, for reference)
                        // The QR code is stored as a file, but we can store a reference if needed
                        $qrCodeReference = basename($filepath);
                        
                        $successCount++;
                        echo "✓ Created: {$material['name']} ({$material['code']}) - QR Code: {$qrCodeReference}\n";
                    } else {
                        $successCount++;
                        echo "✓ Created: {$material['name']} ({$material['code']}) - QR Code generation skipped (material not found after creation)\n";
                    }
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
