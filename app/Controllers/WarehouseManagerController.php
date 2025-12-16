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
use App\Models\UserWarehouseAssignmentModel;
use App\Libraries\QRCodeLibrary;

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
    protected $userWarehouseAssignmentModel;
    protected $qrLibrary;

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
        $this->userWarehouseAssignmentModel = new UserWarehouseAssignmentModel();
        $this->qrLibrary = new QRCodeLibrary();
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

    /**
     * Get assigned warehouse IDs for the logged-in user
     * Returns array of warehouse IDs, or null if user has access to all warehouses (IT Admin)
     */
    private function getAssignedWarehouseIds()
    {
        $userRole = session()->get('user_role');
        $userId = session()->get('user_id');
        
        // IT Administrators have access to all warehouses
        if ($userRole === 'IT Administrator') {
            return null; // null means no filtering (all warehouses)
        }
        
        // Get assigned warehouses for Warehouse Manager
        $assignments = $this->userWarehouseAssignmentModel->getWarehousesByUser($userId, true);
        
        if (empty($assignments)) {
            return []; // No assignments, return empty array (will show nothing)
        }
        
        // Extract warehouse IDs
        return array_column($assignments, 'warehouse_id');
    }

    /**
     * Check if user has access to a specific warehouse
     */
    private function hasWarehouseAccess($warehouseId)
    {
        $assignedIds = $this->getAssignedWarehouseIds();
        
        // If null, user has access to all warehouses (IT Admin)
        if ($assignedIds === null) {
            return true;
        }
        
        // Check if warehouse ID is in assigned list
        return in_array($warehouseId, $assignedIds);
    }

    // ==========================================
    // INVENTORY MANAGEMENT
    // ==========================================

    /**
     * List all inventory
     * NOTE: Warehouse Managers oversee inventory at each warehouse, so they see ALL inventory
     * This method intentionally does NOT filter by assigned warehouses
     */
    public function inventory()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get ALL inventory (no filtering) - Warehouse Managers oversee all warehouses
        $inventory = $this->inventoryModel->getInventoryWithDetails();
        $stats = $this->inventoryModel->getInventoryStats();
        
        // Get all warehouses for filter dropdown
        $warehouses = $this->warehouseModel->findAll();
        $categories = $this->categoryModel->findAll();

        $data = [
            'title' => 'Inventory Management - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory,
            'stats' => $stats,
            'warehouses' => $warehouses,
            'categories' => $categories
        ];

        return view('users/warehouse_manager/inventory', $data);
    }

    /**
     * Recalculate all available quantities
     */
    public function inventoryRecalculateAvailable()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $updated = $this->inventoryModel->recalculateAllAvailableQuantities();
        
        return redirect()->to('/warehouse-manager/inventory')
            ->with('success', "Recalculated available quantities for {$updated} inventory item(s).");
    }

    /**
     * Show add inventory form
     * Warehouse dropdown filtered to show only assigned warehouses
     */
    public function inventoryAdd()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get all active warehouses
        $allWarehouses = $this->warehouseModel->getActiveWarehouses();
        
        // Filter to only assigned warehouses for dropdown
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $warehouses = array_filter($allWarehouses, function($warehouse) use ($assignedWarehouseIds) {
                return in_array($warehouse['id'], $assignedWarehouseIds);
            });
            $warehouses = array_values($warehouses);
        } else {
            // IT Admin or no assignments - show all warehouses
            $warehouses = $allWarehouses;
        }

        $data = [
            'title' => 'Add Stock - WITMS',
            'user' => $this->getUserData(),
            'materials' => $this->materialModel->getActiveMaterials(),
            'warehouses' => $warehouses
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

        // Validate expiration date if provided
        $expirationDate = $this->request->getPost('expiration_date');
        if ($expirationDate) {
            $expDate = strtotime($expirationDate);
            $today = strtotime(date('Y-m-d'));
            
            if ($expDate <= $today) {
                return redirect()->back()
                    ->with('error', 'Expiration date must be in the future. Please select a future date or leave it empty.')
                    ->withInput();
            }
        }

        // Batch number will be auto-generated if empty (handled by InventoryModel callback)
        $batchNumber = trim($this->request->getPost('batch_number') ?? '');
        if (empty($batchNumber)) {
            $batchNumber = ''; // Let the model auto-generate it
        }

        $data = [
            'material_id' => $this->request->getPost('material_id'),
            'warehouse_id' => $this->request->getPost('warehouse_id'),
            'quantity' => $this->request->getPost('quantity'),
            'batch_number' => $batchNumber,
            'location_in_warehouse' => $this->request->getPost('location_in_warehouse'),
            'expiration_date' => $expirationDate ?: null
        ];

        if ($this->inventoryModel->addStock($data)) {
            // Get the generated batch number from the inventory record
            $inventory = $this->inventoryModel
                ->where('material_id', $data['material_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->orderBy('id', 'DESC')
                ->first();
            
            $generatedBatchNumber = $inventory['batch_number'] ?? $batchNumber;
            
            // Record stock movement
            $this->stockMovementModel->recordReceipt([
                'material_id' => $data['material_id'],
                'to_warehouse_id' => $data['warehouse_id'],
                'quantity' => $data['quantity'],
                'batch_number' => $generatedBatchNumber,
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
     * Get material quantity in warehouse (AJAX)
     */
    public function getMaterialQuantity()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $materialId = $this->request->getPost('material_id');
        $warehouseId = $this->request->getPost('warehouse_id');

        if (!$materialId || !$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Material ID and Warehouse ID are required']);
        }

        try {
            // Get material details
            $material = $this->materialModel->find($materialId);
            if (!$material) {
                return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
            }

            // Get unit of measure
            $unit = $this->unitModel->find($material['unit_id']);
            $unitAbbr = $unit ? $unit['abbreviation'] : '';

            // Get inventory for this material in this warehouse
            $inventory = $this->inventoryModel
                ->where('material_id', $materialId)
                ->where('warehouse_id', $warehouseId)
                ->findAll();

            $totalQuantity = 0;
            $totalAvailable = 0;

            foreach ($inventory as $item) {
                $totalQuantity += floatval($item['quantity'] ?? 0);
                $totalAvailable += floatval($item['available_quantity'] ?? 0);
            }

            return $this->response->setJSON([
                'success' => true,
                'quantity' => $totalQuantity,
                'available_quantity' => $totalAvailable,
                'unit' => $unitAbbr
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting material quantity: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit inventory form
     * Only allows editing inventory from assigned warehouses
     */
    public function inventoryEdit($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails($id);

        if (!$inventory) {
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Inventory item not found');
        }

        // Check if user has access to this warehouse
        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // If user has assigned warehouses, check if this inventory belongs to one
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            if (!in_array($inventory['warehouse_id'], $assignedWarehouseIds)) {
                return redirect()->to('/warehouse-manager/inventory')->with('error', 'Access denied. You can only edit inventory from your assigned warehouses.');
            }
        } elseif ($assignedWarehouseIds === []) {
            // User has no warehouse assignments
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Access denied. You have no assigned warehouses.');
        }

        // Batch number will be auto-generated by the model if empty during update

        $data = [
            'title' => 'Edit Inventory - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $inventory
        ];

        return view('users/warehouse_manager/inventory_edit', $data);
    }

    /**
     * Update inventory
     * Only allows updating inventory from assigned warehouses
     */
    public function inventoryUpdate($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Check if inventory exists and user has access
        $inventory = $this->inventoryModel->find($id);
        if (!$inventory) {
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Inventory item not found');
        }

        // Check if user has access to this warehouse
        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // If user has assigned warehouses, check if this inventory belongs to one
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            if (!in_array($inventory['warehouse_id'], $assignedWarehouseIds)) {
                return redirect()->to('/warehouse-manager/inventory')->with('error', 'Access denied. You can only update inventory from your assigned warehouses.');
            }
        } elseif ($assignedWarehouseIds === []) {
            // User has no warehouse assignments
            return redirect()->to('/warehouse-manager/inventory')->with('error', 'Access denied. You have no assigned warehouses.');
        }

        // Prepare update data - include material_id and warehouse_id for batch number generation
        $data = [
            'material_id' => $inventory['material_id'],
            'warehouse_id' => $inventory['warehouse_id'],
            'location_in_warehouse' => $this->request->getPost('location_in_warehouse'),
            'expiration_date' => $this->request->getPost('expiration_date') ?: null
        ];

        // Batch number is auto-generated by model callback if empty
        // Don't include it in update data - let the model handle it

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
     * NOTE: Shows ALL low stock items (no filtering) - Warehouse Managers oversee all warehouses
     */
    public function inventoryLowStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get ALL low stock items (no filtering)
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
     * NOTE: Shows ALL expiring items (no filtering) - Warehouse Managers oversee all warehouses
     */
    public function inventoryExpiring()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Get ALL expiring items (no filtering)
        $expiring = $this->inventoryModel->getExpiringItems(30);

        $data = [
            'title' => 'Expiring Items - WITMS',
            'user' => $this->getUserData(),
            'inventory' => $expiring,
            'stats' => ['total_items' => count($expiring)]
        ];

        return view('users/warehouse_manager/inventory', $data);
    }

    /**
     * Generate QR code for inventory item
     */
    public function inventoryGenerateQR($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $inventory = $this->inventoryModel->getInventoryWithDetails($id);
        
        if (!$inventory) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inventory item not found']);
        }
        
        // Prepare file path
        $timestamp = microtime(true);
        $filename = 'inventory_' . $id . '_' . str_replace('.', '_', $timestamp);
        $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
        
        // Ensure directory exists
        $qrPath = WRITEPATH . 'qrcodes/';
        if (!is_dir($qrPath)) {
            if (!mkdir($qrPath, 0755, true)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create QR code directory']);
            }
        }
        
        // Generate QR code directly to file
        $this->qrLibrary->generateInventoryQR(
            $inventory['id'],
            $inventory['material_code'],
            $inventory['material_name'] ?? '',
            $inventory['warehouse_code'],
            $inventory['batch_number'],
            $filepath
        );
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('warehouse-manager/qrcodes/' . basename($filepath)),
            'filename' => basename($filepath),
            'download_url' => base_url('warehouse-manager/inventory/qr-download/' . basename($filepath))
        ]);
    }

    /**
     * Download QR code for inventory item
     */
    public function inventoryDownloadQR($filename)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $filepath = WRITEPATH . 'qrcodes/' . $filename;
        
        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'QR code file not found');
        }
        
        return $this->response->download($filepath, null);
    }

    /**
     * Batch generate QR codes for inventory items
     */
    public function inventoryBatchGenerateQR()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $ids = $this->request->getPost('ids');
        
        if (!$ids || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid inventory IDs']);
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($ids as $id) {
            $inventory = $this->inventoryModel->getInventoryWithDetails($id);
            
            if ($inventory) {
                try {
                    // Prepare file path
                    $timestamp = microtime(true);
                    $filename = 'inventory_' . $id . '_' . str_replace('.', '_', $timestamp);
                    $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
                    
                    // Ensure directory exists
                    $qrPath = WRITEPATH . 'qrcodes/';
                    if (!is_dir($qrPath)) {
                        if (!mkdir($qrPath, 0755, true)) {
                            throw new \RuntimeException('Failed to create QR code directory');
                        }
                    }
                    
                    // Generate QR code directly to file
                    $this->qrLibrary->generateInventoryQR(
                        $inventory['id'],
                        $inventory['material_code'],
                        $inventory['material_name'] ?? '',
                        $inventory['warehouse_code'],
                        $inventory['batch_number'],
                        $filepath
                    );
                    
                    $results[$id] = [
                        'success' => true,
                        'qr_code' => base_url('warehouse-manager/qrcodes/' . basename($filepath)),
                        'filename' => basename($filepath),
                        'material_name' => $inventory['material_name'],
                        'warehouse_name' => $inventory['warehouse_name']
                    ];
                    $successCount++;
                } catch (\Exception $e) {
                    log_message('error', 'QR Code generation failed for inventory ID ' . $id . ': ' . $e->getMessage());
                    $results[$id] = [
                        'success' => false,
                        'message' => 'Failed to generate QR code: ' . $e->getMessage()
                    ];
                    $failCount++;
                }
            } else {
                $results[$id] = [
                    'success' => false,
                    'message' => 'Inventory item not found'
                ];
                $failCount++;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'results' => $results,
            'summary' => [
                'total' => count($ids),
                'success' => $successCount,
                'failed' => $failCount
            ]
        ]);
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
        if ($accessCheck) return $accessCheck;

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => [
                'label' => 'Material Name',
                'rules' => 'required|regex_match[/^[A-Za-z\s]+$/]',
                'errors' => [
                    'required' => 'Material name is required',
                    'regex_match' => 'Material name can only contain letters and spaces (no special characters)'
                ]
            ],
            'code' => [
                'label' => 'Material Code',
                'rules' => 'required|regex_match[/^[A-Z0-9]+(-[A-Z0-9]+)*$/]',
                'errors' => [
                    'required' => 'Material code is required',
                    'regex_match' => 'Material code must be in uppercase format like MAT101 or MAT-101'
                ]
            ],
            'unit_cost' => [
                'label' => 'Unit Cost',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Unit cost is required',
                    'decimal' => 'Unit cost must be a valid number',
                    'greater_than' => 'Unit cost must be greater than 0'
                ]
            ],
            'reorder_level' => [
                'label' => 'Reorder Level',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Reorder level is required',
                    'decimal' => 'Reorder level must be a valid number',
                    'greater_than' => 'Reorder level must be greater than 0'
                ]
            ],
            'reorder_quantity' => [
                'label' => 'Reorder Quantity',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Reorder quantity is required',
                    'decimal' => 'Reorder quantity must be a valid number',
                    'greater_than' => 'Reorder quantity must be greater than 0'
                ]
            ],
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer'
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validation->getErrors()));
        }

        // Sanitize and format data
        $name = trim($this->request->getPost('name'));
        $code = strtoupper(trim($this->request->getPost('code')));
        $unitCost = floatval($this->request->getPost('unit_cost'));
        $reorderLevel = floatval($this->request->getPost('reorder_level'));
        $reorderQuantity = floatval($this->request->getPost('reorder_quantity'));

        // Additional validation
        if ($unitCost <= 0 || $reorderLevel <= 0 || $reorderQuantity <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unit Cost, Reorder Level, and Reorder Quantity must be greater than 0');
        }

        $data = [
            'name' => $name,
            'code' => $code,
            'qrcode' => '', // QR code is auto-generated, don't accept user input
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'description' => $this->request->getPost('description'),
            'reorder_level' => $reorderLevel,
            'reorder_quantity' => $reorderQuantity,
            'unit_cost' => $unitCost,
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
        if ($accessCheck) return $accessCheck;

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => [
                'label' => 'Material Name',
                'rules' => 'required|regex_match[/^[A-Za-z\s]+$/]',
                'errors' => [
                    'required' => 'Material name is required',
                    'regex_match' => 'Material name can only contain letters and spaces (no special characters)'
                ]
            ],
            'code' => [
                'label' => 'Material Code',
                'rules' => 'required|regex_match[/^[A-Z0-9]+(-[A-Z0-9]+)*$/]',
                'errors' => [
                    'required' => 'Material code is required',
                    'regex_match' => 'Material code must be in uppercase format like MAT101 or MAT-101'
                ]
            ],
            'unit_cost' => [
                'label' => 'Unit Cost',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Unit cost is required',
                    'decimal' => 'Unit cost must be a valid number',
                    'greater_than' => 'Unit cost must be greater than 0'
                ]
            ],
            'reorder_level' => [
                'label' => 'Reorder Level',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Reorder level is required',
                    'decimal' => 'Reorder level must be a valid number',
                    'greater_than' => 'Reorder level must be greater than 0'
                ]
            ],
            'reorder_quantity' => [
                'label' => 'Reorder Quantity',
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'Reorder quantity is required',
                    'decimal' => 'Reorder quantity must be a valid number',
                    'greater_than' => 'Reorder quantity must be greater than 0'
                ]
            ],
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer'
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Validation failed: ' . implode(', ', $validation->getErrors()));
        }

        // Sanitize and format data
        $name = trim($this->request->getPost('name'));
        $code = strtoupper(trim($this->request->getPost('code')));
        $unitCost = floatval($this->request->getPost('unit_cost'));
        $reorderLevel = floatval($this->request->getPost('reorder_level'));
        $reorderQuantity = floatval($this->request->getPost('reorder_quantity'));

        // Additional validation
        if ($unitCost <= 0 || $reorderLevel <= 0 || $reorderQuantity <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unit Cost, Reorder Level, and Reorder Quantity must be greater than 0');
        }

        // Get existing material to preserve QR code
        $existingMaterial = $this->materialModel->find($id);
        if (!$existingMaterial) {
            return redirect()->to('/warehouse-manager/materials')->with('error', 'Material not found');
        }

        $data = [
            'name' => $name,
            'code' => $code,
            'qrcode' => $existingMaterial['qrcode'] ?? '', // Preserve existing QR code (readonly)
            'category_id' => $this->request->getPost('category_id'),
            'unit_id' => $this->request->getPost('unit_id'),
            'description' => $this->request->getPost('description'),
            'reorder_level' => $reorderLevel,
            'reorder_quantity' => $reorderQuantity,
            'unit_cost' => $unitCost,
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

    /**
     * Generate QR code for material
     */
    public function materialsGenerateQR($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $material = $this->materialModel->find($id);
        
        if (!$material) {
            return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
        }
        
        // Generate QR code and get the image data
        // Prepare file path
        $timestamp = microtime(true);
        $filename = 'material_' . $id . '_' . str_replace('.', '_', $timestamp);
        $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
        
        // Ensure directory exists
        $qrPath = WRITEPATH . 'qrcodes/';
        if (!is_dir($qrPath)) {
            if (!mkdir($qrPath, 0755, true)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to create QR code directory']);
            }
        }
        
        // Generate QR code directly to file
        $this->qrLibrary->generateMaterialQR(
            $material['id'],
            $material['code'],
            $material['name'],
            $filepath
        );
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('warehouse-manager/qrcodes/' . basename($filepath)),
            'filename' => basename($filepath),
            'download_url' => base_url('warehouse-manager/materials/qr-download/' . basename($filepath))
        ]);
    }

    /**
     * Download QR code for material
     */
    public function materialsDownloadQR($filename)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $filepath = WRITEPATH . 'qrcodes/' . $filename;
        
        if (!file_exists($filepath)) {
            return redirect()->back()->with('error', 'QR code file not found');
        }
        
        return $this->response->download($filepath, null);
    }

    /**
     * View all materials with QR codes for printing
     */
    public function materialsQRPrint()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $materials = $this->materialModel->getMaterialsWithDetails();
        
        // Generate QR codes for all materials that don't have one yet
        $materialsWithQR = [];
        foreach ($materials as $material) {
            // Check if QR code file exists
            $qrFiles = glob(WRITEPATH . 'qrcodes/material_' . $material['id'] . '_*.png');
            $qrCodeUrl = null;
            $qrFilename = null;
            
            if (!empty($qrFiles)) {
                // Use the most recent QR code
                usort($qrFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $latestFile = $qrFiles[0];
                $qrFilename = basename($latestFile);
                $qrCodeUrl = base_url('warehouse-manager/qrcodes/' . $qrFilename);
            } else {
                // Auto-generate QR code if it doesn't exist
                try {
                    $timestamp = microtime(true);
                    $filename = 'material_' . $material['id'] . '_' . str_replace('.', '_', $timestamp);
                    $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
                    
                    // Ensure directory exists
                    $qrPath = WRITEPATH . 'qrcodes/';
                    if (!is_dir($qrPath)) {
                        if (!mkdir($qrPath, 0755, true)) {
                            throw new \RuntimeException('Failed to create QR code directory');
                        }
                    }
                    
                    // Generate QR code directly to file
                    $this->qrLibrary->generateMaterialQR(
                        $material['id'],
                        $material['code'],
                        $material['name'],
                        $filepath
                    );
                    
                    $qrFilename = basename($filepath);
                    $qrCodeUrl = base_url('warehouse-manager/qrcodes/' . $qrFilename);
                } catch (\Exception $e) {
                    log_message('error', 'Failed to auto-generate QR for material ' . $material['id'] . ': ' . $e->getMessage());
                    // Continue without QR code
                }
            }
            
            $material['qr_code_url'] = $qrCodeUrl;
            $material['qr_filename'] = $qrFilename;
            $materialsWithQR[] = $material;
        }

        $data = [
            'title' => 'Material QR Codes - Print View - WITMS',
            'user' => $this->getUserData(),
            'materials' => $materialsWithQR
        ];

        return view('users/warehouse_manager/materials_qr_print', $data);
    }

    /**
     * List all material categories
     */
    public function materialsCategories()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $categories = $this->categoryModel->getCategoriesWithHierarchy();
        $stats = $this->categoryModel->getCategoryStats();

        $data = [
            'title' => 'Material Categories - WITMS',
            'user' => $this->getUserData(),
            'categories' => $categories,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/materials_categories', $data);
    }

    /**
     * Batch generate QR codes for materials
     */
    public function materialsBatchGenerateQR()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $ids = $this->request->getPost('ids');
        
        if (!$ids || !is_array($ids)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid material IDs']);
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($ids as $id) {
            $material = $this->materialModel->find($id);
            
            if ($material) {
                try {
                    // Prepare file path
                    $timestamp = microtime(true);
                    $filename = 'material_' . $id . '_' . str_replace('.', '_', $timestamp);
                    $filepath = WRITEPATH . 'qrcodes/' . $filename . '.png';
                    
                    // Ensure directory exists
                    $qrPath = WRITEPATH . 'qrcodes/';
                    if (!is_dir($qrPath)) {
                        if (!mkdir($qrPath, 0755, true)) {
                            throw new \RuntimeException('Failed to create QR code directory');
                        }
                    }
                    
                    // Generate QR code directly to file
                    $this->qrLibrary->generateMaterialQR(
                        $material['id'],
                        $material['code'],
                        $material['name'],
                        $filepath
                    );
                    
                    $results[$id] = [
                        'success' => true,
                        'qr_code' => base_url('warehouse-manager/qrcodes/' . basename($filepath)),
                        'filename' => basename($filepath),
                        'material_name' => $material['name']
                    ];
                    $successCount++;
                } catch (\Exception $e) {
                    log_message('error', 'QR Code generation failed for material ID ' . $id . ': ' . $e->getMessage());
                    $results[$id] = [
                        'success' => false,
                        'message' => 'Failed to generate QR code: ' . $e->getMessage()
                    ];
                    $failCount++;
                }
            } else {
                $results[$id] = [
                    'success' => false,
                    'message' => 'Material not found'
                ];
                $failCount++;
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'results' => $results,
            'summary' => [
                'total' => count($ids),
                'success' => $successCount,
                'failed' => $failCount
            ]
        ]);
    }

    // ==========================================
    // WAREHOUSE MANAGEMENT
    // ==========================================

    /**
     * List all warehouses
     * Filtered to show only assigned warehouses
     */
    public function warehouseManagement()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get all warehouses
        $allWarehouses = $this->warehouseModel->getWarehousesWithLocations();
        
        // Filter to only assigned warehouses
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $warehouses = array_filter($allWarehouses, function($warehouse) use ($assignedWarehouseIds) {
                return in_array($warehouse['id'], $assignedWarehouseIds);
            });
            $warehouses = array_values($warehouses);
        } else {
            // IT Admin or no assignments - show all warehouses
            $warehouses = $allWarehouses;
        }
        
        // Calculate stats for filtered warehouses
        $totalCapacity = array_sum(array_column($warehouses, 'capacity'));
        $stats = [
            'total' => count($warehouses),
            'total_warehouses' => count($warehouses),
            'active' => count(array_filter($warehouses, fn($w) => $w['is_active'])),
            'active_warehouses' => count(array_filter($warehouses, fn($w) => $w['is_active'])),
            'inactive' => count(array_filter($warehouses, fn($w) => !$w['is_active'])),
            'with_managers' => count(array_filter($warehouses, fn($w) => !empty($w['manager_id']))),
            'managed_warehouses' => count(array_filter($warehouses, fn($w) => !empty($w['manager_id']))),
            'total_capacity' => number_format($totalCapacity, 0)
        ];

        $data = [
            'title' => 'Warehouse Overview - WITMS',
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

        // Validate manager_id is required
        $managerId = $this->request->getPost('manager_id');
        if (empty($managerId)) {
            // Rollback: delete the location
            $this->warehouseLocationModel->delete($locationId);
            return redirect()->back()->with('error', 'Manager is required. Please select a warehouse manager.')->withInput();
        }

        // Then, create the warehouse
        $warehouseData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'warehouse_location_id' => $locationId,
            'capacity' => $this->request->getPost('capacity'),
            'manager_id' => $managerId,
            'is_active' => 1
        ];

        if ($this->warehouseModel->insert($warehouseData)) {
            $warehouseId = $this->warehouseModel->getInsertID();
            
            // Automatically assign the selected manager to this warehouse
            $this->assignManagerToWarehouse($managerId, $warehouseId);
            
            return redirect()->to('/warehouse-manager/warehouse-management')->with('success', 'Warehouse created successfully! Manager has been automatically assigned to this warehouse.');
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

        // Validate manager_id is required
        $managerId = $this->request->getPost('manager_id');
        if (empty($managerId)) {
            return redirect()->back()->with('error', 'Manager is required. Please select a warehouse manager.')->withInput();
        }

        // Get old manager_id before update
        $oldManagerId = $warehouse['manager_id'] ?? null;

        // Update warehouse info
        $warehouseData = [
            'name' => $this->request->getPost('name'),
            'code' => $this->request->getPost('code'),
            'capacity' => $this->request->getPost('capacity'),
            'manager_id' => $managerId
        ];

        if ($this->warehouseModel->update($id, $warehouseData)) {
            // If manager changed, update assignments
            if ($oldManagerId != $managerId) {
                // Deactivate old manager's assignment if they were assigned
                if ($oldManagerId) {
                    $this->userWarehouseAssignmentModel
                        ->where('user_id', $oldManagerId)
                        ->where('warehouse_id', $id)
                        ->update(['is_active' => 0]);
                }
                
                // Assign new manager to warehouse
                $this->assignManagerToWarehouse($managerId, $id);
            } else {
                // Manager didn't change, but ensure assignment exists and is active
                $this->assignManagerToWarehouse($managerId, $id);
            }
            
            return redirect()->to('/warehouse-manager/warehouse-management')->with('success', 'Warehouse updated successfully! Manager assignment has been updated.');
        } else {
            $errors = $this->warehouseModel->errors();
            return redirect()->back()->with('error', 'Failed to update warehouse: ' . json_encode($errors))->withInput();
        }
    }

    /**
     * Assign manager to warehouse automatically
     * This is called when creating/updating a warehouse with a manager
     */
    private function assignManagerToWarehouse($managerId, $warehouseId)
    {
        try {
            // Get manager's role_id
            $userModel = new \App\Models\UserModel();
            $manager = $userModel->find($managerId);
            
            if (!$manager) {
                log_message('error', "Manager not found: {$managerId}");
                return false;
            }
            
            // Check if assignment already exists
            $existingAssignment = $this->userWarehouseAssignmentModel
                ->where('user_id', $managerId)
                ->where('warehouse_id', $warehouseId)
                ->first();
            
            // Get user's role_id
            $roleId = $manager['role_id'] ?? null;
            if (!$roleId) {
                log_message('error', "Manager does not have a role_id: {$managerId}");
                return false;
            }
            
            // Check if this is the manager's first warehouse assignment (set as primary)
            $existingAssignments = $this->userWarehouseAssignmentModel
                ->where('user_id', $managerId)
                ->where('is_active', 1)
                ->where('is_primary', 1)
                ->first();
            
            $isPrimary = empty($existingAssignments) ? 1 : 0;
            
            $assignmentData = [
                'role_id' => $roleId,
                'is_active' => 1,
                'is_primary' => $isPrimary,
                'assigned_by' => session()->get('user_id'),
                'assigned_at' => date('Y-m-d H:i:s'),
                'notes' => 'Auto-assigned when set as warehouse manager'
            ];
            
            if ($existingAssignment) {
                // Update existing assignment
                $this->userWarehouseAssignmentModel->update($existingAssignment['id'], $assignmentData);
                log_message('info', "Updated warehouse assignment for manager {$managerId} to warehouse {$warehouseId}");
            } else {
                // Create new assignment
                $assignmentData['user_id'] = $managerId;
                $assignmentData['warehouse_id'] = $warehouseId;
                $this->userWarehouseAssignmentModel->insert($assignmentData);
                log_message('info', "Created warehouse assignment for manager {$managerId} to warehouse {$warehouseId}");
            }
            
            // If set as primary, unset other primary warehouses for this manager
            if ($isPrimary) {
                $this->userWarehouseAssignmentModel
                    ->where('user_id', $managerId)
                    ->where('warehouse_id !=', $warehouseId)
                    ->update(['is_primary' => 0]);
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error assigning manager to warehouse: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * View warehouse details
     */
    public function warehouseView($id)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Check if user has access to this warehouse
        if (!$this->hasWarehouseAccess($id)) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('error', 'Access denied to this warehouse');
        }

        $warehouse = $this->warehouseModel->getWarehouseWithLocation($id);
        
        if (!$warehouse) {
            return redirect()->to('/warehouse-manager/warehouse-management')->with('error', 'Warehouse not found');
        }

        // Get inventory count for this warehouse
        $inventoryCount = $this->inventoryModel->where('warehouse_id', $id)->countAllResults();
        
        // Get formatted address and map data
        $locationModel = $this->warehouseLocationModel;
        $location = null;
        $formattedAddress = 'N/A';
        $mapData = null;
        
        // Check if warehouse has a location ID
        if (!empty($warehouse['warehouse_location_id'])) {
            $location = $locationModel->find($warehouse['warehouse_location_id']);
            if ($location) {
                $formattedAddress = $locationModel->formatAddress($location);
                $mapData = $locationModel->getMapData($location, $id);
            }
        }
        
        // Get map config
        $mapConfig = $locationModel->getMapConfig();

        $data = [
            'title' => 'Warehouse Details - WITMS',
            'user' => $this->getUserData(),
            'warehouse' => $warehouse,
            'inventoryCount' => $inventoryCount,
            'formattedAddress' => $formattedAddress,
            'mapData' => $mapData,
            'mapConfig' => $mapConfig,
            'apiKey' => $mapConfig['api_key'] ?? ''
        ];

        return view('users/warehouse_manager/warehouse_view', $data);
    }

    /**
     * Show warehouse map view
     * Filtered to show only assigned warehouses
     */
    public function warehouseMap()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;
        
        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get all warehouses with map data
        $allMapData = $this->warehouseLocationModel->getAllWithMapData(true);
        
        // Filter to only active locations with assigned warehouses
        $mapData = array_filter($allMapData, function($warehouse) use ($assignedWarehouseIds) {
            // Only show active locations
            if (!isset($warehouse['is_active']) || !$warehouse['is_active']) {
                return false;
            }
            
            // Check if warehouse has an ID (some locations might not have warehouses)
            if (!isset($warehouse['warehouseId']) || $warehouse['warehouseId'] === null) {
                return false;
            }
            
            // Filter to only assigned warehouses if user has assignments
            if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
                return in_array($warehouse['warehouseId'], $assignedWarehouseIds);
            }
            
            // IT Admin or no assignments - show all active warehouses
            return true;
        });
        
        $mapData = array_values($mapData);
        
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
     * Filtered to show only movements in assigned warehouses
     */
    public function stockMovements()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get all movements
        $allMovements = $this->stockMovementModel->getMovementsWithDetails();
        
        // Filter to only movements in assigned warehouses
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $movements = array_filter($allMovements, function($movement) use ($assignedWarehouseIds) {
                $fromWarehouse = $movement['from_warehouse_id'] ?? null;
                $toWarehouse = $movement['to_warehouse_id'] ?? null;
                
                // Include if movement is from or to an assigned warehouse
                return ($fromWarehouse && in_array($fromWarehouse, $assignedWarehouseIds)) ||
                       ($toWarehouse && in_array($toWarehouse, $assignedWarehouseIds));
            });
            $movements = array_values($movements);
        } else {
            // IT Admin or no assignments - show all movements
            $movements = $allMovements;
        }
        
        // Calculate stats for filtered movements
        $stats = [
            'total' => count($movements),
            'receipts' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Receipt')),
            'issues' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Issue')),
            'transfers' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Transfer')),
            'adjustments' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Adjustment'))
        ];

        $data = [
            'title' => 'Stock Movements - WITMS',
            'user' => $this->getUserData(),
            'movements' => $movements,
            'stats' => $stats
        ];

        return view('users/warehouse_manager/stock_movements', $data);
    }

    /**
     * Get stock movement statistics (AJAX endpoint)
     * Filtered to show only movements in assigned warehouses
     */
    public function getStockMovementStats()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get all movements
        $allMovements = $this->stockMovementModel->getMovementsWithDetails();
        
        // Filter to only movements in assigned warehouses
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $movements = array_filter($allMovements, function($movement) use ($assignedWarehouseIds) {
                $fromWarehouse = $movement['from_warehouse_id'] ?? null;
                $toWarehouse = $movement['to_warehouse_id'] ?? null;
                
                return ($fromWarehouse && in_array($fromWarehouse, $assignedWarehouseIds)) ||
                       ($toWarehouse && in_array($toWarehouse, $assignedWarehouseIds));
            });
            $movements = array_values($movements);
        } else {
            $movements = $allMovements;
        }
        
        // Calculate stats for filtered movements
        $stats = [
            'total' => count($movements),
            'receipts' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Receipt')),
            'issues' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Issue')),
            'transfers' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Transfer')),
            'adjustments' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Adjustment'))
        ];

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
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
     * Stats filtered to show only data from assigned warehouses
     */
    public function reports()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Get filtered inventory stats based on assigned warehouses (same as dashboard)
        $inventoryStats = $this->getInventoryStatsForWarehouses($assignedWarehouseIds);
        $materialStats = $this->materialModel->getMaterialStats();
        
        // Filter movement stats
        $allMovements = $this->stockMovementModel->getMovementsWithDetails();
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $movements = array_filter($allMovements, function($movement) use ($assignedWarehouseIds) {
                $fromWarehouse = $movement['from_warehouse_id'] ?? null;
                $toWarehouse = $movement['to_warehouse_id'] ?? null;
                return ($fromWarehouse && in_array($fromWarehouse, $assignedWarehouseIds)) ||
                       ($toWarehouse && in_array($toWarehouse, $assignedWarehouseIds));
            });
            $movementStats = [
                'total' => count($movements),
                'receipts' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Receipt')),
                'issues' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Issue')),
                'transfers' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Transfer')),
                'adjustments' => count(array_filter($movements, fn($m) => $m['movement_type'] === 'Adjustment'))
            ];
        } else {
            $movementStats = $this->stockMovementModel->getMovementStats();
        }

        $data = [
            'title' => 'Reports - WITMS',
            'user' => $this->getUserData(),
            'inventoryStats' => $inventoryStats,
            'materialStats' => $materialStats,
            'movementStats' => $movementStats,
            'recentReports' => session()->get('recent_reports') ?? []
        ];

        return view('users/warehouse_manager/reports', $data);
    }

    /**
     * Get inventory statistics filtered by warehouse IDs
     * Same logic as Dashboard controller
     * @param array|null $warehouseIds Array of warehouse IDs to filter by, or null for all
     */
    private function getInventoryStatsForWarehouses(?array $warehouseIds): array
    {
        $builder = $this->inventoryModel->db->table('inventory');

        // Filter by warehouse IDs if provided
        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('warehouse_id', $warehouseIds);
        }

        // Count total items
        $totalItems = $builder->countAllResults(false);
        
        // Get total quantity
        $builderQty = $this->inventoryModel->db->table('inventory');
        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builderQty->whereIn('warehouse_id', $warehouseIds);
        }
        $totalQty = $builderQty->selectSum('quantity')->get()->getRow()->quantity ?? 0;

        // Get total value
        $totalValue = $this->getTotalInventoryValueForWarehouses($warehouseIds);

        // Get low stock items
        $lowStockItems = $this->getLowStockItemsForWarehouses($warehouseIds);
        
        // Get expiring items
        $expiringItems = $this->getExpiringItemsForWarehouses($warehouseIds);

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQty,
            'total_value' => $totalValue,
            'low_stock' => count($lowStockItems),
            'low_stock_items' => count($lowStockItems),
            'expiring' => count($expiringItems),
            'expiring_soon' => count($expiringItems),
            'expiring_items' => count($expiringItems)
        ];
    }

    /**
     * Get total inventory value for specific warehouses
     */
    private function getTotalInventoryValueForWarehouses(?array $warehouseIds): float
    {
        $builder = $this->inventoryModel->db->table('inventory')
            ->select('SUM(inventory.available_quantity * materials.unit_cost) as total_value')
            ->join('materials', 'materials.id = inventory.material_id')
            ->where('inventory.available_quantity >', 0);

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('inventory.warehouse_id', $warehouseIds);
        }

        $result = $builder->get()->getRow();
        return (float)($result->total_value ?? 0);
    }

    /**
     * Get low stock items for specific warehouses
     */
    private function getLowStockItemsForWarehouses(?array $warehouseIds): array
    {
        $builder = $this->inventoryModel->db->table('inventory')
            ->select('inventory.*, materials.reorder_level')
            ->join('materials', 'materials.id = inventory.material_id')
            ->where('inventory.available_quantity <=', 'materials.reorder_level', false)
            ->where('materials.reorder_level >', 0);

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('inventory.warehouse_id', $warehouseIds);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get expiring items for specific warehouses
     */
    private function getExpiringItemsForWarehouses(?array $warehouseIds, int $days = 30): array
    {
        $expiryDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $builder = $this->inventoryModel->db->table('inventory')
            ->where('expiration_date IS NOT NULL')
            ->where('expiration_date <=', $expiryDate)
            ->where('expiration_date >=', date('Y-m-d'));

        if ($warehouseIds !== null && !empty($warehouseIds)) {
            $builder->whereIn('warehouse_id', $warehouseIds);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Generate specific report
     * Filtered to show only data from assigned warehouses
     */
    public function generateReport($type)
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();

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
                // Filter inventory by assigned warehouses
                $allInventory = $this->inventoryModel->getInventoryWithDetails();
                if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
                    $data['inventory'] = array_filter($allInventory, function($item) use ($assignedWarehouseIds) {
                        return in_array($item['warehouse_id'], $assignedWarehouseIds);
                    });
                    $data['inventory'] = array_values($data['inventory']);
                } else {
                    $data['inventory'] = $allInventory;
                }
                return view('reports/inventory_summary', $data);

            case 'lowstock':
                $data['title'] = 'Low Stock Report';
                // Use filtered low stock items method
                $lowStockItems = $this->getLowStockItemsForWarehouses($assignedWarehouseIds);
                $data['lowStock'] = $lowStockItems;
                return view('reports/low_stock', $data);

            case 'expiring':
                $data['title'] = 'Expiring Items Report';
                // Use filtered expiring items method
                $expiringItems = $this->getExpiringItemsForWarehouses($assignedWarehouseIds, 30);
                $data['expiring'] = $expiringItems;
                return view('reports/expiring_items', $data);

            case 'movements':
                $data['title'] = 'Stock Movements Report';
                $allMovements = $this->stockMovementModel->getMovementsWithDetails();
                if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
                    $data['movements'] = array_filter($allMovements, function($movement) use ($assignedWarehouseIds) {
                        $fromWarehouse = $movement['from_warehouse_id'] ?? null;
                        $toWarehouse = $movement['to_warehouse_id'] ?? null;
                        return ($fromWarehouse && in_array($fromWarehouse, $assignedWarehouseIds)) ||
                               ($toWarehouse && in_array($toWarehouse, $assignedWarehouseIds));
                    });
                    $data['movements'] = array_values($data['movements']);
                } else {
                    $data['movements'] = $allMovements;
                }
                return view('reports/stock_movements', $data);

            case 'valuation':
                $data['title'] = 'Inventory Valuation Report';
                // Filter valuation by assigned warehouses
                if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
                    // Get valuation for each assigned warehouse and combine
                    $allValuation = [];
                    foreach ($assignedWarehouseIds as $warehouseId) {
                        $warehouseValuation = $this->inventoryModel->getInventoryValuation($warehouseId);
                        $allValuation = array_merge($allValuation, $warehouseValuation);
                    }
                    // Group by material and sum values
                    $grouped = [];
                    foreach ($allValuation as $item) {
                        $key = $item['material_id'];
                        if (!isset($grouped[$key])) {
                            $grouped[$key] = $item;
                        } else {
                            $grouped[$key]['total_quantity'] += $item['total_quantity'];
                            $grouped[$key]['total_value'] += $item['total_value'];
                        }
                    }
                    $data['valuation'] = array_values($grouped);
                } else {
                    $data['valuation'] = $this->inventoryModel->getInventoryValuation();
                }
                return view('reports/inventory_valuation', $data);

            case 'performance':
                $data['title'] = 'Warehouse Performance Report';
                $allMovements = $this->stockMovementModel->getMovementsWithDetails();
                if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
                    $movements = array_filter($allMovements, function($movement) use ($assignedWarehouseIds) {
                        $fromWarehouse = $movement['from_warehouse_id'] ?? null;
                        $toWarehouse = $movement['to_warehouse_id'] ?? null;
                        return ($fromWarehouse && in_array($fromWarehouse, $assignedWarehouseIds)) ||
                               ($toWarehouse && in_array($toWarehouse, $assignedWarehouseIds));
                    });
                    $movements = array_values($movements);
                    $totalMovements = count($movements);
                    $todayMovements = count(array_filter($movements, fn($m) => date('Y-m-d', strtotime($m['movement_date'])) === date('Y-m-d')));
                    $weekMovements = count(array_filter($movements, fn($m) => strtotime($m['movement_date']) >= strtotime('-7 days')));
                    $monthMovements = count(array_filter($movements, fn($m) => strtotime($m['movement_date']) >= strtotime('-30 days')));
                } else {
                    $totalMovements = $this->stockMovementModel->countAll();
                    $todayMovements = $this->stockMovementModel->where('DATE(movement_date)', date('Y-m-d'))->countAllResults();
                    $weekMovements = $this->stockMovementModel->where('movement_date >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
                    $monthMovements = $this->stockMovementModel->where('movement_date >=', date('Y-m-d', strtotime('-30 days')))->countAllResults();
                }
                // Use filtered inventory stats
                $inventoryStats = $this->getInventoryStatsForWarehouses($assignedWarehouseIds);
                $data['stats'] = [
                    'total_movements' => $totalMovements,
                    'today_movements' => $todayMovements,
                    'week_movements' => $weekMovements,
                    'month_movements' => $monthMovements,
                    'inventory_stats' => $inventoryStats,
                    'material_stats' => $this->materialModel->getMaterialStats()
                ];
                return view('reports/warehouse_performance', $data);

            default:
                return redirect()->to('/warehouse-manager/reports')->with('error', 'Invalid report type');
        }
    }

    /**
     * Show analytics page
     * Filtered to show only data from assigned warehouses (except inventory stats - shows all)
     */
    public function analytics()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        // Inventory stats show ALL inventory (as per requirement)
        $inventoryStats = $this->inventoryModel->getInventoryStats();
        
        // Material stats - filter by materials used in assigned warehouses
        $materialStats = $this->materialModel->getMaterialStats();
        
        // Warehouse stats - filter to only assigned warehouses
        $allWarehouses = $this->warehouseModel->getWarehousesWithLocations();
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $warehouses = array_filter($allWarehouses, function($warehouse) use ($assignedWarehouseIds) {
                return in_array($warehouse['id'], $assignedWarehouseIds);
            });
            $warehouses = array_values($warehouses);
            $warehouseStats = [
                'total' => count($warehouses),
                'active' => count(array_filter($warehouses, fn($w) => $w['is_active'])),
                'inactive' => count(array_filter($warehouses, fn($w) => !$w['is_active'])),
                'with_managers' => count(array_filter($warehouses, fn($w) => !empty($w['manager_id'])))
            ];
        } else {
            $warehouseStats = $this->warehouseModel->getWarehouseStats();
        }

        $data = [
            'title' => 'Analytics - WITMS',
            'user' => $this->getUserData(),
            'inventoryStats' => $inventoryStats, // All inventory
            'materialStats' => $materialStats,
            'warehouseStats' => $warehouseStats
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
    /**
     * Staff Management
     * Filtered to show only staff assigned to assigned warehouses
     */
    public function staffManagement()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $assignedWarehouseIds = $this->getAssignedWarehouseIds();
        
        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();
        
        // Get warehouse-related roles (excluding Warehouse Manager)
        $warehouseRoles = [
            'Warehouse Staff', 'Inventory Auditor', 'Procurement Officer'
        ];
        
        $allStaff = [];
        foreach ($warehouseRoles as $roleName) {
            $role = $roleModel->getRoleByName($roleName);
            if ($role) {
                $users = $userModel->getUsersByRole($role['id']);
                foreach ($users as $user) {
                    $user['role_name'] = $roleName;
                    $allStaff[] = $user;
                }
            }
        }
        
        // Filter staff to only those assigned to assigned warehouses
        if ($assignedWarehouseIds !== null && !empty($assignedWarehouseIds)) {
            $staff = [];
            foreach ($allStaff as $user) {
                // Get user's warehouse assignments
                $userAssignments = $this->userWarehouseAssignmentModel->getWarehousesByUser($user['id'], true);
                $userWarehouseIds = array_column($userAssignments, 'warehouse_id');
                
                // Include if user is assigned to any of the manager's assigned warehouses
                if (!empty(array_intersect($userWarehouseIds, $assignedWarehouseIds))) {
                    $staff[] = $user;
                }
            }
        } else {
            // IT Admin or no assignments - show all staff
            $staff = $allStaff;
        }

        $data = [
            'title' => 'Assign Staff to Work - WITMS',
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

        if (!$this->request->isAJAX() && !$this->request->getMethod() === 'POST') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Get data from JSON or POST
        $taskType = $this->request->getJSON(true)['taskType'] ?? $this->request->getPost('taskType');
        $taskDescription = $this->request->getJSON(true)['taskDescription'] ?? $this->request->getPost('taskDescription');
        $taskLocation = $this->request->getJSON(true)['taskLocation'] ?? $this->request->getPost('taskLocation');
        $taskPriority = $this->request->getJSON(true)['taskPriority'] ?? $this->request->getPost('taskPriority');
        $taskDeadline = $this->request->getJSON(true)['taskDeadline'] ?? $this->request->getPost('taskDeadline');

        $data = [
            'user_id' => $userId,
            'task_type' => $taskType,
            'task_description' => $taskDescription,
            'location' => $taskLocation,
            'priority' => $taskPriority,
            'deadline' => $taskDeadline ? date('Y-m-d H:i:s', strtotime($taskDeadline)) : null,
            'assigned_by' => session()->get('user_id'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
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

            if (!$staff) {
                return $this->response->setJSON(['success' => false, 'message' => 'Staff member not found']);
            }

            // Send email notification
            $emailSent = $this->sendWorkAssignmentEmail($staff, $data, $manager);

            log_message('info', "Work assignment created for user {$userId} by manager " . session()->get('user_id') . ". Email sent: " . ($emailSent ? 'Yes' : 'No'));

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Work assignment created successfully' . ($emailSent ? ' and email notification sent to staff member' : ' (email notification failed)'),
                'assignment_id' => $assignmentId,
                'email_sent' => $emailSent
            ]);
        } else {
            $errors = $this->workAssignmentModel->errors();
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Failed to create work assignment: ' . implode(', ', $errors)
            ]);
        }
    }

    /**
     * Send email notification for work assignment
     */
    private function sendWorkAssignmentEmail($staff, $assignment, $manager)
    {
        try {
            $email = \Config\Services::email();
            
            // Use email config from config file
            $emailConfig = config('Email');
            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
            $email->setTo($staff['email']);
            
            // Format task type for display
            $taskTypeDisplay = ucwords(str_replace('_', ' ', $assignment['task_type']));
            
            $email->setSubject('New Work Assignment - ' . $taskTypeDisplay);
            
            // Create HTML email message
            $message = "<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .task-details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #0d6efd; }
        .task-details p { margin: 5px 0; }
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #ffc107; font-weight: bold; }
        .priority-low { color: #28a745; font-weight: bold; }
        .priority-urgent { color: #dc3545; font-weight: bold; background-color: #fff3cd; padding: 5px; border-radius: 3px; }
        .footer { text-align: center; margin-top: 20px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>New Work Assignment</h2>
        </div>
        <div class='content'>
            <p>Dear <strong>{$staff['first_name']} {$staff['last_name']}</strong>,</p>
            <p>You have been assigned a new work task. Please review the details below:</p>
            
            <div class='task-details'>
                <p><strong>Task Type:</strong> {$taskTypeDisplay}</p>
                <p><strong>Description:</strong> " . ($assignment['task_description'] ?? 'No description provided') . "</p>";
            
            if (!empty($assignment['location'])) {
                $message .= "<p><strong>Location:</strong> " . htmlspecialchars($assignment['location']) . "</p>";
            }
            
            $priorityClass = 'priority-' . strtolower($assignment['priority'] ?? 'medium');
            $priorityDisplay = ucfirst($assignment['priority'] ?? 'Medium');
            $message .= "<p><strong>Priority:</strong> <span class='{$priorityClass}'>{$priorityDisplay}</span></p>";
            
            if (!empty($assignment['deadline'])) {
                $deadlineFormatted = date('F j, Y \a\t g:i A', strtotime($assignment['deadline']));
                $message .= "<p><strong>Deadline:</strong> {$deadlineFormatted}</p>";
            }
            
            $managerName = ($manager['first_name'] ?? '') . ' ' . ($manager['last_name'] ?? '');
            $message .= "</div>
            
            <p><strong>Assigned by:</strong> {$managerName}</p>
            
            <p>Please log in to the WITMS system to view full details and update the status of this assignment.</p>
            
            <p>Thank you for your attention to this matter.</p>
        </div>
        <div class='footer'>
            <p>This is an automated message from the WITMS (Warehouse Inventory and Tracking Management System)</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>";
            
            $email->setMessage($message);
            
            // Send email
            if ($email->send()) {
                log_message('info', "Work assignment email sent successfully to {$staff['email']}");
                return true;
            } else {
                $error = $email->printDebugger(['headers']);
                log_message('error', "Email failed to send to {$staff['email']}: {$error}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Email exception for work assignment: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
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
