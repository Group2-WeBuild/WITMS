<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\DepartmentModel;
use App\Models\WarehouseModel;

/**
 * Department Seeder
 * 
 * Seeds the departments table with initial WeBuild company departments.
 * This seeder creates both Central Office and Warehouse departments.
 * 
 * References:
 * - Migration: 2025-11-09-149990_CreateDepartmentsTable.php
 * - Model: App\Models\DepartmentModel
 * - Dependencies: WarehouseSeeder (must run first to get warehouse IDs)
 * 
 * Usage:
 * php spark db:seed DepartmentSeeder
 */
class DepartmentSeeder extends Seeder
{
    public function run()
    {
        // Initialize models
        $departmentModel = new DepartmentModel();
        
        // Check if warehouses exist
        $warehouseModel = new WarehouseModel();
        $warehouses = $warehouseModel->findAll();
        
        // Create a mapping of warehouse names to IDs
        $warehouseMap = [];
        foreach ($warehouses as $warehouse) {
            $warehouseMap[$warehouse['name']] = $warehouse['id'];
        }        // Department data matching UserSeeder references
        $departments = [
            // Central Office Departments (warehouse_id = null)
            [
                'name'            => 'Executive',
                'description'     => 'Top management and executive decision makers',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'executive@webuild.com',
                'contact_phone'   => '+63-2-8888-1000',
                'is_active'       => 1,
            ],
            [
                'name'            => 'Finance',
                'description'     => 'Accounting, financial management, AP/AR operations',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'finance@webuild.com',
                'contact_phone'   => '+63-2-8888-1001',
                'is_active'       => 1,
            ],
            [
                'name'            => 'Procurement',
                'description'     => 'Purchasing, supplier management, and procurement operations',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'procurement@webuild.com',
                'contact_phone'   => '+63-2-8888-1002',
                'is_active'       => 1,
            ],
            [
                'name'            => 'Information Technology',
                'description'     => 'IT infrastructure, system administration, and technical support',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'it@webuild.com',
                'contact_phone'   => '+63-2-8888-1003',
                'is_active'       => 1,
            ],
            [
                'name'            => 'Quality Control',
                'description'     => 'Inventory auditing and quality assurance',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'qc@webuild.com',
                'contact_phone'   => '+63-2-8888-1004',
                'is_active'       => 1,
            ],
        ];        // Add Warehouse Operations departments if warehouses exist
        if (!empty($warehouses)) {
            foreach ($warehouses as $index => $warehouse) {
                $departments[] = [
                    'name'            => 'Warehouse Operations',
                    'description'     => "Warehouse operations and inventory management for {$warehouse['name']}",
                    'warehouse_id'    => $warehouse['id'],
                    'department_head' => null,
                    'contact_email'   => "warehouse{$warehouse['id']}@webuild.com",
                    'contact_phone'   => '+63-2-8888-' . (2000 + $index),
                    'is_active'       => 1,
                ];
            }
        } else {
            // If no warehouses exist yet, create a general Warehouse Operations department
            $departments[] = [
                'name'            => 'Warehouse Operations',
                'description'     => 'Warehouse operations and inventory management',
                'warehouse_id'    => null,
                'department_head' => null,
                'contact_email'   => 'warehouse@webuild.com',
                'contact_phone'   => '+63-2-8888-2000',
                'is_active'       => 1,
            ];
        }

        $successCount = 0;
        $errorCount = 0;

        // Insert departments
        foreach ($departments as $department) {
            try {
                $result = $departmentModel->insert($department);
                
                if ($result) {
                    $successCount++;
                    $warehouseName = $department['warehouse_id'] 
                        ? "Warehouse ID: {$department['warehouse_id']}" 
                        : "Central Office";
                    echo "✓ Created: {$department['name']} - {$warehouseName}\n";
                } else {
                    $errorCount++;
                    $errors = $departmentModel->errors();
                    echo "✗ Failed: {$department['name']} - " . json_encode($errors) . "\n";
                }
            } catch (\Exception $e) {
                $errorCount++;
                echo "✗ Exception for {$department['name']}: {$e->getMessage()}\n";
            }
        }
    }
}
