<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Department Seeder
 * 
 * Seeds the departments table with initial WeBuild company departments.
 * 
 * Usage:
 * php spark db:seed DepartmentSeeder
 */
class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Central Office Departments
            [
                'name'            => 'Executive Management',
                'description'     => 'Top management and decision makers',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'executive@webuild.com',
                'contact_phone'   => null,
                'is_active'       => true,
            ],
            [
                'name'            => 'Finance Department',
                'description'     => 'Accounting and financial management',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'finance@webuild.com',
                'contact_phone'   => null,
                'is_active'       => true,
            ],
            [
                'name'            => 'Procurement Department',
                'description'     => 'Purchasing and supplier management',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'procurement@webuild.com',
                'contact_phone'   => null,
                'is_active'       => true,
            ],
            [
                'name'            => 'IT Department',
                'description'     => 'Information technology and systems',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'it@webuild.com',
                'contact_phone'   => null,
                'is_active'       => true,
            ],
            
            // Warehouse Departments (these will be created after warehouses exist)
            // You'll need to update warehouse_id after running warehouse seeder
        ];

        $this->db->table('departments')->insertBatch($data);
    }
}
