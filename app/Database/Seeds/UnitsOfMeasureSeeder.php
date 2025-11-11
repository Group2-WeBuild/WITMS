<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UnitsOfMeasureSeeder extends Seeder
{    public function run()
    {
        $timestamp = date('Y-m-d H:i:s');
        
        $data = [
            ['name' => 'Pieces', 'abbreviation' => 'pcs', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Bags', 'abbreviation' => 'bag', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cubic Meters', 'abbreviation' => 'm3', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Sheets', 'abbreviation' => 'sheet', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Gallons', 'abbreviation' => 'gal', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Meters', 'abbreviation' => 'm', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Kilograms', 'abbreviation' => 'kg', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Liters', 'abbreviation' => 'L', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Boxes', 'abbreviation' => 'box', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Rolls', 'abbreviation' => 'roll', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        $this->db->table('units_of_measure')->insertBatch($data);
        
        echo "✓ Created " . count($data) . " units of measure\n";
    }
}
