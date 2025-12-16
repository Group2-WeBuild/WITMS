<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\WarehouseLocationModel;

/**
 * Warehouse Location Seeder
 * 
 * Seeds the warehouse_locations table with Philippine location data.
 * This seeder provides comprehensive location data for dropdowns when creating warehouses.
 * 
 * Includes:
 * - Major cities and municipalities
 * - Provinces
 * - Regions (17 regions of the Philippines)
 * - Postal codes
 * - Coordinates (latitude/longitude) for major cities
 * 
 * References:
 * - Migration: 2025-11-09-140050_CreateWarehouseLocationsTable.php
 * - Model: App\Models\WarehouseLocationModel
 * 
 * Usage:
 * php spark db:seed WarehouseLocationSeeder
 */
class WarehouseLocationSeeder extends Seeder
{
    public function run()
    {
        $locationModel = new WarehouseLocationModel();
        
        // Philippine location data - Major cities and municipalities
        // Format: [city, province, region, postal_code, latitude, longitude, street_address, barangay]
        $locations = [
            // National Capital Region (NCR)
            ['Manila', 'Metro Manila', 'National Capital Region (NCR)', '1000', 14.5995, 120.9842, 'Intramuros', 'Intramuros'],
            ['Quezon City', 'Metro Manila', 'National Capital Region (NCR)', '1100', 14.6760, 121.0437, 'Diliman', 'Diliman'],
            ['Makati', 'Metro Manila', 'National Capital Region (NCR)', '1200', 14.5547, 121.0244, 'Poblacion', 'Poblacion'],
            ['Pasay', 'Metro Manila', 'National Capital Region (NCR)', '1300', 14.5378, 121.0014, 'West Service Road', 'Barangay 178'],
            ['Taguig', 'Metro Manila', 'National Capital Region (NCR)', '1630', 14.5176, 121.0509, 'BGC', 'Fort Bonifacio'],
            ['Mandaluyong', 'Metro Manila', 'National Capital Region (NCR)', '1550', 14.5794, 121.0359, 'Ortigas Avenue', 'Ortigas Center'],
            ['Pasig', 'Metro Manila', 'National Capital Region (NCR)', '1600', 14.5764, 121.0851, 'Ortigas Avenue', 'Ortigas Center'],
            ['Caloocan', 'Metro Manila', 'National Capital Region (NCR)', '1400', 14.6548, 120.9843, 'Rizal Avenue Extension', 'Caloocan City'],
            ['Las Piñas', 'Metro Manila', 'National Capital Region (NCR)', '1740', 14.4496, 120.9826, 'Alabang-Zapote Road', 'Alabang'],
            ['Muntinlupa', 'Metro Manila', 'National Capital Region (NCR)', '1770', 14.4081, 121.0455, 'Alabang-Zapote Road', 'Alabang'],
            ['Marikina', 'Metro Manila', 'National Capital Region (NCR)', '1800', 14.6507, 121.1029, 'Sumulong Highway', 'Marikina Heights'],
            ['Parañaque', 'Metro Manila', 'National Capital Region (NCR)', '1700', 14.4793, 121.0198, 'Ninoy Aquino Avenue', 'Baclaran'],
            ['Valenzuela', 'Metro Manila', 'National Capital Region (NCR)', '1440', 14.7004, 120.9839, 'MacArthur Highway', 'Valenzuela City'],
            ['Malabon', 'Metro Manila', 'National Capital Region (NCR)', '1470', 14.6655, 120.9569, 'Letre Road', 'Malabon City'],
            ['Navotas', 'Metro Manila', 'National Capital Region (NCR)', '1485', 14.6667, 120.9417, 'Navotas Boulevard', 'Navotas City'],
            ['San Juan', 'Metro Manila', 'National Capital Region (NCR)', '1500', 14.6019, 121.0355, 'N. Domingo Street', 'Greenhills'],
            
            // Region I - Ilocos Region
            ['San Fernando', 'La Union', 'Region I (Ilocos Region)', '2500', 16.6164, 120.3158, 'Carlatan', 'Carlatan'],
            ['Vigan', 'Ilocos Sur', 'Region I (Ilocos Region)', '2700', 17.5748, 120.3869, 'Crisologo Street', 'Vigan City'],
            ['Laoag', 'Ilocos Norte', 'Region I (Ilocos Region)', '2900', 18.1978, 120.5957, 'Rizal Avenue', 'Laoag City'],
            ['Dagupan', 'Pangasinan', 'Region I (Ilocos Region)', '2400', 16.0439, 120.3327, 'Perez Boulevard', 'Dagupan City'],
            
            // Region II - Cagayan Valley
            ['Tuguegarao', 'Cagayan', 'Region II (Cagayan Valley)', '3500', 17.6133, 121.7269, 'Rizal Street', 'Tuguegarao City'],
            ['Santiago', 'Isabela', 'Region II (Cagayan Valley)', '3311', 16.6881, 121.5487, 'Maharlika Highway', 'Santiago City'],
            ['Ilagan', 'Isabela', 'Region II (Cagayan Valley)', '3300', 17.1485, 121.8892, 'National Highway', 'Ilagan City'],
            
            // Region III - Central Luzon
            ['Angeles', 'Pampanga', 'Region III (Central Luzon)', '2009', 15.1450, 120.5847, 'MacArthur Highway', 'Angeles City'],
            ['San Fernando', 'Pampanga', 'Region III (Central Luzon)', '2000', 15.0319, 120.6895, 'Jose Abad Santos Avenue', 'San Fernando City'],
            ['Olongapo', 'Zambales', 'Region III (Central Luzon)', '2200', 14.8292, 120.2828, 'Rizal Avenue', 'Olongapo City'],
            ['Malolos', 'Bulacan', 'Region III (Central Luzon)', '3000', 14.8444, 120.8104, 'Plaza Rizal', 'Malolos City'],
            ['Cabanatuan', 'Nueva Ecija', 'Region III (Central Luzon)', '3100', 15.4869, 120.9675, 'Maharlika Highway', 'Cabanatuan City'],
            ['Tarlac', 'Tarlac', 'Region III (Central Luzon)', '2300', 15.4869, 120.5901, 'Romulo Boulevard', 'Tarlac City'],
            
            // Region IV-A - CALABARZON
            ['Calamba', 'Laguna', 'Region IV-A (CALABARZON)', '4027', 14.2117, 121.1656, 'National Highway', 'Calamba City'],
            ['San Pablo', 'Laguna', 'Region IV-A (CALABARZON)', '4000', 14.0703, 121.3256, 'Rizal Avenue', 'San Pablo City'],
            ['Batangas', 'Batangas', 'Region IV-A (CALABARZON)', '4200', 13.7565, 121.0583, 'P. Burgos Street', 'Batangas City'],
            ['Lipa', 'Batangas', 'Region IV-A (CALABARZON)', '4217', 13.9411, 121.1632, 'J.P. Laurel Highway', 'Lipa City'],
            ['Cavite', 'Cavite', 'Region IV-A (CALABARZON)', '4100', 14.4791, 120.8970, 'P. Burgos Street', 'Cavite City'],
            ['Tagaytay', 'Cavite', 'Region IV-A (CALABARZON)', '4120', 14.1000, 120.9333, 'Aguinaldo Highway', 'Tagaytay City'],
            ['Antipolo', 'Rizal', 'Region IV-A (CALABARZON)', '1870', 14.6255, 121.1245, 'Sumulong Highway', 'Antipolo City'],
            ['Lucena', 'Quezon', 'Region IV-A (CALABARZON)', '4301', 13.9314, 121.6174, 'Maharlika Highway', 'Lucena City'],
            
            // Region IV-B - MIMAROPA
            ['Puerto Princesa', 'Palawan', 'Region IV-B (MIMAROPA)', '5300', 9.7392, 118.7353, 'Rizal Avenue', 'Puerto Princesa City'],
            ['Calapan', 'Oriental Mindoro', 'Region IV-B (MIMAROPA)', '5200', 13.4117, 121.1803, 'J.P. Rizal Street', 'Calapan City'],
            ['Romblon', 'Romblon', 'Region IV-B (MIMAROPA)', '5500', 12.5753, 122.2708, 'Rizal Street', 'Romblon'],
            
            // Region V - Bicol Region
            ['Naga', 'Camarines Sur', 'Region V (Bicol Region)', '4400', 13.6192, 123.1814, 'Magsaysay Avenue', 'Naga City'],
            ['Legazpi', 'Albay', 'Region V (Bicol Region)', '4500', 13.1390, 123.7338, 'Rizal Street', 'Legazpi City'],
            ['Sorsogon', 'Sorsogon', 'Region V (Bicol Region)', '4700', 12.9700, 124.0061, 'Rizal Street', 'Sorsogon City'],
            ['Iriga', 'Camarines Sur', 'Region V (Bicol Region)', '4431', 13.4200, 123.4100, 'San Francisco Street', 'Iriga City'],
            
            // Region VI - Western Visayas
            ['Iloilo', 'Iloilo', 'Region VI (Western Visayas)', '5000', 10.7202, 122.5621, 'J.M. Basa Street', 'Iloilo City'],
            ['Bacolod', 'Negros Occidental', 'Region VI (Western Visayas)', '6100', 10.6769, 122.9503, 'Lacson Street', 'Bacolod City'],
            ['Roxas', 'Capiz', 'Region VI (Western Visayas)', '5800', 11.5853, 122.7511, 'Rizal Street', 'Roxas City'],
            ['Kalibo', 'Aklan', 'Region VI (Western Visayas)', '5600', 11.7064, 122.3644, 'Rizal Street', 'Kalibo'],
            
            // Region VII - Central Visayas
            ['Cebu', 'Cebu', 'Region VII (Central Visayas)', '6000', 10.3157, 123.8854, 'Colon Street', 'Cebu City'],
            ['Lapu-Lapu', 'Cebu', 'Region VII (Central Visayas)', '6015', 10.3103, 123.9494, 'Mactan Airport Road', 'Lapu-Lapu City'],
            ['Mandaue', 'Cebu', 'Region VII (Central Visayas)', '6014', 10.3333, 123.9333, 'A.C. Cortes Avenue', 'Mandaue City'],
            ['Tagbilaran', 'Bohol', 'Region VII (Central Visayas)', '6300', 9.6644, 123.8522, 'Carlos P. Garcia Avenue', 'Tagbilaran City'],
            ['Dumaguete', 'Negros Oriental', 'Region VII (Central Visayas)', '6200', 9.3077, 123.3054, 'Rizal Boulevard', 'Dumaguete City'],
            
            // Region VIII - Eastern Visayas
            ['Tacloban', 'Leyte', 'Region VIII (Eastern Visayas)', '6500', 11.2444, 125.0039, 'Real Street', 'Tacloban City'],
            ['Ormoc', 'Leyte', 'Region VIII (Eastern Visayas)', '6541', 11.0064, 124.6075, 'Luna Street', 'Ormoc City'],
            ['Calbayog', 'Samar', 'Region VIII (Eastern Visayas)', '6710', 12.0667, 124.6000, 'Rizal Street', 'Calbayog City'],
            
            // Region IX - Zamboanga Peninsula
            ['Zamboanga', 'Zamboanga del Sur', 'Region IX (Zamboanga Peninsula)', '7000', 6.9214, 122.0790, 'Pasonanca Road', 'Zamboanga City'],
            ['Dipolog', 'Zamboanga del Norte', 'Region IX (Zamboanga Peninsula)', '7100', 8.5881, 123.3414, 'Rizal Avenue', 'Dipolog City'],
            ['Pagadian', 'Zamboanga del Sur', 'Region IX (Zamboanga Peninsula)', '7016', 7.8258, 123.4370, 'Rizal Avenue', 'Pagadian City'],
            
            // Region X - Northern Mindanao
            ['Cagayan de Oro', 'Misamis Oriental', 'Region X (Northern Mindanao)', '9000', 8.4542, 124.6319, 'Velez Street', 'Cagayan de Oro City'],
            ['Iligan', 'Lanao del Norte', 'Region X (Northern Mindanao)', '9200', 8.2280, 124.2452, 'Roxas Avenue', 'Iligan City'],
            ['Ozamiz', 'Misamis Occidental', 'Region X (Northern Mindanao)', '7200', 8.1481, 123.8405, 'Rizal Avenue', 'Ozamiz City'],
            ['Gingoog', 'Misamis Oriental', 'Region X (Northern Mindanao)', '9014', 8.8167, 125.1000, 'Rizal Street', 'Gingoog City'],
            
            // Region XI - Davao Region
            ['Davao', 'Davao del Sur', 'Region XI (Davao Region)', '8000', 7.0731, 125.6128, 'McArthur Highway', 'Davao City'],
            ['Tagum', 'Davao del Norte', 'Region XI (Davao Region)', '8100', 7.4478, 125.8078, 'Apokon Road', 'Tagum City'],
            ['Digos', 'Davao del Sur', 'Region XI (Davao Region)', '8002', 6.7497, 125.3570, 'Rizal Avenue', 'Digos City'],
            ['Mati', 'Davao Oriental', 'Region XI (Davao Region)', '8200', 6.9528, 126.2167, 'Rizal Street', 'Mati City'],
            
            // Region XII - SOCCSKSARGEN
            ['General Santos', 'South Cotabato', 'Region XII (SOCCSKSARGEN)', '9500', 6.1128, 125.1717, 'National Highway', 'General Santos City'],
            ['Koronadal', 'South Cotabato', 'Region XII (SOCCSKSARGEN)', '9506', 6.5031, 124.8469, 'National Highway', 'Koronadal City'],
            ['Cotabato', 'Maguindanao', 'Region XII (SOCCSKSARGEN)', '9600', 7.2044, 124.2464, 'Sinsuat Avenue', 'Cotabato City'],
            ['Tacurong', 'Sultan Kudarat', 'Region XII (SOCCSKSARGEN)', '9800', 6.6881, 124.6739, 'National Highway', 'Tacurong City'],
            
            // Region XIII - Caraga
            ['Butuan', 'Agusan del Norte', 'Region XIII (Caraga)', '8600', 8.9492, 125.5436, 'J.C. Aquino Avenue', 'Butuan City'],
            ['Surigao', 'Surigao del Norte', 'Region XIII (Caraga)', '8400', 9.7853, 125.4950, 'Rizal Street', 'Surigao City'],
            ['Tandag', 'Surigao del Sur', 'Region XIII (Caraga)', '8300', 9.0783, 126.1986, 'Rizal Street', 'Tandag City'],
            
            // Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)
            ['Marawi', 'Lanao del Sur', 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)', '9700', 7.9986, 124.2928, 'Rizal Street', 'Marawi City'],
            ['Lamitan', 'Basilan', 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)', '7302', 6.6500, 122.1333, 'Rizal Street', 'Lamitan City'],
            
            // Cordillera Administrative Region (CAR)
            ['Baguio', 'Benguet', 'Cordillera Administrative Region (CAR)', '2600', 16.4023, 120.5960, 'Session Road', 'Baguio City'],
            ['La Trinidad', 'Benguet', 'Cordillera Administrative Region (CAR)', '2601', 16.4644, 120.5875, 'Km. 5', 'La Trinidad'],
            
            // Additional major municipalities
            ['Cainta', 'Rizal', 'Region IV-A (CALABARZON)', '1900', 14.5786, 121.1222, 'Ortigas Avenue Extension', 'Cainta'],
            ['San Pedro', 'Laguna', 'Region IV-A (CALABARZON)', '4023', 14.3589, 121.0578, 'National Highway', 'San Pedro'],
            ['Biñan', 'Laguna', 'Region IV-A (CALABARZON)', '4024', 14.3333, 121.0833, 'National Highway', 'Biñan'],
            ['Santa Rosa', 'Laguna', 'Region IV-A (CALABARZON)', '4026', 14.3167, 121.1111, 'National Highway', 'Santa Rosa'],
            ['Bacoor', 'Cavite', 'Region IV-A (CALABARZON)', '4102', 14.4594, 120.9269, 'Aguinaldo Highway', 'Bacoor'],
            ['Imus', 'Cavite', 'Region IV-A (CALABARZON)', '4103', 14.4297, 120.9369, 'Aguinaldo Highway', 'Imus'],
            ['Dasmariñas', 'Cavite', 'Region IV-A (CALABARZON)', '4114', 14.3294, 120.9369, 'Aguinaldo Highway', 'Dasmariñas'],
        ];
        
        $successCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        
        echo "Seeding warehouse locations...\n\n";
        
        foreach ($locations as $location) {
            [$city, $province, $region, $postalCode, $latitude, $longitude, $streetAddress, $barangay] = $location;
            
            // Check if location already exists (by city and province)
            $existing = $locationModel
                ->where('city', $city)
                ->where('province', $province)
                ->first();
            
            if ($existing) {
                $skippedCount++;
                echo "⊘ Skipped: {$city}, {$province} (already exists)\n";
                continue;
            }
            
            $data = [
                'street_address' => $streetAddress,
                'barangay' => $barangay,
                'city' => $city,
                'province' => $province,
                'region' => $region,
                'postal_code' => $postalCode,
                'country' => 'Philippines',
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
            
            try {
                $result = $locationModel->insert($data);
                
                if ($result) {
                    $successCount++;
                    echo "✓ Created: {$city}, {$province} ({$region})\n";
                } else {
                    $errorCount++;
                    $errors = $locationModel->errors();
                    echo "✗ Failed: {$city}, {$province} - " . json_encode($errors) . "\n";
                }
            } catch (\Exception $e) {
                $errorCount++;
                echo "✗ Exception for {$city}, {$province}: {$e->getMessage()}\n";
            }
        }
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════\n";
        echo "Seeding Summary:\n";
        echo "  ✓ Successfully created: {$successCount}\n";
        echo "  ⊘ Skipped (already exists): {$skippedCount}\n";
        echo "  ✗ Errors: {$errorCount}\n";
        echo "═══════════════════════════════════════════════════════════\n";
    }
}

