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
            // Handle case where code might contain JSON (from multiple QR codes concatenated)
            // This happens when the scanner reads multiple QR codes at once
            if (isset($scanData->code) && is_string($scanData->code) && strpos($scanData->code, '{') !== false) {
                $codeStr = $scanData->code;
                
                // Check if it contains multiple JSON objects (indicated by '}{')
                if (strpos($codeStr, '}{') !== false) {
                    // Extract the first JSON object
                    $firstJsonEnd = strpos($codeStr, '}');
                    if ($firstJsonEnd !== false) {
                        $firstJson = substr($codeStr, 0, $firstJsonEnd + 1);
                        try {
                            $parsedCode = json_decode($firstJson, true);
                            if ($parsedCode && is_array($parsedCode)) {
                                // Replace scanData with parsed data
                                foreach ($parsedCode as $key => $value) {
                                    $scanData->$key = $value;
                                }
                                log_message('debug', 'Extracted first QR code from concatenated string');
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'Failed to parse extracted JSON: ' . $e->getMessage());
                        }
                    }
                } else {
                    // Try to parse as single JSON object
                    try {
                        $parsedCode = json_decode($codeStr, true);
                        if ($parsedCode && is_array($parsedCode)) {
                            // Merge parsed data into scanData
                            foreach ($parsedCode as $key => $value) {
                                if (!isset($scanData->$key)) {
                                    $scanData->$key = $value;
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Not valid JSON, keep as code
                    }
                }
            }
            
            // Support both old format (full field names) and new compact format (single letters)
            $type = $scanData->type ?? $scanData->t ?? null;
            $id = $scanData->id ?? $scanData->i ?? null;
            $code = $scanData->code ?? $scanData->c ?? null;
            
            // Ensure ID is an integer if it exists
            if ($id !== null) {
                $id = (int)$id;
            }
            
            // Log the parsed data for debugging
            log_message('debug', 'QR Scan - Type: ' . ($type ?? 'null') . ', ID: ' . ($id ?? 'null') . ', Code: ' . ($code ?? 'null'));
            
            if ($type === 'material' || $type === 'm' || $type === 'material_code') {
                // Handle material scan
                $materialCode = $code ?? $scanData->material_code ?? null;
                $material = null;
                
                // Try to find by code first if code is provided
                if ($materialCode) {
                    // Get material with details by code using a direct query
                    $material = $this->materialModel->select('
                        materials.*,
                        material_categories.name as category_name,
                        material_categories.code as category_code,
                        units_of_measure.name as unit_name,
                        units_of_measure.abbreviation as unit_abbreviation
                    ')
                    ->join('material_categories', 'material_categories.id = materials.category_id')
                    ->join('units_of_measure', 'units_of_measure.id = materials.unit_id')
                    ->where('materials.code', $materialCode)
                    ->first();
                }
                
                // If not found by code, try by ID
                if (!$material && $id) {
                    $material = $this->materialModel->getMaterialsWithDetails($id);
                }
                
                // If still not found, log for debugging
                if (!$material) {
                    log_message('error', 'Material not found - Code: ' . ($materialCode ?? 'null') . ', ID: ' . ($id ?? 'null'));
                    log_message('error', 'Scan data: ' . json_encode($scanData));
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => 'Material not found',
                        'debug' => [
                            'code' => $materialCode,
                            'id' => $id,
                            'type' => $type
                        ]
                    ]);
                }

                // Get inventory for this material
                $inventory = $this->inventoryModel->getInventoryByMaterial($material['id']);

                return $this->response->setJSON([
                    'success' => true,
                    'type' => 'material',
                    'material' => $material,
                    'inventory' => $inventory
                ]);

            } elseif ($type === 'inventory' || $type === 'i') {
                // Handle inventory scan
                $inventoryId = $id;
                
                if (!$inventoryId) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Inventory ID not found in QR code']);
                }
                
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

            } elseif ($type === 'warehouse' || $type === 'w') {
                // Handle warehouse scan
                $warehouseCode = $code;
                $warehouseId = $id;
                
                if ($warehouseId) {
                    $warehouse = $this->warehouseModel->find($warehouseId);
                } elseif ($warehouseCode) {
                    $warehouse = $this->warehouseModel->where('code', $warehouseCode)->first();
                } else {
                    return $this->response->setJSON(['success' => false, 'message' => 'Warehouse code or ID not found']);
                }
                
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

            } elseif ($type === 'movement' || $type === 'mv') {
                // Handle movement scan
                $movementId = $id;
                
                if (!$movementId) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Movement ID not found']);
                }
                
                $movements = $this->stockMovementModel->getMovementsWithDetails($movementId);
                $movement = is_array($movements) && isset($movements[0]) ? $movements[0] : $movements;
                
                if (!$movement) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Movement not found']);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'type' => 'movement',
                    'movement' => $movement
                ]);

            } else {
                // Try to find by material code as fallback
                $materialCode = $code ?? $scanData->code ?? null;
                
                if ($materialCode) {
                    $allMaterials = $this->materialModel->getMaterialsWithDetails();
                    $material = null;
                    foreach ($allMaterials as $m) {
                        if ($m['code'] === $materialCode) {
                            $material = $m;
                            break;
                        }
                    }
                    
                    if ($material) {
                        $inventory = $this->inventoryModel->getInventoryByMaterial($material['id']);
                        
                        return $this->response->setJSON([
                            'success' => true,
                            'type' => 'material',
                            'material' => $material,
                            'inventory' => $inventory
                        ]);
                    }
                }
                
                // Try by ID if provided
                if ($id) {
                    // Try inventory first
                    $inventory = $this->inventoryModel->getInventoryWithDetails($id);
                    if ($inventory) {
                        return $this->response->setJSON([
                            'success' => true,
                            'type' => 'inventory',
                            'inventory' => $inventory
                        ]);
                    }
                    
                    // Try material with details
                    $material = $this->materialModel->getMaterialsWithDetails($id);
                    if ($material) {
                        $inventory = $this->inventoryModel->getInventoryByMaterial($material['id']);
                        return $this->response->setJSON([
                            'success' => true,
                            'type' => 'material',
                            'material' => $material,
                            'inventory' => $inventory
                        ]);
                    }
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

    /**
     * AJAX: Get warehouses list
     */
    public function getWarehouses()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $warehouses = $this->warehouseModel->where('is_active', 1)->findAll();
            
            return $this->response->setJSON([
                'success' => true,
                'warehouses' => $warehouses
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getWarehouses error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading warehouses'
            ]);
        }
    }

    /**
     * AJAX: Get departments list
     */
    public function getDepartments()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        // Allow both AJAX and regular GET requests
        if (!$this->request->isAJAX() && !$this->request->is('get')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $departmentModel = new \App\Models\DepartmentModel();
            $departments = $departmentModel->getActiveDepartments();
            
            log_message('debug', 'Departments loaded: ' . count($departments));
            
            return $this->response->setJSON([
                'success' => true,
                'departments' => $departments,
                'count' => count($departments)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getDepartments error: ' . $e->getMessage());
            log_message('error', 'getDepartments stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading departments: ' . $e->getMessage(),
                'departments' => []
            ]);
        }
    }

    /**
     * AJAX: Generate requisition number
     */
    public function generateRequisitionNumber()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $requisitionNumber = $this->generateRequisitionNumberSequence();
            
            return $this->response->setJSON([
                'success' => true,
                'requisition_number' => $requisitionNumber
            ]);
        } catch (\Exception $e) {
            log_message('error', 'generateRequisitionNumber error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error generating requisition number'
            ]);
        }
    }

    /**
     * Generate requisition number sequence
     * Format: REQ-YYYY-MMDD-NNNN (e.g., REQ-2024-1216-0001)
     */
    private function generateRequisitionNumberSequence()
    {
        $year = date('Y');
        $monthDay = date('md');
        $prefix = "REQ-{$year}-{$monthDay}-";
        
        // Find the last requisition number for today
        $db = \Config\Database::connect();
        $builder = $db->table('stock_movements');
        
        // Search in notes field for requisition numbers
        $today = date('Y-m-d');
        $movements = $builder->select('notes')
            ->where('movement_type', 'Issue')
            ->where('DATE(created_at)', $today)
            ->like('notes', 'Requisition #:')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
        
        $nextNumber = 1;
        $pattern = '/Requisition\s*#:\s*' . preg_quote($prefix, '/') . '(\d+)/i';
        
        foreach ($movements as $movement) {
            if (preg_match($pattern, $movement['notes'], $matches)) {
                $lastNumber = (int)$matches[1];
                if ($lastNumber >= $nextNumber) {
                    $nextNumber = $lastNumber + 1;
                }
            }
        }
        
        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Process Issue Stock from Scanned Items
     */
    public function processIssueFromScanned()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        
        if (!$data || !isset($data['items']) || empty($data['items'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No items provided']);
        }

        $warehouseId = $data['warehouse_id'] ?? null;
        $issuedTo = $data['issued_to'] ?? '';
        $reference = $data['reference'] ?? '';
        $notes = $data['notes'] ?? '';

        if (!$warehouseId || !$issuedTo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse and Issued To are required']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($data['items'] as $item) {
                $materialId = $item['material_id'] ?? null;
                $quantity = floatval($item['quantity'] ?? 0);

                if (!$materialId || $quantity <= 0) {
                    $failCount++;
                    $errors[] = "Invalid item data for material ID: " . ($materialId ?? 'unknown');
                    continue;
                }

                // Build notes
                $fullNotes = 'Issued to: ' . $issuedTo;
                if ($reference) {
                    $fullNotes .= "\nRequisition #: " . $reference;
                }
                if ($notes) {
                    $fullNotes .= "\n" . $notes;
                }

                $movementData = [
                    'material_id' => $materialId,
                    'from_warehouse_id' => $warehouseId,
                    'to_warehouse_id' => null,
                    'quantity' => $quantity,
                    'notes' => $fullNotes,
                    'performed_by' => session()->get('user_id'),
                    'movement_date' => date('Y-m-d H:i:s')
                ];

                $movementId = $this->stockMovementModel->recordIssue($movementData);
                
                if ($movementId) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Failed to issue material ID: " . $materialId;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed. Please try again.'
                ]);
            }

            if ($successCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Successfully issued {$successCount} item(s)" . ($failCount > 0 ? ". {$failCount} failed." : ''),
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to issue any items',
                    'errors' => $errors
                ]);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'processIssueFromScanned error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error processing issue: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process Transfer Stock from Scanned Items
     */
    public function processTransferFromScanned()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        
        if (!$data || !isset($data['items']) || empty($data['items'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No items provided']);
        }

        $fromWarehouseId = $data['from_warehouse_id'] ?? null;
        $toWarehouseId = $data['to_warehouse_id'] ?? null;
        $reference = $data['reference'] ?? '';
        $notes = $data['notes'] ?? '';

        if (!$fromWarehouseId || !$toWarehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Both warehouses are required']);
        }

        if ($fromWarehouseId == $toWarehouseId) {
            return $this->response->setJSON(['success' => false, 'message' => 'From and To warehouses must be different']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($data['items'] as $item) {
                $materialId = $item['material_id'] ?? null;
                $quantity = floatval($item['quantity'] ?? 0);

                if (!$materialId || $quantity <= 0) {
                    $failCount++;
                    $errors[] = "Invalid item data for material ID: " . ($materialId ?? 'unknown');
                    continue;
                }

                // Build notes
                $fullNotes = $notes ?? '';
                if ($reference) {
                    $fullNotes = 'Transfer Ref: ' . $reference . ($fullNotes ? "\n" . $fullNotes : '');
                }

                $movementData = [
                    'material_id' => $materialId,
                    'from_warehouse_id' => $fromWarehouseId,
                    'to_warehouse_id' => $toWarehouseId,
                    'quantity' => $quantity,
                    'notes' => $fullNotes,
                    'performed_by' => session()->get('user_id'),
                    'movement_date' => date('Y-m-d H:i:s')
                ];

                $movementId = $this->stockMovementModel->recordTransfer($movementData);
                
                if ($movementId) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Failed to transfer material ID: " . $materialId;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed. Please try again.'
                ]);
            }

            if ($successCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Successfully transferred {$successCount} item(s)" . ($failCount > 0 ? ". {$failCount} failed." : ''),
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to transfer any items',
                    'errors' => $errors
                ]);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'processTransferFromScanned error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error processing transfer: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process Adjust Stock from Scanned Items
     */
    public function processAdjustFromScanned()
    {
        $accessCheck = $this->checkAccess();
        if ($accessCheck) return $accessCheck;

        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getJSON(true);
        
        if (!$data || !isset($data['adjustments']) || empty($data['adjustments'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No adjustments provided']);
        }

        $warehouseId = $data['warehouse_id'] ?? null;
        $reason = $data['reason'] ?? '';
        $notes = $data['notes'] ?? '';

        if (!$warehouseId || !$reason) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse and reason are required']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($data['adjustments'] as $adjustment) {
                $materialId = $adjustment['material_id'] ?? null;
                $currentQty = floatval($adjustment['current_quantity'] ?? 0);
                $newQty = floatval($adjustment['new_quantity'] ?? 0);
                $adjustmentType = $adjustment['adjustment_type'] ?? 'increase';
                $adjustmentQty = floatval($adjustment['adjustment_quantity'] ?? 0);

                if (!$materialId || $newQty < 0) {
                    $failCount++;
                    $errors[] = "Invalid adjustment data for material ID: " . ($materialId ?? 'unknown');
                    continue;
                }

                // Get inventory record
                $inventory = $this->inventoryModel
                    ->where('material_id', $materialId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if (!$inventory) {
                    // Create new inventory record if it doesn't exist
                    $inventoryId = $this->inventoryModel->insert([
                        'material_id' => $materialId,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $newQty,
                        'available_quantity' => $newQty,
                        'reserved_quantity' => 0
                    ]);
                    $inventory = $this->inventoryModel->find($inventoryId);
                } else {
                    // Update existing inventory
                    $this->inventoryModel->update($inventory['id'], [
                        'quantity' => $newQty,
                        'available_quantity' => $newQty - ($inventory['reserved_quantity'] ?? 0)
                    ]);
                }

                // Create stock movement record
                $stockMovementModel = new \App\Models\StockMovementModel();
                $referenceNumber = 'ADJ-' . date('Y') . '-' . str_pad($stockMovementModel->countAll() + 1, 6, '0', STR_PAD_LEFT);
                
                $fullNotes = "Adjustment: {$reason}";
                if ($notes) {
                    $fullNotes .= ". {$notes}";
                }
                $fullNotes .= " (From {$currentQty} to {$newQty})";

                $movementData = [
                    'reference_number' => $referenceNumber,
                    'material_id' => $materialId,
                    'from_warehouse_id' => $warehouseId,
                    'to_warehouse_id' => $warehouseId,
                    'movement_type' => 'Adjustment',
                    'quantity' => $adjustmentQty,
                    'batch_number' => $inventory['batch_number'] ?? null,
                    'movement_date' => date('Y-m-d H:i:s'),
                    'performed_by' => session()->get('user_id'),
                    'notes' => $fullNotes
                ];

                $movementId = $stockMovementModel->insert($movementData);
                
                if ($movementId) {
                    $successCount++;
                } else {
                    $failCount++;
                    $errors[] = "Failed to record adjustment for material ID: " . $materialId;
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed. Please try again.'
                ]);
            }

            if ($successCount > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Successfully adjusted {$successCount} item(s)" . ($failCount > 0 ? ". {$failCount} failed." : ''),
                    'success_count' => $successCount,
                    'fail_count' => $failCount,
                    'errors' => $errors
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to adjust any items',
                    'errors' => $errors
                ]);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'processAdjustFromScanned error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error processing adjustment: ' . $e->getMessage()
            ]);
        }
    }
}

