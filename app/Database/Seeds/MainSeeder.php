<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{    public function run()
    {
        echo "\n Starting WeBuild WITMS Database Seeding...\n\n";
        
        $this->call('DepartmentSeeder');
        $this->call('UserSeeder');
        $this->call('WarehouseSeeder');
        $this->call('UnitsOfMeasureSeeder');
        $this->call('MaterialCategorySeeder');
        $this->call('MaterialSeeder');
        $this->call('InventorySeeder');
        $this->call('WarehouseLocationSeeder');
        
        echo "\n WeBuild WITMS database seeding completed successfully!\n";
        echo "You can now test the login system with the provided credentials.\n\n";
    }
}
