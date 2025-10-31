<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\DepartmentModel;

/**
 * Department Seeder
 * 
 * Seeds the departments table with initial WeBuild company departments.
 * This seeder uses the DepartmentModel to ensure proper validation and data handling.
 * 
 * References: 
 * - Migration: 2025-09-29-140446_CreateDepartmentsTable.php
 * - Model: App\Models\DepartmentModel
 * 
 * Usage:
 * php spark db:seed DepartmentSeeder
 */
class DepartmentSeeder extends Seeder
{
    public function run()
    {
        // Initialize the Department Model
        $departmentModel = new DepartmentModel();

        // Clear existing departments (optional - comment out if you want to keep existing data)
        // $departmentModel->truncate();

        echo "Starting WeBuild department seeding process...\n\n";

        // Department data matching the migration structure
        // Warehouse locations must match ENUM values: Central Office, Warehouse A, Warehouse B, Warehouse C, All Warehouses
        $departments = [
            [
                'name'               => 'Warehouse Operations',
                'description'        => 'Manages day-to-day warehouse operations, inventory management, and logistics coordination.',
                'warehouse_location' => 'All Warehouses',
                'department_head'    => 'Juan Carlos Martinez',
                'contact_email'      => 'warehouse.ops@webuild.com',
                'contact_phone'      => '+63-2-8123-4567',
                'is_active'          => 1,
            ],
            [
                'name'               => 'Quality Control',
                'description'        => 'Ensures product quality standards, conducts inspections, and manages quality assurance processes.',
                'warehouse_location' => 'All Warehouses',
                'department_head'    => 'Maria Elena Santos',
                'contact_email'      => 'quality@webuild.com',
                'contact_phone'      => '+63-2-8234-5678',
                'is_active'          => 1,
            ],
            [
                'name'               => 'Procurement',
                'description'        => 'Handles supplier relationships, purchase orders, and procurement processes for construction materials.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Roberto Antonio Cruz',
                'contact_email'      => 'procurement@webuild.com',
                'contact_phone'      => '+63-2-8345-6789',
                'is_active'          => 1,
            ],
            [
                'name'               => 'Finance',
                'description'        => 'Manages financial operations, accounts payable/receivable, and financial reporting.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Catherine Mae Gonzales',
                'contact_email'      => 'finance@webuild.com',
                'contact_phone'      => '+63-2-8456-7890',
                'is_active'          => 1,
            ],
            [
                'name'               => 'Information Technology',
                'description'        => 'Manages IT infrastructure, system administration, and technical support for WITMS.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Marjovic Prato Alejado',
                'contact_email'      => 'it@webuild.com',
                'contact_phone'      => '+63-2-8567-8901',
                'is_active'          => 1,
            ],
            [
                'name'               => 'Executive',
                'description'        => 'Top management and executive leadership for strategic decision making.',
                'warehouse_location' => 'Central Office',
                'department_head'    => 'Eduardo Fernandez Ramirez',
                'contact_email'      => 'executive@webuild.com',
                'contact_phone'      => '+63-2-8678-9012',
                'is_active'          => 1,
            ],
        ];

        // Insert departments using the model (with validation and timestamps)
        $successCount = 0;
        $errorCount = 0;

        foreach ($departments as $department) {
            try {
                // Use the createDepartment method from DepartmentModel
                $result = $departmentModel->createDepartment($department);
                
                if ($result) {
                    $successCount++;
                    echo "✓ Seeded: {$department['name']} ({$department['warehouse_location']})\n";
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

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Department Seeding Summary:\n";
        echo "- Successfully seeded: {$successCount} departments\n";
        echo "- Failed: {$errorCount} departments\n";
        echo "- Total departments in database: " . $departmentModel->countAll() . "\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "✓ WeBuild department structure seeded successfully!\n";
    }
}
