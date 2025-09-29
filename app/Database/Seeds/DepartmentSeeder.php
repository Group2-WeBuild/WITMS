<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'               => 'Warehouse Operations',
                'description'        => 'Manages day-to-day warehouse operations, inventory management, and logistics coordination.',
                'warehouse_location' => 'All Warehouses',
                'department_head'    => 'Juan Carlos Martinez',
                'contact_email'      => 'warehouse.ops@webuild.com',
                'contact_phone'      => '+63-2-8123-4567',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Quality Control',
                'description'        => 'Ensures product quality standards, conducts inspections, and manages quality assurance processes.',
                'warehouse_location' => 'All Warehouses',
                'department_head'    => 'Maria Elena Santos',
                'contact_email'      => 'quality@webuild.com',
                'contact_phone'      => '+63-2-8234-5678',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Procurement',
                'description'        => 'Handles supplier relationships, purchase orders, and procurement processes for construction materials.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Roberto Antonio Cruz',
                'contact_email'      => 'procurement@webuild.com',
                'contact_phone'      => '+63-2-8345-6789',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Finance - Central Office',
                'description'        => 'Manages financial operations, accounts payable/receivable, and financial reporting.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Catherine Mae Gonzales',
                'contact_email'      => 'finance@webuild.com',
                'contact_phone'      => '+63-2-8456-7890',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Information Technology',
                'description'        => 'Manages IT infrastructure, system administration, and technical support for WITMS.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Marjovic Prato Alejado',
                'contact_email'      => 'it@webuild.com',
                'contact_phone'      => '+63-2-8567-8901',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Executive',
                'description'        => 'Top management and executive leadership for strategic decision making.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Eduardo Fernandez Ramirez',
                'contact_email'      => 'executive@webuild.com',
                'contact_phone'      => '+63-2-8678-9012',
                'is_active'          => 1,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert departments
        $this->db->table('departments')->insertBatch($data);
        
        echo "WeBuild department structure seeded successfully!\n";
    }
}
