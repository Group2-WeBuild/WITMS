<?php

namespace App\Controllers;

use App\Models\InventoryModel;
use App\Models\MaterialModel;
use App\Models\MaterialCategoryModel;
use App\Models\WarehouseModel;
use App\Models\UnitsOfMeasureModel;
use App\Models\StockMovementModel;

class WarehouseManagerController extends BaseController
{
    protected $inventoryModel;
    protected $materialModel;
    protected $categoryModel;
    protected $warehouseModel;
    protected $unitModel;
    protected $stockMovementModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->materialModel = new MaterialModel();
        $this->categoryModel = new MaterialCategoryModel();
        $this->warehouseModel = new WarehouseModel();
        $this->unitModel = new UnitsOfMeasureModel();
        $this->stockMovementModel = new StockMovementModel();
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
}
