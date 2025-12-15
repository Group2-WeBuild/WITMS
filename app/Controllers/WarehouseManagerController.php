<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\MaterialModel;
use App\Models\MaterialCategoryModel;
use App\Models\WarehouseModel;
use App\Models\WarehouseLocationModel;
use App\Models\UnitsOfMeasureModel;
use App\Models\WorkAssignmentModel;
use App\Models\StockMovementModel;

class WarehouseManagerController extends BaseController
{
    protected $inventoryModel;
    protected $materialModel;
    protected $categoryModel;
    protected $warehouseModel;
    protected $warehouseLocationModel;
    protected $unitModel;
    protected $workAssignmentModel;
    protected $stockMovementModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->materialModel = new MaterialModel();
        $this->categoryModel = new MaterialCategoryModel();
        $this->warehouseModel = new WarehouseModel();
        $this->warehouseLocationModel = new WarehouseLocationModel();
        $this->unitModel = new UnitsOfMeasureModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->workAssignmentModel = new WorkAssignmentModel();
    }

    /**
     * Check role access
     */
    private function checkAccess()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to continue');
        }

        $userRole = session()->get('user_role');
        $allowedRoles = ['Warehouse Manager', 'IT Administrator'];
        
        if (!in_array($userRole, $allowedRoles)) {
            return redirect()->to('/dashboard')->with('error', 'Access denied');
        }

        return null;
    }

    /**
     * Get user data for views
     */
    private function getUserData()
    {
        return [
            'full_name' => session()->get('full_name'),
            'role' => session()->get('user_role'),
            'email' => session()->get('user_email')
        ];
    }

    // ==========================================
    // INVENTORY MANAGEMENT
    // ==========================================

    /**
     * List all inventory
     */
    public function inventory()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails();
        $stats = $this->inventoryModel->getInventoryStats();

        $data = [
            'title' => 'Inventory Management - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/inventory', $data);
    }

    /**
     * Show add inventory form
     */
    public function inventoryAdd()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Add Stock - WITMS',
            'user' => $this->getUserData(),
            'materials' => $this->materialModel->getActiveMaterials(),
            'warehouses' => $this->warehouseModel->getActiveWarehouses()
        ];

        return view('users/warehouse_manager/inventory_add', $data);
    }

    /**
     * Store new inventory
     */
    public function inventoryStore()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'material_id' => $this->request->getPost('material_id'),
            'warehouse_id' => $this->request->getPost('warehouse_id'),
            'quantity' => $this->request->getPost('quantity'),
            'batch_number' => $this->request->getPost('batch_number'),
            'location_in_warehouse' => $this->request->getPost('location_in_warehouse'),
            'expiration_date' => $this->request->getPost('expiration_date')
        ];

        if ($this->inventoryModel->addStock($data)) {
            // Record stock movement
            $this->stockMovementModel->recordReceipt([
                'material_id' => $data['material_id'],
                'to_warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
                'batch_number' => $data['batch_number'],
                'performed_by' => session()->get('user_id'),
                'notes' => $this->request->getPost('notes')
            ]);

            return redirect()->to('/warehouse-manager/inventory')->with('success', 'Stock added successfully!');
        } else {
            $errors = $this->inventoryModel->errors();
            return redirect()->back()->with('error', 'Failed to add stock: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * Show edit inventory form
     */
    public function inventoryEdit($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails($id);

        if (!$inventory) {
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Inventory item not found');
        }

        $data = [
            'title' => 'Edit Inventory - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory,
            'materials' => $this->materialModel->getActiveMaterials(),
            'warehouses' => $this->warehouseModel->getActiveWarehouses()
        ];

        return view('users/warehouse_manager/inventory_edit', $data);
    }

    /**
     * Update inventory
     */
    public function inventoryUpdate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'location_in_warehouse' => $this->request->getPost('location_in_warehouse'),
            'expiration_date' => $this->request->getPost('expiration_date'),
            'batch_number' => $this->request->getPost('batch_number')
        ];

        if ($this->inventoryModel->update($id, $data)) {
            return redirect()->to('/warehouse-manager/inventory')->with('success', 'Inventory updated successfully!');
        } else {
            $errors = $this->inventoryModel->errors();
            return redirect()->back()->with('error', 'Failed to update: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * View inventory details
     */
    public function inventoryView($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails($id);
        $movements = $this->stockMovementModel->getMovementsByMaterial($inventory['material_id'], 10);

        if (!$inventory) {
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Inventory item not found');
        }

        $data = [
            'title' => 'Inventory Details - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory,
            'movements' => $movements
        ];

        return view('users/warehouse_manager/inventory_view', $data);
    }

    /**
     * Show low stock items
     */
    public function inventoryLowStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $lowStock = $this->inventoryModel->getLowStockItems();

        $data = [
            'title' => 'Low Stock Items - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $lowStock,
            'stats' => ['total_items' => count($lowStock)]
        ];

        return view('users/warehouse_manager/inventory', $data);
    }

    /**
     * Show expiring items
     */
    public function inventoryExpiring()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $expiring = $this->inventoryModel->getExpiringItems(30);

        $data = [
            'title' => 'Expiring Items - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $expiring,
            'stats' => ['total_items' => count($expiring)]
        ];

        return view('users/warehouse_manager/inventory', $data);
    }

    // ==========================================
    // MATERIALS MANAGEMENT
    // ==========================================

    /**
     * List all materials
     */
    public function materials()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $materials = $this->materialModel->getMaterialsWithDetails();
        $materialStats = $this->materialModel->getMaterialStats();
        $categoryStats = $this->categoryModel->getCategoryStats();

        $stats = array_merge($materialStats, $categoryStats);

        $data = [
            'title' => 'Materials Catalog - WITMS',
            'user' => $this->getUserData(),
            'materials' => $materials,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/materials', $data);
    }

    /**
     * Show add material form
     */
    public function materialsAdd()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Add Material - WITMS',
            'user' => $this->getUserData(),
            'categories' => $this->categoryModel->getActiveCategories(),
            'units' => $this->unitModel->getActiveUnits()
        ];

        return view('users/warehouse_manager/materials_add', $data);
    }

    /**
     * Store new material
     */
    public function materialsStore()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'qrcode' => $this->request->getPost('qrcode'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'description' => $this->request->getPost('description'),
            'reorder_level' => $this->request->getPost('reorder_level'),
            'reorder_quantity' => $this->request->getPost('reorder_quantity'),
            'unit_cost' => $this->request->getPost('unit_cost'),
            'is_perishable' => $this->request->getPost('is_perishable') ? 1 : 0,
            'shelf_life_days' => $this->request->getPost('shelf_life_days')
        ];

        if ($this->materialModel->createMaterial($data)) {
            return redirect()->to('/warehouse-manager/materials')->with('success', 'Material added successfully!');
        } else {
            $errors = $this->materialModel->errors();
            return redirect()->back()->with('error', 'Failed to add material: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * Show edit material form
     */
    public function materialsEdit($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $material = $this->materialModel->find($id);

        if (!$material) {
            return redirect()->to('/warehouse-manager/materials')->with('error', 'Material not found');
        }

        $data = [
            'title' => 'Edit Material - WITMS',
            'user' => $this->getUserData(),
            'material' => $material,
            'categories' => $this->categoryModel->getActiveCategories(),
            'units' => $this->unitModel->getActiveUnits()
        ];

        return view('users/warehouse_manager/materials_edit', $data);
    }

    /**
     * Update material
     */
    public function materialsUpdate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;        $data = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'qrcode' => $this->request->getPost('qrcode'),
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'description' => $this->request->getPost('description'),
            'reorder_level' => $this->request->getPost('reorder_level'),
            'reorder_quantity' => $this->request->getPost('reorder_quantity'),
            'unit_cost' => $this->request->getPost('unit_cost'),
            'is_perishable' => $this->request->getPost('is_perishable') ? 1 : 0,
            'shelf_life_days' => $this->request->getPost('shelf_life_days')
        ];

        if ($this->materialModel->updateMaterial($id, $data)) {
            return redirect()->to('/warehouse-manager/materials')->with('success', 'Material updated successfully!');
        } else {
            $errors = $this->materialModel->errors();
            return redirect()->back()->with('error', 'Failed to update material: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * View material details
     */
    public function materialsView($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $material = $this->materialModel->getMaterialsWithDetails($id);

        if (!$material) {
            return redirect()->to('/warehouse-manager/materials')->with('error', 'Material not found');
        }

        $data = [
            'title' => 'Material Details - WITMS',
            'user' => $this->getUserData(),
            'material' => $material
        ];

        return view('users/warehouse_manager/materials_view', $data);
    }

    /**
     * Deactivate material
     */
    public function materialsDeactivate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if ($this->materialModel->deactivateMaterial($id)) {
            return redirect()->to('/warehouse-manager/materials')->with('success', 'Material deactivated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to deactivate material');
        }
    }    
    
    /**
     * Activate material
     */
    public function materialsActivate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if ($this->materialModel->activateMaterial($id)) {
            return redirect()->to('/warehouse-manager/materials')->with('success', 'Material activated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to activate material');
        }
    }

    // ==========================================
    // WAREHOUSE MANAGEMENT
    // ==========================================

    /**
     * List all warehouses
     */
    public function warehouseManagement()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $warehouses = $this->warehouseModel->getWarehousesWithLocations();
        $stats = $this->warehouseModel->getWarehouseStats();

        $data = [
            'title' => 'Warehouse Management - WITMS',
            'user' => $this->getUserData(),
            'warehouses' => $warehouses,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/warehouse_list', $data);
    }

    /**
     * Show add warehouse form
     */
    public function warehouseAdd()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get users with Warehouse Manager role
        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();
        $warehouseManagerRole = $roleModel->getRoleByName('Warehouse Manager');
        $warehouseManagers = [];
        
        if ($warehouseManagerRole) {
            $warehouseManagers = $userModel->getUsersByRole($warehouseManagerRole['id']);
        }

        $data = [
            'title' => 'Add Warehouse - WITMS',
            'user' => $this->getUserData(),
            'warehouseManagers' => $warehouseManagers
        ];

        return view('users/warehouse_manager/warehouse_add', $data);
    }

    /**
     * Store new warehouse
     */
    public function warehouseStore()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // First, create the warehouse location
        $locationData = [
            'street_address' => $this->request->getPost('street_address'),
            'barangay' => $this->request->getPost('barangay'),
            'city' => $this->request->getPost('city'),
            'province' => $this->request->getPost('province'),
            'region' => $this->request->getPost('region'),
            'postal_code' => $this->request->getPost('postal_code'),
            'country' => $this->request->getPost('country') ?? 'Philippines',
            'latitude' => $this->request->getPost('latitude') ?: null,
            'longitude' => $this->request->getPost('longitude') ?: null
        ];

        $locationId = $this->warehouseLocationModel->insert($locationData);

        if (!$locationId) {
            $errors = $this->warehouseLocationModel->errors();
            return redirect()->back()->with('error', 'Failed to create warehouse location: ' . json_encode($errors))->withInput();
        }

        // Then, create the warehouse
        $warehouseData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'warehouse_location_id' => $locationId,
            'capacity' => $this->request->getPost('capacity'),
            'manager_id' => $this->request->getPost('manager_id') ?: null,
            'is_active' => 1
        ];

        if ($this->warehouseModel->insert($warehouseData)) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('success', 'Warehouse created successfully!');
        } else {
            // Rollback: delete the location
            $this->warehouseLocationModel->delete($locationId);
            $errors = $this->warehouseModel->errors();
            return redirect()->back()->with('error', 'Failed to create warehouse: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * Show edit warehouse form
     */
    public function warehouseEdit($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $warehouse = $this->warehouseModel->getWarehouseWithLocation($id);

        if (!$warehouse) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('error', 'Warehouse not found');
        }

        // Get users with Warehouse Manager role
        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();
        $warehouseManagerRole = $roleModel->getRoleByName('Warehouse Manager');
        $warehouseManagers = [];
        
        if ($warehouseManagerRole) {
            $warehouseManagers = $userModel->getUsersByRole($warehouseManagerRole['id']);
        }

        $data = [
            'title' => 'Edit Warehouse - WITMS',
            'user' => $this->getUserData(),
            'warehouse' => $warehouse,
            'warehouseManagers' => $warehouseManagers
        ];

        return view('users/warehouse_manager/warehouse_edit', $data);
    }

    /**
     * Update warehouse
     */
    public function warehouseUpdate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $warehouse = $this->warehouseModel->find($id);
        if (!$warehouse) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('error', 'Warehouse not found');
        }

        // Update warehouse location
        $locationData = [
            'street_address' => $this->request->getPost('street_address'),
            'barangay' => $this->request->getPost('barangay'),
            'city' => $this->request->getPost('city'),
            'province' => $this->request->getPost('province'),
            'region' => $this->request->getPost('region'),
            'postal_code' => $this->request->getPost('postal_code'),
            'country' => $this->request->getPost('country') ?? 'Philippines',
            'latitude' => $this->request->getPost('latitude') ?: null,
            'longitude' => $this->request->getPost('longitude') ?: null
        ];

        if (!$this->warehouseLocationModel->update($warehouse['warehouse_location_id'], $locationData)) {
            $errors = $this->warehouseLocationModel->errors();
            return redirect()->back()->with('error', 'Failed to update location: ' . json_encode($errors))->withInput();
        }

        // Update warehouse info
        $warehouseData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'capacity' => $this->request->getPost('capacity'),
            'manager_id' => $this->request->getPost('manager_id') ?: null
        ];

        if ($this->warehouseModel->update($id, $warehouseData)) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('success', 'Warehouse updated successfully!');
        } else {
            $errors = $this->warehouseModel->errors();
            return redirect()->back()->with('error', 'Failed to update warehouse: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * View warehouse details
     */
    public function warehouseView($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $warehouse = $this->warehouseModel->getWarehouseWithLocation($id);
        
        if (!$warehouse) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('error', 'Warehouse not found');
        }

        // Get inventory count for this warehouse
        $inventoryCount = $this->inventoryModel->where('warehouse_id', $id)->countAllResults();
        
        // Get formatted address
        $locationModel = $this->warehouseLocationModel;
        $location = $locationModel->find($warehouse['warehouse_location_id']);
        $formattedAddress = $location ? $locationModel->formatAddress($location) : 'N/A';

        $data = [
            'title' => 'Warehouse Details - WITMS',
            'user' => $this->getUserData(),
            'warehouse' => $warehouse,
            'inventoryCount' => $inventoryCount,
            'formattedAddress' => $formattedAddress
        ];

        return view('users/warehouse_manager/warehouse_view', $data);
    }

    /**
     * Show warehouse map view
     */
    public function warehouseMap()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;        // Get all warehouses with map data
        $mapData = $this->warehouseLocationModel->getAllWithMapData(true);
        $mapConfig = $this->warehouseLocationModel->getMapConfig();

        $data = [
            'title' => 'Warehouse Map - WITMS',
            'user' => $this->getUserData(),
            'mapData' => $mapData,
            'mapConfig' => $mapConfig,
            'apiKey' => $mapConfig['api_key'] ?? ''
        ];

        return view('users/warehouse_manager/warehouse_map', $data);
    }

    /**
     * Show stock movements
     */
    public function stockMovements()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $movements = $this->stockMovementModel->getMovementsWithDetails();
        $stats = $this->stockMovementModel->getMovementStats();

        $data = [
            'title' => 'Stock Movements - WITMS',
            'user' => $this->getUserData(),
            'movements' => $movements,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/stock_movements', $data);
    }

    /**
     * Get report display name
     */
    private function getReportName($type)
    {
        $names = [
            'inventory' => 'Inventory Summary',
            'lowstock' => 'Low Stock Report',
            'expiring' => 'Expiring Items',
            'movements' => 'Stock Movements',
            'valuation' => 'Inventory Valuation',
            'performance' => 'Warehouse Performance'
        ];
        return $names[$type] ?? 'Unknown Report';
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Reports - WITMS',
            'user' => $this->getUserData(),
            'inventoryStats' => $this->inventoryModel->getInventoryStats(),
            'materialStats' => $this->materialModel->getMaterialStats(),
            'movementStats' => $this->stockMovementModel->getMovementStats(),
            'recentReports' => session()->get('recent_reports', [])
        ];

        return view('users/warehouse_manager/reports', $data);
    }

    /**
     * Generate specific report
     */
    public function generateReport($type)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Track recent reports in session
        $recentReports = session()->get('recent_reports') ?? [];
        $reportInfo = [
            'type' => $type,
            'name' => $this->getReportName($type),
            'generated_at' => date('Y-m-d H:i:s'),
            'url' => base_url('warehouse-manager/reports/generate/' . $type)
        ];
        
        // Add to beginning and keep only last 5
        array_unshift($recentReports, $reportInfo);
        $recentReports = array_slice($recentReports, 0, 5);
        session()->set('recent_reports', $recentReports);

        $data = [
            'user' => $this->getUserData(),
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => session()->get('user_name')
        ];

        switch ($type) {
            case 'inventory':
                $data['title'] = 'Inventory Summary Report';
                $data['inventory'] = $this->inventoryModel->getInventoryWithDetails();
                return view('reports/inventory_summary', $data);

            case 'lowstock':
                $data['title'] = 'Low Stock Report';
                $data['lowStock'] = $this->inventoryModel->getLowStockItems();
                return view('reports/low_stock', $data);

            case 'expiring':
                $data['title'] = 'Expiring Items Report';
                $data['expiring'] = $this->inventoryModel->getExpiringItems(30);
                return view('reports/expiring_items', $data);

            case 'movements':
                $data['title'] = 'Stock Movements Report';
                $data['movements'] = $this->stockMovementModel->getMovementsWithDetails();
                return view('reports/stock_movements', $data);

            case 'valuation':
                $data['title'] = 'Inventory Valuation Report';
                $data['valuation'] = $this->inventoryModel->getInventoryValuation();
                return view('reports/inventory_valuation', $data);

            case 'performance':
                $data['title'] = 'Warehouse Performance Report';
                $data['stats'] = [
                    'total_movements' => $this->stockMovementModel->countAll(),
                    'today_movements' => $this->stockMovementModel->where('DATE(movement_date)', date('Y-m-d'))->countAllResults(),
                    'week_movements' => $this->stockMovementModel->where('movement_date >=', date('Y-m-d', strtotime('-7 days')))->countAllResults(),
                    'month_movements' => $this->stockMovementModel->where('movement_date >=', date('Y-m-d', strtotime('-30 days')))->countAllResults(),
                    'inventory_stats' => $this->inventoryModel->getInventoryStats(),
                    'material_stats' => $this->materialModel->getMaterialStats()
                ];
                return view('reports/warehouse_performance', $data);

            default:
                return redirect()->to('/warehouse-manager/reports')->with('error', 'Invalid report type');
        }
    }

    /**
     * Show analytics page
     */
    public function analytics()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Analytics - WITMS',
            'user' => $this->getUserData(),
            'inventoryStats' => $this->inventoryModel->getInventoryStats(),
            'materialStats' => $this->materialModel->getMaterialStats(),
            'warehouseStats' => $this->warehouseModel->getWarehouseStats()
        ];

        return view('users/warehouse_manager/analytics', $data);
    }

    /**
     * Show stock alerts
     */
    public function stockAlerts()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $lowStockItems = $this->inventoryModel->getLowStockItems();
        $expiringItems = $this->inventoryModel->getExpiringItems();

        $data = [
            'title' => 'Stock Alerts - WITMS',
            'user' => $this->getUserData(),
            'lowStockItems' => $lowStockItems,
            'expiringItems' => $expiringItems
        ];

        return view('users/warehouse_manager/stock_alerts', $data);
    }

    /**
     * Send email to procurement officer
     */
    public function contactProcurement()
    {
        $materialId = $this->request->getPost('materialId');
        
        // Get material details
        $materialModel = new \App\Models\MaterialModel();
        $material = $materialModel->find($materialId);
        
        if (!$material) {
            return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
        }
        
        // Get procurement officer email
        $userModel = new \App\Models\UserModel();
        $procurementOfficers = $userModel->getUsersByRoleName('Procurement Officer');
        
        if (empty($procurementOfficers)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No procurement officer found']);
        }
        
        // Get current warehouse manager info
        $currentUser = $this->getUserData();
        
        // Send email to the first procurement officer
        $procurementEmail = $procurementOfficers[0]['email'];
        $procurementName = trim(($procurementOfficers[0]['first_name'] ?? '') . ' ' . ($procurementOfficers[0]['last_name'] ?? ''));
        if (empty($procurementName)) {
            $procurementName = 'Procurement Officer';
        }
        
        $email = \Config\Services::email();
        $emailConfig = new \Config\Email();
        
        $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
        $email->setTo($procurementEmail);
        $email->setSubject('Stock Alert: Material Reorder Request - ' . $material['name']);
        
        // Create email message
        $message = "Dear {$procurementName},\n\n";
        $message .= "This is an automated stock alert notification from the WITMS system.\n\n";
        $message .= "The following material needs to be reordered:\n\n";
        $message .= "Material Name: {$material['name']}\n";
        $message .= "Material Code: {$material['code']}\n";
        $message .= "Reorder Level: {$material['reorder_level']}\n";
        $message .= "Suggested Reorder Quantity: {$material['reorder_quantity']}\n\n";
        $message .= "Requested by: " . $currentUser['full_name'] . " (Warehouse Manager)\n";
        $message .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Please process this purchase request at your earliest convenience.\n\n";
        $message .= "Thank you,\n";
        $message .= "WeBuild WITMS System";
        
        $email->setMessage($message);
        
        if ($email->send()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Email sent to procurement officer successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to send email: ' . $email->printDebugger(['headers'])]);
        }
    }

    /**
     * Show staff management
     */
    public function staffManagement()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();
        
        // Get warehouse-related roles (excluding Warehouse Manager)
        $warehouseRoles = [
            'Warehouse Staff', 'Inventory Auditor', 'Procurement Officer'
        ];
        
        $staff = [];
        foreach ($warehouseRoles as $roleName) {
            $role = $roleModel->getRoleByName($roleName);
            if ($role) {
                $users = $userModel->getUsersByRole($role['id']);
                foreach ($users as $user) {
                    $user['role_name'] = $roleName;
                    $staff[] = $user;
                }
            }
        }

        $data = [
            'title' => 'Staff Management - WITMS',
            'user' => $this->getUserData(),
            'staff' => $staff
        ];

        return view('users/warehouse_manager/staff_management', $data);
    }

    /**
     * View staff member details
     */
    public function staffView($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userModel = new \App\Models\UserModel();
        
        $staff = $userModel->find($id);
        
        if (!$staff) {
            return redirect()->to('/warehouse-manager/staff-management')->with('error', 'Staff member not found');
        }

        $data = [
            'title' => 'View Staff - WITMS',
            'user' => $this->getUserData(),
            'staff' => $staff
        ];

        return view('users/warehouse_manager/staff_view', $data);
    }

    /**
     * Show edit staff form
     */
    public function staffEdit($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();
        
        $staff = $userModel->find($id);
        
        if (!$staff) {
            return redirect()->to('/warehouse-manager/staff-management')->with('error', 'Staff member not found');
        }

        $data = [
            'title' => 'Edit Staff - WITMS',
            'user' => $this->getUserData(),
            'staff' => $staff,
            'roles' => $roleModel->getActiveRoles()
        ];

        return view('users/warehouse_manager/staff_edit', $data);
    }

    /**
     * Update staff member
     */
    public function staffUpdate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userModel = new \App\Models\UserModel();
        
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        if ($userModel->update($id, $data)) {
            return redirect()->to('/warehouse-manager/staff-management')->with('success', 'Staff member updated successfully!');
        } else {
            $errors = $userModel->errors();
            return redirect()->back()->with('error', 'Failed to update staff member: ' . implode(', ', $errors));
        }
    }

    /**
     * Assign work to staff member
     */
    public function assignWork($userId)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if ($this->request->getMethod() === 'POST') {
            $data = [
                'user_id' => $userId,
                'task_type' => $this->request->getPost('taskType'),
                'task_description' => $this->request->getPost('taskDescription'),
                'location' => $this->request->getPost('taskLocation'),
                'priority' => $this->request->getPost('taskPriority'),
                'deadline' => $this->request->getPost('taskDeadline'),
                'assigned_by' => session()->get('user_id')
            ];

            // Validate required fields
            if (empty($data['task_type'])) {
                return $this->response->setJSON(['success' => false, 'message' => 'Task type is required']);
            }

            // Insert work assignment
            $assignmentId = $this->workAssignmentModel->insert($data);

            if ($assignmentId) {
                // Get staff details for email
                $userModel = new \App\Models\UserModel();
                $staff = $userModel->find($userId);
                $manager = $userModel->find(session()->get('user_id'));

                // Send email notification
                $emailSent = $this->sendWorkAssignmentEmail($staff, $data, $manager);

                return $this->response->setJSON([
                    'success' => true, 
                    'message' => 'Work assignment created successfully' . ($emailSent ? ' and email sent' : ''),
                    'assignment_id' => $assignmentId
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create work assignment']);
            }
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
    }

    /**
     * Send email notification for work assignment
     */
    private function sendWorkAssignmentEmail($staff, $assignment, $manager)
    {
        try {
            $email = \Config\Services::email();
            
            $email->setFrom('noreply@witms.com', 'WITMS System');
            $email->setTo($staff['email']);
            
            $email->setSubject('New Work Assignment - ' . $assignment['task_type']);
            
            $message = "Dear {$staff['first_name']} {$staff['last_name']},\n\n";
            $message .= "You have been assigned a new task:\n\n";
            $message .= "Task Type: " . ucfirst(str_replace('_', ' ', $assignment['task_type'])) . "\n";
            $message .= "Description: " . ($assignment['task_description'] ?? 'No description provided') . "\n";
            if (!empty($assignment['location'])) {
                $message .= "Location: " . $assignment['location'] . "\n";
            }
            $message .= "Priority: " . ucfirst($assignment['priority']) . "\n";
            if (!empty($assignment['deadline'])) {
                $message .= "Deadline: " . date('M j, Y H:i', strtotime($assignment['deadline'])) . "\n";
            }
            $message .= "\nAssigned by: {$manager['first_name']} {$manager['last_name']}\n\n";
            $message .= "Please login to the system for more details.\n";
            $message .= "WITMS Team";
            
            $email->setMessage($message);
            
            // Check if email config is properly set
            if ($email->send()) {
                return true;
            } else {
                log_message('error', 'Email failed to send: ' . $email->printDebugger(['headers']));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show inventory adjustment form
     */
    public function inventoryAdjust($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails($id);

        if (!$inventory) {
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Inventory item not found');
        }

        $data = [
            'title' => 'Adjust Inventory - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory
        ];

        return view('users/warehouse_manager/inventory_adjust', $data);
    }

    /**
     * Process inventory adjustment
     */
    public function inventoryAdjustProcess($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->find($id);
        if (!$inventory) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inventory item not found']);
        }

        $adjustmentType = $this->request->getPost('adjustment_type');
        $quantity = $this->request->getPost('quantity');
        $reason = $this->request->getPost('reason');
        $notes = $this->request->getPost('notes');

        if (empty($adjustmentType) || empty($quantity) || empty($reason)) {
            return $this->response->setJSON(['success' => false, 'message' => 'All fields are required']);
        }

        // Calculate new quantity
        $currentQuantity = $inventory['quantity'];
        $adjustmentQuantity = intval($quantity);
        
        if ($adjustmentType === 'increase') {
            $newQuantity = $currentQuantity + $adjustmentQuantity;
        } else {
            $newQuantity = $currentQuantity - $adjustmentQuantity;
            if ($newQuantity < 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Cannot reduce quantity below zero']);
            }
        }

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update inventory quantity
            $this->inventoryModel->update($id, ['quantity' => $newQuantity]);

            // Create stock movement record
            $stockMovementModel = new \App\Models\StockMovementModel();
            $referenceNumber = 'ADJ-' . date('Y') . '-' . str_pad($stockMovementModel->countAll() + 1, 6, '0', STR_PAD_LEFT);
            
            $movementData = [
                'reference_number' => $referenceNumber,
                'material_id' => $inventory['material_id'],
                'from_warehouse_id' => $inventory['warehouse_id'],
                'to_warehouse_id' => $inventory['warehouse_id'],
                'movement_type' => 'Adjustment',
                'quantity' => $adjustmentQuantity,
                'batch_number' => $inventory['batch_number'],
                'movement_date' => date('Y-m-d H:i:s'),
                'performed_by' => session()->get('user_id'),
                'notes' => "Adjustment: {$reason}. {$notes}"
            ];

            $stockMovementModel->insert($movementData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to process adjustment']);
            }

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Inventory adjusted successfully',
                'new_quantity' => $newQuantity
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Inventory adjustment error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'An error occurred during adjustment']);
        }
    }

    /**
     * Activate warehouse
     */
    public function warehouseActivate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if ($this->warehouseModel->toggleWarehouseStatus($id, true)) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('success', 'Warehouse activated successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to activate warehouse');
        }
    }
}
