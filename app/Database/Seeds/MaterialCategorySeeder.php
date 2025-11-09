<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['name' => 'Cement & Aggregates', 'code' => 'CAT-001', 'description' => 'Cement, sand, gravel, etc.', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Steel & Metal', 'code' => 'CAT-002', 'description' => 'Rebars, steel bars, metal sheets', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Wood & Lumber', 'code' => 'CAT-003', 'description' => 'Plywood, lumber, wooden boards', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Electrical', 'code' => 'CAT-004', 'description' => 'Wires, cables, switches', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Plumbing', 'code' => 'CAT-005', 'description' => 'Pipes, fittings, valves', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Paint & Finishing', 'code' => 'CAT-006', 'description' => 'Paints, varnish, sealants', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Hardware', 'code' => 'CAT-007', 'description' => 'Nails, screws, bolts, hinges', 'parent_id' => null, 'is_active' => true],
            ['name' => 'Safety Equipment', 'code' => 'CAT-008', 'description' => 'Helmets, gloves, safety gear', 'parent_id' => null, 'is_active' => true],
        ];

        $this->db->table('material_categories')->insertBatch($data);
    }
}
