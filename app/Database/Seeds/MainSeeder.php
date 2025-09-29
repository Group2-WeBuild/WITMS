<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        echo "\n🏗️  Starting WeBuild WITMS Database Seeding...\n\n";
        
        // Step 1: Seed departments first (required for foreign key relationships)
        echo "📁 Seeding departments...\n";
        $this->call('DepartmentSeeder');
        
        echo "\n👥 Seeding users...\n";
        // Step 2: Seed users (depends on departments)
        $this->call('UserSeeder');
        
        echo "\n✅ WeBuild WITMS database seeding completed successfully!\n";
        echo "🎯 You can now test the login system with the provided credentials.\n\n";
    }
}
