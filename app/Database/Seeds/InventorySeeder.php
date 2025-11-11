<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\InventoryModel;
use App\Models\MaterialModel;
use App\Models\WarehouseModel;

class InventorySeeder extends Seeder
{
    public function run()
    {
        $inventoryModel = new InventoryModel();
        $materialModel = new MaterialModel();
        $warehouseModel = new WarehouseModel();

        // Get materials and warehouses
        $materials = $materialModel->findAll();
        $warehouses = $warehouseModel->findAll();

        if (empty($materials)) {
            echo "⚠ Warning: No materials found. Please run MaterialSeeder first!\n";
            return;
        }

        if (empty($warehouses)) {
            echo "⚠ Warning: No warehouses found. Please run WarehouseSeeder first!\n";
            return;
        }

        // Create inventory records
        $inventoryData = [];
        
        foreach ($materials as $material) {
            foreach ($warehouses as $warehouse) {
                // Random quantity between 50-500
                $quantity = rand(50, 500);
                $reserved = rand(0, 50);
                
                $inventoryData[] = [
                    'material_id' => $material['id'],
                    'warehouse_id' => $warehouse['id'],
                    'batch_number' => 'BATCH-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'quantity' => $quantity,
                    'reserved_quantity' => $reserved,
                    'available_quantity' => $quantity - $reserved,
                    'location_in_warehouse' => 'Aisle ' . chr(65 + rand(0, 5)) . ', Rack ' . rand(1, 10),
                    'expiration_date' => $material['is_perishable'] ? date('Y-m-d', strtotime('+1 year')) : null,
                    'last_counted_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($inventoryData as $item) {
            try {
                $result = $inventoryModel->insert($item);
                
                if ($result) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors = $inventoryModel->errors();
                    echo "✗ Failed: Material ID {$item['material_id']} - " . json_encode($errors) . "\n";
                }
            } catch (\Exception $e) {
                $errorCount++;
                echo "✗ Exception: {$e->getMessage()}\n";
            }
        }

        echo "\n=== Inventory Seeding Complete ===\n";
        echo "✓ Success: {$successCount} inventory records created\n";
        echo "✗ Failed: {$errorCount}\n";
    }
}
