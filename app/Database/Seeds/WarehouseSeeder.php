<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\RoleModel;
use App\Models\UserModel;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        // Get Warehouse Manager role and users dynamically
        $roleModel = new RoleModel();
        $userModel = new UserModel();
        
        $warehouseManagerRole = $roleModel->where('name', 'Warehouse Manager')->first();
        
        if (!$warehouseManagerRole) {
            echo "✗ Error: Warehouse Manager role not found. Please run RoleSeeder first.\n";
            return;
        }
        
        // Get all warehouse managers
        $warehouseManagers = $userModel->where('role_id', $warehouseManagerRole['id'])
                                       ->where('is_active', 1)
                                       ->findAll();
        
        if (empty($warehouseManagers)) {
            echo "✗ Error: No Warehouse Managers found. Please run UserSeeder first.\n";
            return;
        }
        
        echo "Found " . count($warehouseManagers) . " Warehouse Manager(s)\n";

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
        echo "✓ Created " . count($locationData) . " warehouse locations\n";

        // STEP 2: Insert warehouses SECOND (with warehouse_location_id and manager_id references)
        // Model validation rules:
        // - name: required, max 100 chars, letters and spaces only (/^[A-Za-z\s]+$/)
        // - code: required, max 20 chars, format WH-001 or WH001 (/^WH-?\d{3,}$/)
        // - warehouse_location_id: optional, must exist in warehouse_locations
        // - capacity: optional, numeric, >= 0
        // - manager_id: REQUIRED (per ITAdministratorController), must be valid Warehouse Manager user
        // - is_active: optional, must be 0 or 1
        
        // Assign managers (cycle through available managers if fewer managers than warehouses)
        $managerCount = count($warehouseManagers);
        
        $warehouseData = [
            [
                'name'                  => 'Warehouse A',
                'code'                  => 'WH-001',
                'warehouse_location_id' => 1, // References warehouse_locations.id
                'capacity'              => 5000.00,
                'manager_id'            => $warehouseManagers[0 % $managerCount]['id'], // Required: assign first manager
                'is_active'             => 1, // Must be 0 or 1 per model validation
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
            [
                'name'                  => 'Warehouse B',
                'code'                  => 'WH-002',
                'warehouse_location_id' => 2, // References warehouse_locations.id
                'capacity'              => 3000.00,
                'manager_id'            => $warehouseManagers[1 % $managerCount]['id'], // Required: assign next available manager
                'is_active'             => 1, // Must be 0 or 1 per model validation
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
            [
                'name'                  => 'Warehouse C',
                'code'                  => 'WH-003',
                'warehouse_location_id' => 3, // References warehouse_locations.id
                'capacity'              => 2000.00,
                'manager_id'            => $warehouseManagers[2 % $managerCount]['id'], // Required: assign next available manager
                'is_active'             => 1, // Must be 0 or 1 per model validation
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],        
        ];
        
        // Insert warehouses after locations exist
        $this->db->table('warehouses')->insertBatch($warehouseData);
        echo "✓ Created " . count($warehouseData) . " warehouses\n";

        // STEP 3: Create user_warehouse_assignments for each manager-warehouse pair
        // This is required by ITAdministratorController when creating warehouses
        $assignmentData = [];
        $primaryAssigned = []; // Track which users already have a primary warehouse
        
        // Get the warehouse IDs we just inserted
        $warehouses = $this->db->table('warehouses')
                               ->whereIn('code', ['WH-001', 'WH-002', 'WH-003'])
                               ->get()
                               ->getResultArray();
        
        foreach ($warehouses as $index => $warehouse) {
            $managerId = $warehouse['manager_id'];
            $isPrimary = !isset($primaryAssigned[$managerId]) ? 1 : 0; // First assignment for each manager is primary
            
            if ($isPrimary) {
                $primaryAssigned[$managerId] = true;
            }
            
            $assignmentData[] = [
                'user_id'       => $managerId,
                'warehouse_id'  => $warehouse['id'],
                'role_id'       => $warehouseManagerRole['id'],
                'is_primary'    => $isPrimary,
                'is_active'     => 1,
                'assigned_by'   => null, // Seeder - no user session
                'assigned_at'   => date('Y-m-d H:i:s'),
                'notes'         => 'Auto-assigned by WarehouseSeeder',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
        }
        
        if (!empty($assignmentData)) {
            $this->db->table('user_warehouse_assignments')->insertBatch($assignmentData);
            echo "✓ Created " . count($assignmentData) . " user warehouse assignments\n";
        }
        
        echo "✓ Warehouse seeding completed successfully!\n";
    }
}
