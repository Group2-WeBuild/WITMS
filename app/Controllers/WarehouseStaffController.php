<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\InventoryModel;
use App\Models\WarehouseModel;
use App\Models\StockMovementModel;
use App\Models\UserModel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

class WarehouseStaffController extends BaseController
{
    protected $materialModel;
    protected $inventoryModel;
    protected $stockMovementModel;
    protected $userModel;
    protected $warehouseModel;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
        $this->inventoryModel = new InventoryModel();
        $this->stockMovementModel = new StockMovementModel();
        $this->userModel = new UserModel();
        $this->warehouseModel = new WarehouseModel();
    }

    /**
     * Check if user has access (Warehouse Staff role)
     */
    protected function checkAccess()
    {
        $isLoggedIn = session()->get('isLoggedIn');
        $userRole = session()->get('user_role');
        $userId = session()->get('user_id');
        
        // Debug logging - temporarily disabled
        // log_message('debug', 'WarehouseStaff checkAccess - Logged in: ' . ($isLoggedIn ? 'true' : 'false'));
        // log_message('debug', 'WarehouseStaff checkAccess - User Role: ' . ($userRole ?? 'NULL'));
        // log_message('debug', 'WarehouseStaff checkAccess - User ID: ' . ($userId ?? 'NULL'));
        
        // More flexible role checking
        $allowedRoles = ['Warehouse Staff', 'warehouse staff', 'Warehouse Manager', 'warehouse manager'];
        
        if (!$isLoggedIn || !in_array($userRole, $allowedRoles)) {
            log_message('error', 'WarehouseStaff access denied - Role mismatch or not logged in');
            log_message('error', 'Expected one of: ' . implode(', ', $allowedRoles) . ', Got: "' . $userRole . '"');
            // Try with trimmed and lowercase role
            if ($userRole && in_array(trim(strtolower($userRole)), array_map('strtolower', $allowedRoles))) {
                log_message('debug', 'Role matches after trimming/lowercase');
                return null;
            }
            return redirect()->to('/login')->with('error', 'Please login as Warehouse Staff');
        }
        return null;
    }

    /**
     * Get user data
     */
    protected function getUserData()
    {
        $userId = session()->get('user_id');
        
        // Debug: Log what's in session
        log_message('debug', 'Session data for getUserData: ' . json_encode([
            'user_id' => $userId,
            'full_name' => session()->get('full_name'),
            'first_name' => session()->get('first_name'),
            'last_name' => session()->get('last_name'),
            'user_role' => session()->get('user_role')
        ]));
        
        // If no user data from database, create from session
        $userData = $this->userModel->find($userId);
        
        // Always ensure we have full_name from session if database doesn't have it
        if ($userData && !isset($userData['full_name'])) {
            $userData['full_name'] = session()->get('full_name') ?? 'User';
        }
        
        if (!$userData) {
            // Fallback to session data if database query fails
            $userData = [
                'id' => $userId,
                'full_name' => session()->get('full_name') ?? 'User',
                'first_name' => session()->get('first_name') ?? '',
                'last_name' => session()->get('last_name') ?? '',
                'email' => session()->get('user_email') ?? ''
            ];
        }
        
        // Add role from session to user data for sidebar navigation
        $userData['role'] = session()->get('user_role');
        
        return $userData;
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userId = session()->get('user_id');
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        $db = \Config\Database::connect();
        
        // Total inventory items count
        $totalItems = $db->table('inventory')
            ->select('COUNT(DISTINCT material_id) as count')
            ->get()->getRow()->count ?? 0;
        
        // Total stock quantity across all warehouses
        $totalStock = $db->table('inventory')
            ->selectSum('quantity')
            ->get()->getRow()->quantity ?? 0;
        
        // Today's movements by type
        $todaysReceipts = $this->stockMovementModel
            ->where('movement_type', 'Receipt')
            ->where('DATE(created_at)', $today)
            ->countAllResults();
            
        $todaysIssues = $this->stockMovementModel
            ->where('movement_type', 'Issue')
            ->where('DATE(created_at)', $today)
            ->countAllResults();
            
        $todaysTransfers = $this->stockMovementModel
            ->where('movement_type', 'Transfer')
            ->where('DATE(created_at)', $today)
            ->countAllResults();
        
        // This month's movements
        $monthlyReceipts = $this->stockMovementModel
            ->where('movement_type', 'Receipt')
            ->like('created_at', $thisMonth, 'after')
            ->countAllResults();
            
        $monthlyIssues = $this->stockMovementModel
            ->where('movement_type', 'Issue')
            ->like('created_at', $thisMonth, 'after')
            ->countAllResults();
            
        $monthlyTransfers = $this->stockMovementModel
            ->where('movement_type', 'Transfer')
            ->like('created_at', $thisMonth, 'after')
            ->countAllResults();
        
        // My activities today
        $myTodayActivities = $this->stockMovementModel
            ->where('performed_by', $userId)
            ->where('DATE(created_at)', $today)
            ->countAllResults();
        
        // Low stock items count - use fresh model instance to avoid query builder conflicts
        $freshInventoryModel = new \App\Models\InventoryModel();
        $lowStockItems = $freshInventoryModel->getLowStockItems();
        $lowStockCount = count($lowStockItems);
        
        // Get recent activities (last 10)
        $recentActivities = $this->stockMovementModel
            ->select('stock_movements.*, materials.name as material_name, materials.code as material_code, 
                      w1.name as from_warehouse_name, w2.name as to_warehouse_name')
            ->join('materials', 'materials.id = stock_movements.material_id')
            ->join('warehouses w1', 'w1.id = stock_movements.from_warehouse_id', 'left')
            ->join('warehouses w2', 'w2.id = stock_movements.to_warehouse_id', 'left')
            ->orderBy('stock_movements.created_at', 'DESC')
            ->limit(10)
            ->findAll();
        
        // Get warehouses for quick reference
        $warehouses = $this->warehouseModel->where('is_active', 1)->findAll();

        $data = [
            'title' => 'Dashboard - WITMS',
            'user' => $this->getUserData(),
            'totalItems' => $totalItems,
            'totalStock' => $totalStock,
            'todaysReceipts' => $todaysReceipts,
            'todaysIssues' => $todaysIssues,
            'todaysTransfers' => $todaysTransfers,
            'monthlyReceipts' => $monthlyReceipts,
            'monthlyIssues' => $monthlyIssues,
            'monthlyTransfers' => $monthlyTransfers,
            'myTodayActivities' => $myTodayActivities,
            'lowStockCount' => $lowStockCount,
            'lowStockItems' => array_slice($lowStockItems, 0, 5),
            'recentActivities' => $recentActivities,
            'warehouses' => $warehouses
        ];

        return view('users/warehouse_staff/dashboard', $data);
    }

    /**
     * Scan Items page
     */
    public function scanItem()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Scan Items - WITMS',
            'user' => $this->getUserData()
        ];

        return view('users/warehouse_staff/scan_item', $data);
    }

    /**
     * Store scanned items in session
     */
    public function storeScannedItems()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON();
        
        if (!$data || !isset($data->items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No items provided']);
        }

        // Store items in session
        session()->set('scanned_items', $data->items);
        session()->set('scan_action', $data->action);

        // Redirect based on action
        $redirectUrl = '';
        switch ($data->action) {
            case 'issue':
                $redirectUrl = base_url('warehouse-staff/issue-stock');
                break;
            case 'transfer':
                $redirectUrl = base_url('warehouse-staff/transfer-stock');
                break;
            case 'adjust':
                $redirectUrl = base_url('warehouse-staff/adjust-stock');
                break;
            default:
                $redirectUrl = base_url('warehouse-staff/scan-item');
        }

        return $this->response->setJSON([
            'success' => true,
            'redirect' => $redirectUrl
        ]);
    }

    /**
     * QR Scanner page
     */
    public function qrScanner()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'QR Scanner - WITMS',
            'user' => $this->getUserData()
        ];

        return view('users/warehouse_staff/qr_scanner', $data);
    }

    /**
     * Handle QR code scan data via AJAX
     */
    public function qrScanData()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $scanData = $this->request->getJSON();
        
        if (!$scanData) {
            return $this->response->setJSON(['success' => false, 'message' => 'No scan data provided']);
        }

        try {
            if ($scanData->type === 'material' || $scanData->type === 'material_code') {
                // Handle material scan
                $materialCode = $scanData->code ?? $scanData->material_code;
                
                $material = $this->materialModel->where('code', $materialCode)->first();
                
                if (!$material) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
                }

                // Get inventory for this material
                $inventory = $this->inventoryModel->getInventoryByMaterial($material['id']);

                return $this->response->setJSON([
                    'success' => true,
                    'type' => 'material',
                    'material' => $material,
                    'inventory' => $inventory
                ]);

            } elseif ($scanData->type === 'inventory') {
                // Handle inventory scan
                $inventoryId = $scanData->id;
                
                $inventory = $this->inventoryModel->getInventoryWithDetails($inventoryId);
                
                if (!$inventory) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Inventory not found']);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'type' => 'inventory',
                    'inventory' => $inventory,
                    'material' => [
                        'id' => $inventory['material_id'],
                        'name' => $inventory['material_name'],
                        'code' => $inventory['material_code']
                    ]
                ]);

            } elseif ($scanData->type === 'warehouse') {
                // Handle warehouse scan
                $warehouseCode = $scanData->code;
                
                $warehouse = $this->warehouseModel->where('code', $warehouseCode)->first();
                
                if (!$warehouse) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Warehouse not found']);
                }

                // Get inventory in this warehouse
                $inventory = $this->inventoryModel->getInventoryByWarehouse($warehouse['id']);

                return $this->response->setJSON([
                    'success' => true,
                    'type' => 'warehouse',
                    'warehouse' => $warehouse,
                    'inventory' => $inventory
                ]);

            } else {
                // Try to find by material code as fallback
                $material = $this->materialModel->where('code', $scanData->code ?? $scanData)->first();
                
                if ($material) {
                    $inventory = $this->inventoryModel->getInventoryByMaterial($material['id']);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'type' => 'material',
                        'material' => $material,
                        'inventory' => $inventory
                    ]);
                }

                return $this->response->setJSON(['success' => false, 'message' => 'Unknown QR code format']);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error processing scan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Read QR code from uploaded image
     */
    public function readQRUpload()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $file = $this->request->getFile('qr_image');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'No valid file uploaded']);
        }

        // Check file type
        if (!in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid image format']);
        }

        try {
            // Configure QR reader options
            $options = new QROptions([
                'readerUseImagickIfAvailable' => true,
                'readerIncreaseContrast' => true,
                'readerGrayscale' => true,
                'readerInvertColors' => false,
            ]);

            $qrCode = new QRCode($options);
            
            // Read QR code from uploaded file
            $result = $qrCode->readFromFile($file->getTempName());
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'qr_data' => $result
                ]);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'No QR code found in image']);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error reading QR code: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Stock Movements
     */
    public function stockMovements()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Stock Movements - WITMS',
            'user' => $this->getUserData()
        ];

        return view('users/warehouse_staff/stock_movements', $data);
    }

    /**
     * Receive Stock
     */
    public function receiveStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Receive Stock - WITMS',
            'user' => $this->getUserData(),
            'materials' => $this->materialModel->findAll(),
            'warehouses' => $this->warehouseModel->where('is_active', 1)->findAll()
        ];

        return view('users/warehouse_staff/receive_stock', $data);
    }

    /**
     * Process Receive Stock
     */
    public function processReceiveStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $validation = \Config\Services::validation();
        $validation->setRules([
            'material_id' => 'required|integer',
            'quantity' => 'required|numeric|greater_than[0]',
            'warehouse_id' => 'required|integer',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $data = [
            'material_id' => $this->request->getPost('material_id'),
            'to_warehouse_id' => $this->request->getPost('warehouse_id'),
            'from_warehouse_id' => null,
            'quantity' => $this->request->getPost('quantity'),
            'batch_number' => $this->request->getPost('batch_number'),
            'notes' => $this->request->getPost('notes'),
            'performed_by' => session()->get('user_id'),
            'movement_date' => date('Y-m-d H:i:s')
        ];

        // Add reference info if provided
        $reference = $this->request->getPost('reference');
        if ($reference) {
            $data['reference_type'] = 'purchase_order';
            $data['notes'] = ($data['notes'] ? $data['notes'] . "\n" : '') . 'PO/Reference: ' . $reference;
        }

        // Use model's recordReceipt method (handles movement_type and inventory update via callback)
        $movementId = $this->stockMovementModel->recordReceipt($data);
        
        if ($movementId) {
            return redirect()->to('warehouse-staff/dashboard')->with('success', 'Stock received successfully! Reference: SM-REC-' . date('Ymd'));
        }

        return redirect()->back()->withInput()->with('error', 'Failed to receive stock. Please check all fields.');
    }

    /**
     * Issue Stock
     */
    public function issueStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $departmentModel = new \App\Models\DepartmentModel();

        $data = [
            'title' => 'Issue Stock - WITMS',
            'user' => $this->getUserData(),
            'materials' => $this->materialModel->findAll(),
            'warehouses' => $this->warehouseModel->where('is_active', 1)->findAll(),
            'departments' => $departmentModel->getActiveDepartments()
        ];

        return view('users/warehouse_staff/issue_stock', $data);
    }

    /**
     * Process Issue Stock
     */
    public function processIssueStock()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $validation = \Config\Services::validation();
        $validation->setRules([
            'material_id' => 'required|integer',
            'quantity' => 'required|numeric|greater_than[0]',
            'warehouse_id' => 'required|integer',
            'issued_to' => 'required|string'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $issuedTo = $this->request->getPost('issued_to');
        $reference = $this->request->getPost('reference');
        $notes = $this->request->getPost('notes');
        
        // Build comprehensive notes
        $fullNotes = 'Issued to: ' . $issuedTo;
        if ($reference) {
            $fullNotes .= "\nRequisition #: " . $reference;
        }
        if ($notes) {
            $fullNotes .= "\n" . $notes;
        }

        $data = [
            'material_id' => $this->request->getPost('material_id'),
            'from_warehouse_id' => $this->request->getPost('warehouse_id'),
            'to_warehouse_id' => null,
            'quantity' => $this->request->getPost('quantity'),
            'notes' => $fullNotes,
            'performed_by' => session()->get('user_id'),
            'movement_date' => date('Y-m-d H:i:s')
        ];

        // Use model's recordIssue method (handles movement_type and inventory update via callback)
        $movementId = $this->stockMovementModel->recordIssue($data);
        
        if ($movementId) {
            return redirect()->to('warehouse-staff/dashboard')->with('success', 'Stock issued successfully to ' . $issuedTo);
        }

        return redirect()->back()->withInput()->with('error', 'Failed to issue stock. Please check available quantity.');
    }

    /**
     * Stock Transfer
     */
    public function stockTransfer()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $data = [
            'title' => 'Stock Transfer - WITMS',
            'user' => $this->getUserData(),
            'materials' => $this->materialModel->findAll(),
            'warehouses' => $this->warehouseModel->where('is_active', 1)->findAll()
        ];

        return view('users/warehouse_staff/stock_transfer', $data);
    }

    /**
     * Process Stock Transfer
     */
    public function processStockTransfer()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $validation = \Config\Services::validation();
        $validation->setRules([
            'material_id' => 'required|integer',
            'quantity' => 'required|numeric|greater_than[0]',
            'from_warehouse_id' => 'required|integer',
            'to_warehouse_id' => 'required|integer|differs[from_warehouse_id]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $reference = $this->request->getPost('reference');
        $notes = $this->request->getPost('notes');
        
        // Build notes with reference if provided
        $fullNotes = $notes ?? '';
        if ($reference) {
            $fullNotes = 'Transfer Ref: ' . $reference . ($fullNotes ? "\n" . $fullNotes : '');
        }

        $data = [
            'material_id' => $this->request->getPost('material_id'),
            'from_warehouse_id' => $this->request->getPost('from_warehouse_id'),
            'to_warehouse_id' => $this->request->getPost('to_warehouse_id'),
            'quantity' => $this->request->getPost('quantity'),
            'notes' => $fullNotes,
            'performed_by' => session()->get('user_id'),
            'movement_date' => date('Y-m-d H:i:s')
        ];

        // Use model's recordTransfer method (handles movement_type and inventory update via callback)
        $movementId = $this->stockMovementModel->recordTransfer($data);
        
        if ($movementId) {
            return redirect()->to('warehouse-staff/dashboard')->with('success', 'Stock transferred successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to transfer stock. Please check available quantity.');
    }

    /**
     * Activity Log
     */
    public function activity()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        $userId = session()->get('user_id');
        
        // Get filter parameters
        $type = $this->request->getGet('type');
        $date = $this->request->getGet('date');

        // Build query
        $builder = $this->stockMovementModel->builder();
        $builder->select('stock_movements.*, materials.name as material_name, materials.code as material_code, units_of_measure.abbreviation as unit')
                ->join('materials', 'materials.id = stock_movements.material_id')
                ->join('units_of_measure', 'units_of_measure.id = materials.unit_id', 'left')
                ->where('stock_movements.performed_by', $userId)
                ->orderBy('stock_movements.created_at', 'DESC');

        if ($type) {
            $builder->where('stock_movements.movement_type', $type);
        }

        if ($date) {
            $builder->where('DATE(stock_movements.created_at)', $date);
        }

        $activities = $builder->get()->getResultArray();

        $data = [
            'title' => 'My Activity - WITMS',
            'user' => $this->getUserData(),
            'activities' => $activities
        ];

        return view('users/warehouse_staff/activity', $data);
    }

    /**
     * Search Inventory page
     */
    public function searchInventory()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if ($this->request->isAJAX()) {
            // Handle AJAX search request
            $search = $this->request->getJSON()->search ?? '';
            $warehouseId = $this->request->getJSON()->warehouse_id ?? '';
            $categoryId = $this->request->getJSON()->category_id ?? '';

            $builder = $this->inventoryModel->builder();
            $builder->select('inventory.*, materials.name, materials.code, materials.reorder_level, 
                            warehouses.name as warehouse_name,
                            units_of_measure.abbreviation as unit, inventory.location_in_warehouse')
                    ->join('materials', 'materials.id = inventory.material_id')
                    ->join('warehouses', 'warehouses.id = inventory.warehouse_id', 'left')
                    ->join('units_of_measure', 'units_of_measure.id = materials.unit_id', 'left');

            if ($search) {
                $builder->groupStart()
                        ->like('materials.code', $search)
                        ->orLike('materials.name', $search)
                        ->groupEnd();
            }

            if ($warehouseId) {
                $builder->where('inventory.warehouse_id', $warehouseId);
            }

            $results = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'results' => $results
            ]);
        } else {
            // Load the search page with dropdown data
            $data = [
                'title' => 'Search Inventory - WITMS',
                'user' => $this->getUserData(),
                'warehouses' => $this->warehouseModel->where('is_active', 1)->findAll()
            ];

            // Load categories if CategoryModel exists
            if (class_exists('App\Models\CategoryModel')) {
                $categoryModel = new \App\Models\CategoryModel();
                $data['categories'] = $categoryModel->where('is_active', 1)->findAll();
            } else {
                $data['categories'] = [];
            }

            return view('users/warehouse_staff/search_inventory', $data);
        }
    }

    /**
     * AJAX: Get dashboard stats for real-time updates
     */
    public function getDashboardStats()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $userId = session()->get('user_id');
            $today = date('Y-m-d');
            $thisMonth = date('Y-m');
            
            $db = \Config\Database::connect();
            
            // Total inventory items count
            $totalItems = $db->table('inventory')
                ->select('COUNT(DISTINCT material_id) as count')
                ->get()->getRow()->count ?? 0;
            
            // Total stock quantity
            $totalStock = $db->table('inventory')
                ->selectSum('quantity')
                ->get()->getRow()->quantity ?? 0;
            
            // Today's movements
            $todaysReceipts = $this->stockMovementModel
                ->where('movement_type', 'Receipt')
                ->where('DATE(created_at)', $today)
                ->countAllResults();
                
            $todaysIssues = $this->stockMovementModel
                ->where('movement_type', 'Issue')
                ->where('DATE(created_at)', $today)
                ->countAllResults();
                
            $todaysTransfers = $this->stockMovementModel
                ->where('movement_type', 'Transfer')
                ->where('DATE(created_at)', $today)
                ->countAllResults();
            
            // Monthly movements
            $monthlyReceipts = $this->stockMovementModel
                ->where('movement_type', 'Receipt')
                ->like('created_at', $thisMonth, 'after')
                ->countAllResults();
                
            $monthlyIssues = $this->stockMovementModel
                ->where('movement_type', 'Issue')
                ->like('created_at', $thisMonth, 'after')
                ->countAllResults();
                
            $monthlyTransfers = $this->stockMovementModel
                ->where('movement_type', 'Transfer')
                ->like('created_at', $thisMonth, 'after')
                ->countAllResults();
            
            // My activities today
            $myTodayActivities = $this->stockMovementModel
                ->where('performed_by', $userId)
                ->where('DATE(created_at)', $today)
                ->countAllResults();
            
            // Low stock count - use fresh model instance to avoid query builder conflicts
            $freshInventoryModel = new \App\Models\InventoryModel();
            $lowStockItems = $freshInventoryModel->getLowStockItems();
            $lowStockCount = count($lowStockItems);

            return $this->response->setJSON([
                'success' => true,
                'stats' => [
                    'totalItems' => (int)$totalItems,
                    'totalStock' => number_format((float)$totalStock, 0),
                    'lowStockCount' => $lowStockCount,
                    'myTodayActivities' => $myTodayActivities,
                    'todaysReceipts' => $todaysReceipts,
                    'todaysIssues' => $todaysIssues,
                    'todaysTransfers' => $todaysTransfers,
                    'monthlyReceipts' => $monthlyReceipts,
                    'monthlyIssues' => $monthlyIssues,
                    'monthlyTransfers' => $monthlyTransfers
                ],
                'timestamp' => date('H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getDashboardStats error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching stats'
            ]);
        }
    }

    /**
     * AJAX: Get materials with quantities for a specific warehouse
     */
    public function getMaterialsByWarehouse()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $warehouseId = $this->request->getGet('warehouse_id');
        
        if (!$warehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse ID required']);
        }

        try {
            // Get all materials with their quantities in the specified warehouse
            $db = \Config\Database::connect();
            $builder = $db->table('materials');
            $builder->select('
                materials.id,
                materials.name,
                materials.code,
                units_of_measure.abbreviation as unit,
                COALESCE(inventory.quantity, 0) as total_qty,
                COALESCE(inventory.available_quantity, 0) as available_qty
            ');
            $builder->join('units_of_measure', 'units_of_measure.id = materials.unit_id', 'left');
            $builder->join('inventory', 'inventory.material_id = materials.id AND inventory.warehouse_id = ' . (int)$warehouseId, 'left');
            $builder->where('materials.is_active', 1);
            $builder->orderBy('materials.name', 'ASC');
            
            $materials = $builder->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'materials' => $materials
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getMaterialsByWarehouse error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
