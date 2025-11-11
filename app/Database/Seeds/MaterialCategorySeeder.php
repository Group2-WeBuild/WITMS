<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{    public function run()
    {
        $timestamp = date('Y-m-d H:i:s');
        
        $data = [
            ['name' => 'Steel', 'code' => 'STEEL', 'description' => 'Steel products and rebars', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Cement', 'code' => 'CEM', 'description' => 'Cement and concrete products', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Aggregates', 'code' => 'AGG', 'description' => 'Sand, gravel, and aggregates', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Wood Products', 'code' => 'WOOD', 'description' => 'Plywood and lumber', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Paints', 'code' => 'PAINT', 'description' => 'Paints and coatings', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Electrical', 'code' => 'ELEC', 'description' => 'Electrical supplies', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Plumbing', 'code' => 'PLUMB', 'description' => 'Plumbing supplies', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Hardware', 'code' => 'HARD', 'description' => 'Hardware and tools', 'parent_id' => null, 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];

        $this->db->table('material_categories')->insertBatch($data);
        
        echo "✓ Created " . count($data) . " material categories\n";
    }
}
