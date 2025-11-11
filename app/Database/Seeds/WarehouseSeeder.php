<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'       => 'Main Warehouse',
                'code'       => 'WH-001',
                'location'   => 'Metro Manila, Philippines',
                'capacity'   => 5000.00,
                'manager_id' => null,
                'is_active'  => true,
            ],
            [
                'name'       => 'Secondary Warehouse',
                'code'       => 'WH-002',
                'location'   => 'Cebu City, Philippines',
                'capacity'   => 3000.00,
                'manager_id' => null,
                'is_active'  => true,
            ],
            [
                'name'       => 'Storage Facility A',
                'code'       => 'WH-003',
                'location'   => 'Davao City, Philippines',
                'capacity'   => 2000.00,
                'manager_id' => null,
                'is_active'  => true,
            ],
        ];

        /** @var \CodeIgniter\Database\BaseBuilder $builder */
        $builder = $this->db->table('warehouses');
        $builder->insertBatch($data);
    }
}
