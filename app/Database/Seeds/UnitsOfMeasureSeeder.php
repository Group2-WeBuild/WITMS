<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitsOfMeasureSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Pieces', 'abbreviation' => 'pcs', 'is_active' => true],
            ['name' => 'Meters', 'abbreviation' => 'm', 'is_active' => true],
            ['name' => 'Kilograms', 'abbreviation' => 'kg', 'is_active' => true],
            ['name' => 'Liters', 'abbreviation' => 'L', 'is_active' => true],
            ['name' => 'Boxes', 'abbreviation' => 'box', 'is_active' => true],
            ['name' => 'Bags', 'abbreviation' => 'bag', 'is_active' => true],
            ['name' => 'Rolls', 'abbreviation' => 'roll', 'is_active' => true],
            ['name' => 'Sheets', 'abbreviation' => 'sheet', 'is_active' => true],
            ['name' => 'Cubic Meters', 'abbreviation' => 'm³', 'is_active' => true],
            ['name' => 'Square Meters', 'abbreviation' => 'm²', 'is_active' => true],
        ];

        $this->db->table('units_of_measure')->insertBatch($data);
    }
}
