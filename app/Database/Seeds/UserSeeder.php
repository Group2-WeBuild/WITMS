<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\DepartmentModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Initialize models
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $departmentModel = new DepartmentModel();

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
        
        $users = [            
            // IT Administrator
            [
                'email'              => 'marjovicalejado123@gmail.com',
                'password'           => 'Alejado@123',  
                'role_id'            => $roleMap['IT Administrator'] ?? 7,
                'department_id'      => $deptMap['Information Technology'],                
                'first_name'         => 'Marjovic',
                'middle_name'        => 'Prato',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391520886',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],            // Warehouse Staff
            [
                'email'              => 'aslainiemaruhom@gmail.com',
                'password'           => 'Maruhom@123',  
                'role_id'            => $roleMap['Warehouse Staff'] ?? 2,
                'department_id'      => $deptMap['Warehouse Operations'],                
                'first_name'         => 'Aslainie',
                'middle_name'        => 'Lampac',
                'last_name'          => 'Maruhom',
                'phone_number'       => '09391234567',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Warehouse Manager
            [
                'email'              => 'cyrylljoyalejado@gmail.com',
                'password'           => 'Alejado@123',  
                'role_id'            => $roleMap['Warehouse Manager'] ?? 1,
                'department_id'      => $deptMap['Warehouse Operations'],
                'first_name'         => 'Cyryll Joy',
                'middle_name'        => '',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391123456',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Procurement Officer
            [
                'email'              => 'maryjoyalejado@gmail.com',
                'password'           => 'Alejado@123',    
                'role_id'            => $roleMap['Procurement Officer'] ?? 4,
                'department_id'      => $deptMap['Procurement'],                
                'first_name'         => 'Mary Joy',
                'middle_name'        => '',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391456789',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Inventory Auditor
            [
                'email'              => 'viverleialejado@gmail.com',
                'password'           => 'Alejado@123',  
                'role_id'            => $roleMap['Inventory Auditor'] ?? 3,
                'department_id'      => $deptMap['Quality Control'],                
                'first_name'         => 'Viver Lei',
                'middle_name'        => '',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391345678',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Top Management
            [
                'email'              => 'victoralejado@gmail.com',
                'password'           => 'Alejado@123',  
                'role_id'            => $roleMap['Top Management'] ?? 8,
                'department_id'      => $deptMap['Executive'],                
                'first_name'         => 'Victor',
                'middle_name'        => 'Tabaniera',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391890123',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Accounts Receivable Clerk
            [
                'email'              => 'virginialejado@gmail.com',
                'password'           => 'Alejado@123',  
                'role_id'            => $roleMap['Accounts Receivable Clerk'] ?? 6,
                'department_id'      => $deptMap['Finance'],                
                'first_name'         => 'Virginia',
                'middle_name'        => 'Prato',
                'last_name'          => 'Alejado',
                'phone_number'       => '09391678901',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],

            // Accounts Payable Clerk
            [
                'email'              => 'ammarthefilipino@gmail.com',
                'password'           => 'Filipino@123',  
                'role_id'            => $roleMap['Accounts Payable Clerk'] ?? 5,
                'department_id'      => $deptMap['Finance'],                
                'first_name'         => 'Ammar',
                'middle_name'        => 'The',
                'last_name'          => 'Filipino',
                'phone_number'       => '09391567890',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
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
    }
}
