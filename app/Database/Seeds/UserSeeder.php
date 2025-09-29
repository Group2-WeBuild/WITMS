<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // First, get department IDs
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->findAll();
        
        // Create a mapping of department names to IDs
        $deptMap = [];
        foreach ($departments as $dept) {
            $deptMap[$dept['name']] = $dept['id'];
        }

        $data = [
            // Warehouse Manager
            [
                'email'              => 'warehouse.manager@webuild.com',
                'password'           => password_hash('WareManager2024!', PASSWORD_DEFAULT),
                'role'               => 'Warehouse Manager',
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,
                'first_name'         => 'Juan Carlos',
                'middle_name'        => 'Rivera',
                'last_name'          => 'Martinez',
                'phone_number'       => '+63-917-123-4567',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            
            // Warehouse Staff
            [
                'email'              => 'warehouse.staff@webuild.com',
                'password'           => password_hash('WareStaff2024!', PASSWORD_DEFAULT),
                'role'               => 'Warehouse Staff',
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,
                'first_name'         => 'Maria Elena',
                'middle_name'        => 'Gomez',
                'last_name'          => 'Santos',
                'phone_number'       => '+63-917-234-5678',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Inventory Auditor
            [
                'email'              => 'inventory.auditor@webuild.com',
                'password'           => password_hash('InvAuditor2024!', PASSWORD_DEFAULT),
                'role'               => 'Inventory Auditor',
                'department_id'      => $deptMap['Quality Control'] ?? 2,
                'first_name'         => 'Roberto Antonio',
                'middle_name'        => 'Dela',
                'last_name'          => 'Cruz',
                'phone_number'       => '+63-917-345-6789',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Procurement Officer
            [
                'email'              => 'procurement.officer@webuild.com',
                'password'           => password_hash('ProcOfficer2024!', PASSWORD_DEFAULT),
                'role'               => 'Procurement Officer',
                'department_id'      => $deptMap['Procurement'] ?? 3,
                'first_name'         => 'Catherine Mae',
                'middle_name'        => 'Villanueva',
                'last_name'          => 'Gonzales',
                'phone_number'       => '+63-917-456-7890',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Accounts Payable Clerk
            [
                'email'              => 'accounts.payable@webuild.com',
                'password'           => password_hash('AcctPayable2024!', PASSWORD_DEFAULT),
                'role'               => 'Accounts Payable Clerk',
                'department_id'      => $deptMap['Finance - Central Office'] ?? 4,
                'first_name'         => 'Jessica Anne',
                'middle_name'        => 'Torres',
                'last_name'          => 'Reyes',
                'phone_number'       => '+63-917-567-8901',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Accounts Receivable Clerk
            [
                'email'              => 'accounts.receivable@webuild.com',
                'password'           => password_hash('AcctReceivable2024!', PASSWORD_DEFAULT),
                'role'               => 'Accounts Receivable Clerk',
                'department_id'      => $deptMap['Finance - Central Office'] ?? 4,
                'first_name'         => 'Miguel Angelo',
                'middle_name'        => 'Mendoza',
                'last_name'          => 'Fernandez',
                'phone_number'       => '+63-917-678-9012',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // IT Administrator
            [
                'email'              => 'it.admin@webuild.com',
                'password'           => password_hash('ITAdmin2024!', PASSWORD_DEFAULT),
                'role'               => 'IT Administrator',
                'department_id'      => $deptMap['Information Technology'] ?? 5,
                'first_name'         => 'Marjovic Prato',
                'middle_name'        => 'Santos',
                'last_name'          => 'Alejado',
                'phone_number'       => '+63-917-789-0123',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Top Management
            [
                'email'              => 'ceo@webuild.com',
                'password'           => password_hash('TopMgmt2024!', PASSWORD_DEFAULT),
                'role'               => 'Top Management',
                'department_id'      => $deptMap['Executive'] ?? 6,
                'first_name'         => 'Eduardo',
                'middle_name'        => 'Fernandez',
                'last_name'          => 'Ramirez',
                'phone_number'       => '+63-917-890-1234',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Additional Warehouse Staff for testing
            [
                'email'              => 'warehouse.staff2@webuild.com',
                'password'           => password_hash('WareStaff2_2024!', PASSWORD_DEFAULT),
                'role'               => 'Warehouse Staff',
                'department_id'      => $deptMap['Warehouse Operations'] ?? 1,
                'first_name'         => 'Ana Patricia',
                'middle_name'        => 'Lopez',
                'last_name'          => 'Morales',
                'phone_number'       => '+63-917-901-2345',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],

            // Additional IT Administrator for backup
            [
                'email'              => 'it.admin2@webuild.com',
                'password'           => password_hash('ITAdmin2_2024!', PASSWORD_DEFAULT),
                'role'               => 'IT Administrator',
                'department_id'      => $deptMap['Information Technology'] ?? 5,
                'first_name'         => 'Carlos Miguel',
                'middle_name'        => 'Dela Cruz',
                'last_name'          => 'Villanueva',
                'phone_number'       => '+63-917-012-3456',
                'is_active'          => 1,
                'email_verified_at'  => date('Y-m-d H:i:s'),
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert users
        $this->db->table('users')->insertBatch($data);
        
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
        
        echo "Total users created: " . count($data) . "\n";
        echo "WeBuild WITMS users seeded successfully! 🎉\n";
    }
}
