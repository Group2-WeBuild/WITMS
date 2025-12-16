<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseLocationModel extends Model
{    protected $table            = 'warehouse_locations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'street_address',
        'barangay',
        'city',
        'province',
        'region',
        'postal_code',
        'country',
        'latitude',
        'longitude'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'city'           => 'required|max_length[100]',
        'country'        => 'required|max_length[100]',
        'street_address' => 'permit_empty|max_length[255]',
        'barangay'       => 'permit_empty|max_length[100]',
        'province'       => 'permit_empty|max_length[100]',
        'region'         => 'permit_empty|max_length[100]',
        'postal_code'    => 'permit_empty|max_length[20]|regex_match[/^\d+$/]',
        'latitude'       => 'permit_empty|decimal',
        'longitude'      => 'permit_empty|decimal',
    ];
    
    protected $validationMessages = [
        'city' => [
            'required' => 'City is required'
        ],
        'country' => [
            'required' => 'Country is required'
        ],
        'postal_code' => [
            'regex_match' => 'Postal code must contain numbers only. Special characters and letters are not allowed.'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get location by warehouse ID
     */
    public function getByWarehouseId($warehouseId)
    {
        return $this->where('warehouse_id', $warehouseId)->first();
    }

    /**
     * Get formatted full address
     */
    public function getFormattedAddress($locationId)
    {
        $location = $this->find($locationId);
        
        if (!$location) {
            return null;
        }

        return $this->formatAddress($location);
    }

    /**
     * Format address array into string
     */
    public function formatAddress($location)
    {
        $parts = [];

        if (!empty($location['street_address'])) {
            $parts[] = $location['street_address'];
        }
        if (!empty($location['barangay'])) {
            $parts[] = 'Brgy. ' . $location['barangay'];
        }
        if (!empty($location['city'])) {
            $parts[] = $location['city'];
        }
        if (!empty($location['province'])) {
            $parts[] = $location['province'];
        }
        if (!empty($location['region'])) {
            $parts[] = $location['region'];
        }
        if (!empty($location['postal_code'])) {
            $parts[] = $location['postal_code'];
        }
        if (!empty($location['country'])) {
            $parts[] = $location['country'];
        }

        return implode(', ', $parts);
    }

    /**
     * Get short address (City, Region)
     */
    public function getShortAddress($location)
    {
        $parts = [];
        
        if (!empty($location['city'])) {
            $parts[] = $location['city'];
        }
        if (!empty($location['region'])) {
            $parts[] = $location['region'];
        }
        
        return implode(', ', $parts);
    }

    /**
     * Create or update warehouse location
     */
    public function setWarehouseLocation($warehouseId, $locationData)
    {
        $existing = $this->getByWarehouseId($warehouseId);

        $locationData['warehouse_id'] = $warehouseId;

        if ($existing) {
            return $this->update($existing['id'], $locationData);
        } else {
            return $this->insert($locationData);
        }
    }

    /**
     * Get locations by city
     */
    public function getByCity($city)
    {
        return $this->where('city', $city)->findAll();
    }

    /**
     * Get locations by region
     */
    public function getByRegion($region)
    {
        return $this->where('region', $region)->findAll();
    }

    /**
     * Get all unique cities
     */
    public function getAllCities()
    {
        return $this->select('city')
                    ->distinct()
                    ->orderBy('city', 'ASC')
                    ->findAll();
    }

    /**
     * Get all unique regions
     */
    public function getAllRegions()
    {
        return $this->select('region')
                    ->distinct()
                    ->where('region IS NOT NULL')
                    ->orderBy('region', 'ASC')
                    ->findAll();
    }

    /**
     * Get all unique provinces
     */
    public function getAllProvinces()
    {
        return $this->select('province')
                    ->distinct()
                    ->where('province IS NOT NULL')
                    ->orderBy('province', 'ASC')
                    ->findAll();
    }

    /**
     * Get all unique provinces by region
     */
    public function getProvincesByRegion($region)
    {
        return $this->select('province')
                    ->distinct()
                    ->where('region', $region)
                    ->where('province IS NOT NULL')
                    ->orderBy('province', 'ASC')
                    ->findAll();
    }

    /**
     * Get all cities by province
     */
    public function getCitiesByProvince($province)
    {
        return $this->select('city, id')
                    ->where('province', $province)
                    ->orderBy('city', 'ASC')
                    ->findAll();
    }

    /**
     * Get all cities by region
     */
    public function getCitiesByRegion($region)
    {
        return $this->select('city, id, province')
                    ->where('region', $region)
                    ->orderBy('city', 'ASC')
                    ->findAll();
    }

    /**
     * Get dropdown data for location selection
     * Returns structured data for cascading dropdowns (Region -> Province -> City)
     */
    public function getDropdownData()
    {
        $regions = $this->getAllRegions();
        $data = [];
        
        foreach ($regions as $regionRow) {
            $region = $regionRow['region'];
            $provinces = $this->getProvincesByRegion($region);
            
            $regionData = [
                'region' => $region,
                'provinces' => []
            ];
            
            foreach ($provinces as $provinceRow) {
                $province = $provinceRow['province'];
                $cities = $this->getCitiesByProvince($province);
                
                $provinceData = [
                    'province' => $province,
                    'cities' => array_map(function($city) {
                        return [
                            'id' => $city['id'],
                            'city' => $city['city']
                        ];
                    }, $cities)
                ];
                
                $regionData['provinces'][] = $provinceData;
            }
            
            $data[] = $regionData;
        }
        
        return $data;
    }

    /**
     * Get location details by ID for dropdown display
     */
    public function getLocationForDropdown($locationId)
    {
        $location = $this->find($locationId);
        if (!$location) {
            return null;
        }
        
        return [
            'id' => $location['id'],
            'display' => $location['city'] . ', ' . $location['province'] . ' (' . $location['region'] . ')',
            'city' => $location['city'],
            'province' => $location['province'],
            'region' => $location['region'],
            'full_address' => $this->formatAddress($location)
        ];
    }

    /**
     * Check if location has coordinates for Google Maps
     */
    public function hasCoordinates($location)
    {
        return !empty($location['latitude']) && !empty($location['longitude']);
    }

    /**
     * Get Google Maps URL (works with or without coordinates)
     * With coordinates: Direct link (faster)
     * Without coordinates: Search link (uses geocoding)
     */
    public function getGoogleMapsUrl($location)
    {
        if ($this->hasCoordinates($location)) {
            // Option 1: Direct link with coordinates (FASTER)
            return "https://www.google.com/maps?q={$location['latitude']},{$location['longitude']}";
        } else {
            // Option 2: Search link with address (SLOWER, uses geocoding)
            $address = $this->formatAddress($location);
            return "https://www.google.com/maps/search/" . urlencode($address);
        }
    }    /**
     * Get data formatted for Google Maps JavaScript API
     * Returns array ready for map markers and info windows
     * 
     * @param array $location Location data
     * @param int|null $warehouseId Optional warehouse ID to include warehouse details
     * @return array Map-ready data structure
     */
    public function getMapData($location, $warehouseId = null)
    {
        $mapData = [
            'id'           => $location['id'],
            'warehouse_id' => $warehouseId ?? $location['warehouse_id'] ?? null,
            'title'        => $location['city'] ?? 'Warehouse Location',
            'address'      => $this->formatAddress($location),
            'shortAddress' => $this->getShortAddress($location),
            'lat'          => $location['latitude'] ?? null,
            'lng'          => $location['longitude'] ?? null,
            'hasCoords'    => $this->hasCoordinates($location),
            'city'         => $location['city'] ?? '',
            'region'       => $location['region'] ?? '',
            'province'     => $location['province'] ?? '',
            'postal_code'  => $location['postal_code'] ?? '',
            'street'       => $location['street_address'] ?? '',
            'barangay'     => $location['barangay'] ?? '',
        ];

        // If warehouse ID provided, include warehouse details
        if ($warehouseId !== null) {
            $warehouseModel = new WarehouseModel();
            $warehouse = $warehouseModel->find($warehouseId);
            if ($warehouse) {
                $mapData['warehouse_name'] = $warehouse['name'];
                $mapData['warehouse_code'] = $warehouse['code'];
                $mapData['capacity'] = $warehouse['capacity'];
                $mapData['is_active'] = $warehouse['is_active'];
            }
        }

        return $mapData;
    }

    /**
     * Get all warehouse locations formatted for Google Maps JavaScript API
     * Returns JSON-ready array for map markers
     * 
     * @param bool $includeWarehouseDetails Include warehouse info in each location
     * @return array Array of locations ready for Google Maps
     */    public function getAllWithMapData($includeWarehouseDetails = true)
    {
        if ($includeWarehouseDetails) {
            // Get locations with warehouse details via JOIN
            // Note: warehouses.warehouse_location_id references warehouse_locations.id
            $locations = $this->select('
                warehouse_locations.*,
                warehouses.id as warehouse_id,
                warehouses.name as warehouse_name,
                warehouses.code as warehouse_code,
                warehouses.capacity,
                warehouses.is_active
            ')
            ->join('warehouses', 'warehouses.warehouse_location_id = warehouse_locations.id', 'left')
            ->findAll();

            return array_map(function($location) {
                return [
                    'id'            => $location['id'],
                    'warehouseId'   => $location['warehouse_id'] ?? null,
                    'warehouse_name'=> $location['warehouse_name'] ?? 'Unknown',
                    'warehouse_code'=> $location['warehouse_code'] ?? 'N/A',
                    'title'         => $location['warehouse_name'] ?? $location['city'],
                    'address'       => $this->formatAddress($location),
                    'shortAddress'  => $this->getShortAddress($location),
                    'lat'           => (float)($location['latitude'] ?? 0),
                    'lng'           => (float)($location['longitude'] ?? 0),
                    'hasCoords'     => $this->hasCoordinates($location),
                    'city'          => $location['city'] ?? '',
                    'region'        => $location['region'] ?? '',
                    'capacity'      => $location['capacity'] ?? 0,
                    'is_active'     => (bool)($location['is_active'] ?? false),
                ];
            }, $locations);
        }

        // Simple version without warehouse details
        $locations = $this->findAll();
        $mapData = [];

        foreach ($locations as $location) {
            $mapData[] = $this->getMapData($location);
        }

        return $mapData;
    }

    /**
     * Get JavaScript-ready JSON for Google Maps markers
     * Perfect for embedding in <script> tags
     * 
     * @param bool $includeWarehouseDetails Include warehouse info
     * @return string JSON string ready for JavaScript
     */
    public function getMapDataJSON($includeWarehouseDetails = true)
    {
        $data = $this->getAllWithMapData($includeWarehouseDetails);
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Get Google Maps JavaScript API initialization data
     * Returns array with API key and default settings
     * 
     * @return array Configuration for Google Maps
     */
    public function getMapConfig()
    {
        return [
            'api_key'       => env('GOOGLE_MAPS_API_KEY', 'AIzaSyCU7wTDjCC3iDTuu-pFSS-Ob64IHOnfWvc'),
            'default_zoom'  => 12,
            'default_lat'   => 14.5995, // Manila default
            'default_lng'   => 120.9842,
            'map_type'      => 'roadmap', // roadmap, satellite, hybrid, terrain
            'markers_clustered' => true,
        ];
    }
}
