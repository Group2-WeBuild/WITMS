<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WarehouseSeeder extends Seeder
{    public function run()
    {
        // STEP 1: Insert warehouse locations FIRST (no foreign key dependencies)
        $locationData = [
            [
                'street_address'  => 'Km. 14 West Service Road',
                'barangay'        => 'Barangay 178',
                'city'            => 'Pasay City',
                'province'        => 'Metro Manila',
                'region'          => 'National Capital Region (NCR)',
                'postal_code'     => '1300',
                'country'         => 'Philippines',
                'latitude'        => 14.5378,
                'longitude'       => 121.0014,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'street_address'  => 'National Highway',
                'barangay'        => 'Barangay Fatima',
                'city'            => 'General Santos City',
                'province'        => 'South Cotabato',
                'region'          => 'Region XII (SOCCSKSARGEN)',
                'postal_code'     => '9500',
                'country'         => 'Philippines',
                'latitude'        => null,
                'longitude'       => null,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
            [
                'street_address'  => 'Km. 8 McArthur Highway',
                'barangay'        => 'Barangay Panacan',
                'city'            => 'Davao City',
                'province'        => 'Davao del Sur',
                'region'          => 'Region XI (Davao Region)',
                'postal_code'     => '8000',
                'country'         => 'Philippines',
                'latitude'        => 7.0731,
                'longitude'       => 125.6128,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert warehouse locations first
        $this->db->table('warehouse_locations')->insertBatch($locationData);

        // STEP 2: Insert warehouses SECOND (with warehouse_location_id references)
        $warehouseData = [
            [
                'name'                  => 'Warehouse A',
                'code'                  => 'WH-001',
                'warehouse_location_id' => 1, // References warehouse_locations.id
                'capacity'              => 5000.00,
                'manager_id'            => null,
                'is_active'             => true,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
            [
                'name'                  => 'Warehouse B',
                'code'                  => 'WH-002',
                'warehouse_location_id' => 2, // References warehouse_locations.id
                'capacity'              => 3000.00,
                'manager_id'            => null,
                'is_active'             => true,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
            [
                'name'                  => 'Warehouse C',
                'code'                  => 'WH-003',
                'warehouse_location_id' => 3, // References warehouse_locations.id
                'capacity'              => 2000.00,
                'manager_id'            => null,
                'is_active'             => true,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],        ];
        
        // Insert warehouses after locations exist
        $this->db->table('warehouses')->insertBatch($warehouseData);
    }
}
