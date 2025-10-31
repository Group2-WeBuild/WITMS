<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\DepartmentModel;

/**
 * User Seeder
 * 
 * Seeds the users table with initial WeBuild company user accounts.
 * This seeder uses the UserModel to ensure proper validation and data handling.
 * 
 * References: 
 * - Migration: 2025-09-29-150001_CreateUsersTable.php
 * - Model: App\Models\UserModel
 * - Dependencies: RoleSeeder (must run first), DepartmentSeeder (must run first)
 * 
 * Usage:
 * php spark db:seed UserSeeder
 */
class UserSeeder extends Seeder
{
    public function run()
    {
        // Initialize models
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $departmentModel = new DepartmentModel();

        echo "Starting WeBuild user account seeding process...\n\n";

        // First, get department IDs dynamically
        $departments = $departmentModel->findAll();
        
        // Create a mapping of department names to IDs
        $deptMap = [];
        foreach ($departments as $dept) {
            $deptMap[$dept['name']] = $dept['id'];
        }

        // Get role IDs dynamically
        $roles = $roleModel->findAll();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role['name']] = $role['id'];
        }

        // User data matching the migration structure
        // Note: Passwords will be hashed by UserModel's beforeInsert callback
        $users = [            // Warehouse Manager
            [
                'email'              => 'warehouse.manager@webuild.com',
                'password'           => 'WareManager2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Warehouse Manager'] ?? 1,
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,
                'first_name'         => 'Juan Carlos',
                'middle_name'        => 'Rivera',
                'last_name'          => 'Martinez',
                'phone_number'       => '+63-917-123-4567',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],
            
            // Warehouse Staff
            [
                'email'              => 'warehouse.staff@webuild.com',
                'password'           => 'WareStaff2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Warehouse Staff'] ?? 2,
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,                'first_name'         => 'Maria Elena',
                'middle_name'        => 'Gomez',
                'last_name'          => 'Santos',
                'phone_number'       => '+63-917-234-5678',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Inventory Auditor
            [
                'email'              => 'inventory.auditor@webuild.com',
                'password'           => 'InvAuditor2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Inventory Auditor'] ?? 3,
                'department_id'      => $deptMap['Quality Control'] ?? 2,                'first_name'         => 'Roberto Antonio',
                'middle_name'        => 'Dela',
                'last_name'          => 'Cruz',
                'phone_number'       => '+63-917-345-6789',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Procurement Officer
            [
                'email'              => 'procurement.officer@webuild.com',
                'password'           => 'ProcOfficer2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Procurement Officer'] ?? 4,
                'department_id'      => $deptMap['Procurement'] ?? 3,                'first_name'         => 'Catherine Mae',
                'middle_name'        => 'Villanueva',
                'last_name'          => 'Gonzales',
                'phone_number'       => '+63-917-456-7890',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Accounts Payable Clerk
            [
                'email'              => 'accounts.payable@webuild.com',
                'password'           => 'AcctPayable2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Accounts Payable Clerk'] ?? 5,
                'department_id'      => $deptMap['Finance'] ?? 4,                'first_name'         => 'Jessica Anne',
                'middle_name'        => 'Torres',
                'last_name'          => 'Reyes',
                'phone_number'       => '+63-917-567-8901',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Accounts Receivable Clerk
            [
                'email'              => 'accounts.receivable@webuild.com',
                'password'           => 'AcctReceivable2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Accounts Receivable Clerk'] ?? 6,
                'department_id'      => $deptMap['Finance'] ?? 4,                'first_name'         => 'Miguel Angelo',
                'middle_name'        => 'Mendoza',
                'last_name'          => 'Fernandez',
                'phone_number'       => '+63-917-678-9012',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // IT Administrator
            [
                'email'              => 'marjovicalejado123@gmail.com',
                'password'           => 'ITAdmin2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['IT Administrator'] ?? 7,
                'department_id'      => $deptMap['Information Technology'] ?? 5,                'first_name'         => 'Marjovic Prato',
                'middle_name'        => 'Santos',
                'last_name'          => 'Alejado',
                'phone_number'       => '+63-917-789-0123',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Top Management
            [
                'email'              => 'ceo@webuild.com',
                'password'           => 'TopMgmt2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Top Management'] ?? 8,
                'department_id'      => $deptMap['Executive'] ?? 6,                'first_name'         => 'Eduardo',
                'middle_name'        => 'Fernandez',
                'last_name'          => 'Ramirez',
                'phone_number'       => '+63-917-890-1234',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Additional Warehouse Staff for testing
            [
                'email'              => 'warehouse.staff2@webuild.com',
                'password'           => 'WareStaff2_2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['Warehouse Staff'] ?? 2,
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,                'first_name'         => 'Ana Patricia',
                'middle_name'        => 'Lopez',
                'last_name'          => 'Morales',
                'phone_number'       => '+63-917-901-2345',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Additional IT Administrator for backup
            [
                'email'              => 'it.admin2@webuild.com',
                'password'           => 'ITAdmin2_2024!',  // Will be hashed by UserModel
                'role_id'            => $roleMap['IT Administrator'] ?? 7,
                'department_id'      => $deptMap['Information Technology'] ?? 5,                'first_name'         => 'Carlos Miguel',
                'middle_name'        => 'Dela Cruz',
                'last_name'          => 'Villanueva',
                'phone_number'       => '+63-917-012-3456',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert users using UserModel (with validation and password hashing)
        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                // Use the createUser method from UserModel
                $result = $userModel->createUser($user);
                
                if ($result) {
                    $successCount++;
                    $fullName = "{$user['first_name']} {$user['last_name']}";
                    $roleId = $user['role_id'];
                    $roleName = array_search($roleId, $roleMap) ?: 'Unknown';
                    echo "✓ Created: {$fullName} ({$user['email']}) - Role: {$roleName}\n";
                } else {
                    $errorCount++;
                    $errors = $userModel->errors();
                    echo "✗ Failed: {$user['email']} - " . json_encode($errors) . "\n";
                }
            } catch (\Exception $e) {
                $errorCount++;
                echo "✗ Exception for {$user['email']}: {$e->getMessage()}\n";
            }
        }

        echo "\n" . str_repeat("=", 70) . "\n";
        echo "User Seeding Summary:\n";
        echo "- Successfully created: {$successCount} users\n";
        echo "- Failed: {$errorCount} users\n";
        echo "- Total users in database: " . $userModel->countAll() . "\n";
        echo str_repeat("=", 70) . "\n\n";
        
        echo "\n=== WeBuild WITMS User Accounts Created ===\n";
        echo "Test credentials for WeBuild roles:\n\n";
        
        echo "🏢 WAREHOUSE OPERATIONS:\n";
        echo "   Manager: warehouse.manager@webuild.com / WareManager2024!\n";
        echo "   Staff:   warehouse.staff@webuild.com / WareStaff2024!\n";
        echo "   Staff 2: warehouse.staff2@webuild.com / WareStaff2_2024!\n\n";
        
        echo "🔍 QUALITY CONTROL:\n";
        echo "   Auditor: inventory.auditor@webuild.com / InvAuditor2024!\n\n";
        
        echo "🛒 PROCUREMENT:\n";
        echo "   Officer: procurement.officer@webuild.com / ProcOfficer2024!\n\n";
        
        echo "💰 FINANCE:\n";
        echo "   Payable:    accounts.payable@webuild.com / AcctPayable2024!\n";
        echo "   Receivable: accounts.receivable@webuild.com / AcctReceivable2024!\n\n";
        
        echo "💻 INFORMATION TECHNOLOGY:\n";
        echo "   Admin:   it.admin@webuild.com / ITAdmin2024!\n";
        echo "   Admin 2: it.admin2@webuild.com / ITAdmin2_2024!\n\n";
        
        echo "👔 EXECUTIVE:\n";
        echo "   CEO: ceo@webuild.com / TopMgmt2024!\n\n";
          echo "All passwords follow secure format with:\n";
        echo "- Minimum 8 characters\n";
        echo "- Uppercase and lowercase letters\n";
        echo "- Numbers and special characters\n";
        echo "- Role-specific prefix for easy identification\n\n";
        
        echo "NOTE: Passwords are automatically hashed by UserModel for security.\n";
        echo "✓ WeBuild WITMS users seeded successfully! 🎉\n";
    }
}
