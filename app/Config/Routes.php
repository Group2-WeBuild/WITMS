<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==========================================
// AUTHENTICATION ROUTES
// ==========================================
$routes->get('/', 'Auth::login');
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');
$routes->post('/logout', 'Auth::logout');
$routes->get('/auth/login', 'Auth::login');
$routes->post('/auth/login', 'Auth::login');
$routes->get('/auth/logout', 'Auth::logout');
$routes->post('/auth/logout', 'Auth::logout');
$routes->get('/auth/reset-password', 'Auth::resetPassword');
$routes->post('/auth/reset-password', 'Auth::resetPassword');
$routes->get('/auth/reset-password-confirm/(:any)', 'Auth::resetPasswordConfirm/$1');
$routes->post('/auth/reset-password-confirm/(:any)', 'Auth::resetPasswordConfirm/$1');
$routes->get('/auth/contact-admin', 'Auth::contactAdministrator');
$routes->post('/auth/contact-admin', 'Auth::contactAdministrator');

// ==========================================
// DASHBOARD ROUTES - ROLE-BASED ACCESS
// ==========================================
// Main dashboard (redirects based on role)
$routes->get('/dashboard', 'Dashboard::index');

// Role-specific dashboard routes
$routes->get('/warehouse-manager/dashboard', 'Dashboard::warehouseManager');
$routes->get('/warehouse-staff/dashboard', 'Dashboard::warehouseStaff');
$routes->get('/inventory-auditor/dashboard', 'Dashboard::inventoryAuditor');
$routes->get('/procurement-officer/dashboard', 'Dashboard::procurementOfficer');
$routes->get('/accounts-payable/dashboard', 'Dashboard::accountsPayable');
$routes->get('/accounts-receivable/dashboard', 'Dashboard::accountsReceivable');
$routes->get('/it-administrator/dashboard', 'ITAdministratorController::dashboard');
$routes->get('/top-management/dashboard', 'Dashboard::topManagement');

// ==========================================
// WAREHOUSE MANAGER ROUTES
// ==========================================
$routes->group('warehouse-manager', function ($routes) {
    // Inventory Management
    $routes->get('inventory', 'WarehouseManagerController::inventory');
    $routes->get('inventory/add', 'WarehouseManagerController::inventoryAdd');
    $routes->post('inventory/store', 'WarehouseManagerController::inventoryStore');
    $routes->post('inventory/get-material-quantity', 'WarehouseManagerController::getMaterialQuantity');
    $routes->get('inventory/edit/(:num)', 'WarehouseManagerController::inventoryEdit/$1');
    $routes->post('inventory/update/(:num)', 'WarehouseManagerController::inventoryUpdate/$1');
    $routes->get('inventory/view/(:num)', 'WarehouseManagerController::inventoryView/$1');
    $routes->get('inventory/adjust/(:num)', 'WarehouseManagerController::inventoryAdjust/$1');
    $routes->post('inventory/adjust-process/(:num)', 'WarehouseManagerController::inventoryAdjustProcess/$1');
    $routes->get('inventory/low-stock', 'WarehouseManagerController::inventoryLowStock');
    $routes->get('inventory/expiring', 'WarehouseManagerController::inventoryExpiring');
    $routes->get('inventory/recalculate-available', 'WarehouseManagerController::inventoryRecalculateAvailable');
    // Inventory QR Codes
    $routes->post('inventory/qr-generate/(:num)', 'WarehouseManagerController::inventoryGenerateQR/$1');
    $routes->post('inventory/qr-batch-generate', 'WarehouseManagerController::inventoryBatchGenerateQR');
    $routes->get('inventory/qr-download/(:any)', 'WarehouseManagerController::inventoryDownloadQR/$1');
      // Materials Management
    $routes->get('materials', 'WarehouseManagerController::materials');
    $routes->get('materials/add', 'WarehouseManagerController::materialsAdd');
    $routes->post('materials/store', 'WarehouseManagerController::materialsStore');
    $routes->get('materials/edit/(:num)', 'WarehouseManagerController::materialsEdit/$1');
    $routes->post('materials/update/(:num)', 'WarehouseManagerController::materialsUpdate/$1');
    $routes->get('materials/view/(:num)', 'WarehouseManagerController::materialsView/$1');
    $routes->get('materials/deactivate/(:num)', 'WarehouseManagerController::materialsDeactivate/$1');
    $routes->get('materials/activate/(:num)', 'WarehouseManagerController::materialsActivate/$1');
    // Material QR Codes
    $routes->get('materials/qr-print', 'WarehouseManagerController::materialsQRPrint');
    $routes->post('materials/qr-generate/(:num)', 'WarehouseManagerController::materialsGenerateQR/$1');
    $routes->post('materials/qr-batch-generate', 'WarehouseManagerController::materialsBatchGenerateQR');
    $routes->get('materials/qr-download/(:any)', 'WarehouseManagerController::materialsDownloadQR/$1');
    // Material Categories
    $routes->get('materials/categories', 'WarehouseManagerController::materialsCategories');
    
    // QR Code routes
    $routes->get('qrcodes/(:any)', 'QRCodeController::serve/$1');

    // Warehouse Management
    $routes->get('warehouse-management', 'WarehouseManagerController::warehouseManagement');
    $routes->get('warehouse/add', 'WarehouseManagerController::warehouseAdd');
    $routes->post('warehouse/store', 'WarehouseManagerController::warehouseStore');
    $routes->get('warehouse/edit/(:num)', 'WarehouseManagerController::warehouseEdit/$1');
    $routes->post('warehouse/update/(:num)', 'WarehouseManagerController::warehouseUpdate/$1');
    $routes->get('warehouse/view/(:num)', 'WarehouseManagerController::warehouseView/$1');
    $routes->get('warehouse/map', 'WarehouseManagerController::warehouseMap');
    $routes->get('warehouse/deactivate/(:num)', 'WarehouseManagerController::warehouseDeactivate/$1');
    $routes->get('warehouse/activate/(:num)', 'WarehouseManagerController::warehouseActivate/$1');
    
    // Stock Movements
    $routes->get('stock-movements', 'WarehouseManagerController::stockMovements');
    $routes->get('stock-movements/stats', 'WarehouseManagerController::getStockMovementStats');
    
    // Reports
    $routes->get('reports', 'WarehouseManagerController::reports');
    $routes->get('reports/generate/(:alpha)', 'WarehouseManagerController::generateReport/$1');
    
    // Analytics
    $routes->get('analytics', 'WarehouseManagerController::analytics');
    
    // Stock Alerts
    $routes->get('stock-alerts', 'WarehouseManagerController::stockAlerts');
    $routes->post('stock-alerts/contact-procurement', 'WarehouseManagerController::contactProcurement');
    
    // Staff Management
    $routes->get('staff-management', 'WarehouseManagerController::staffManagement');
    $routes->post('staff/assign/(:num)', 'WarehouseManagerController::assignWork/$1');

    // Scan Item
    $routes->get('scan-item', 'WarehouseManagerController::scanItem');
      
});

// ==========================================
// WAREHOUSE STAFF ROUTES
// ==========================================
$routes->group('warehouse-staff', function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'WarehouseStaffController::dashboard');
    
    // Scan Items
    $routes->get('scan-item', 'WarehouseStaffController::scanItem');
    $routes->post('scan/store-items', 'WarehouseStaffController::storeScannedItems');
    
    // QR Scanner
    $routes->get('qr-scanner', 'WarehouseStaffController::qrScanner');
    $routes->post('qr/scan-data', 'WarehouseStaffController::qrScanData');
    $routes->post('qr/read-upload', 'WarehouseStaffController::readQRUpload');
    
    // Search Inventory
    $routes->get('search-inventory', 'WarehouseStaffController::searchInventory');
    $routes->post('search-inventory', 'WarehouseStaffController::searchInventory');
    
    // Receive Stock
    $routes->get('receive', 'WarehouseStaffController::receiveStock');
    $routes->post('receive/process', 'WarehouseStaffController::processReceiveStock');
    
    // Issue Stock
    $routes->get('issue', 'WarehouseStaffController::issueStock');
    $routes->post('issue/process', 'WarehouseStaffController::processIssueStock');
    
    // Stock Transfer
    $routes->get('transfer', 'WarehouseStaffController::stockTransfer');
    $routes->post('transfer/process', 'WarehouseStaffController::processStockTransfer');
    
    // Activity Log
    $routes->get('activity', 'WarehouseStaffController::activity');
    
    // Stock Movements
    $routes->get('stock-movements', 'WarehouseStaffController::stockMovements');
    
    // AJAX Endpoints
    $routes->get('ajax/materials-by-warehouse', 'WarehouseStaffController::getMaterialsByWarehouse');
    $routes->get('ajax/dashboard-stats', 'WarehouseStaffController::getDashboardStats');
    $routes->get('ajax/warehouses', 'WarehouseStaffController::getWarehouses');
    $routes->get('ajax/departments', 'WarehouseStaffController::getDepartments');
    $routes->get('ajax/generate-requisition', 'WarehouseStaffController::generateRequisitionNumber');
    
    // Process scanned items actions
    $routes->post('scan/process-receive', 'WarehouseStaffController::processReceiveFromScanned');
    $routes->post('scan/process-issue', 'WarehouseStaffController::processIssueFromScanned');
    $routes->post('scan/process-transfer', 'WarehouseStaffController::processTransferFromScanned');
    $routes->post('scan/process-adjust', 'WarehouseStaffController::processAdjustFromScanned');
    
    // Additional AJAX endpoints
    $routes->get('ajax/unassigned-warehouses', 'WarehouseStaffController::getUnassignedWarehouses');
    $routes->get('ajax/generate-po', 'WarehouseStaffController::generatePONumber');
    $routes->get('ajax/generate-transfer-reference', 'WarehouseStaffController::generateTransferReference');
});

// ==========================================
// IT ADMINISTRATOR ROUTES
// ==========================================
$routes->group('it-administrator', function ($routes) {
    // User Management
    $routes->get('users', 'ITAdministratorController::users');
    $routes->post('users/create', 'ITAdministratorController::createUser');
    $routes->post('users/get', 'ITAdministratorController::getUser');
    $routes->post('users/update', 'ITAdministratorController::updateUser');
    $routes->post('users/soft-delete', 'ITAdministratorController::softDeleteUser');
    $routes->post('users/reactivate', 'ITAdministratorController::reactivateUser');
    
    // Warehouse Assignments
    $routes->get('warehouse-assignments', 'ITAdministratorController::warehouseAssignments');
    $routes->post('warehouse-assignments/assign', 'ITAdministratorController::assignUserToWarehouse');
    $routes->post('warehouse-assignments/remove', 'ITAdministratorController::removeUserFromWarehouse');
    $routes->post('warehouse-assignments/reactivate', 'ITAdministratorController::reactivateAssignment');
    $routes->post('warehouse-assignments/set-primary', 'ITAdministratorController::setPrimaryWarehouse');
    $routes->post('warehouse-assignments/get-user-warehouses', 'ITAdministratorController::getUserWarehouses');
    
    // Department Management
    $routes->get('departments', 'ITAdministratorController::departments');
    $routes->post('departments/create', 'ITAdministratorController::createDepartment');
    $routes->post('departments/get', 'ITAdministratorController::getDepartment');
    $routes->post('departments/update', 'ITAdministratorController::updateDepartment');
    $routes->post('departments/soft-delete', 'ITAdministratorController::softDeleteDepartment');
    $routes->post('departments/reactivate', 'ITAdministratorController::reactivateDepartment');
    
    // System Settings
    $routes->get('settings', 'ITAdministratorController::settings');
    
    // Database Backup
    $routes->get('backup', 'ITAdministratorController::backup');
    $routes->post('backup/create', 'ITAdministratorController::createBackup');
    $routes->get('backup/download/(:any)', 'ITAdministratorController::downloadBackup/$1');
    $routes->post('backup/delete', 'ITAdministratorController::deleteBackup');
    
    // System Logs
    $routes->get('logs', 'ITAdministratorController::logs');
    $routes->post('logs/get-entries', 'ITAdministratorController::getLogEntries');
    $routes->get('logs/download/(:any)', 'ITAdministratorController::downloadLog/$1');
    $routes->post('logs/delete', 'ITAdministratorController::deleteLog');
    $routes->post('logs/clear-old', 'ITAdministratorController::clearOldLogs');
    
    // Configuration
    $routes->get('config', 'ITAdministratorController::config');
    
    // Warehouse Management
    $routes->get('warehouse-management', 'ITAdministratorController::warehouseManagement');
    $routes->post('warehouse-management/create', 'ITAdministratorController::createWarehouse');
    $routes->post('warehouse-management/get', 'ITAdministratorController::getWarehouse');
    $routes->post('warehouse-management/update', 'ITAdministratorController::updateWarehouse');
    $routes->post('warehouse-management/delete', 'ITAdministratorController::deleteWarehouse');
    
    // Inventory Management (Full CRUD)
    $routes->get('inventory', 'ITAdministratorController::inventory');
    $routes->post('inventory/create', 'ITAdministratorController::createInventory');
    $routes->post('inventory/get', 'ITAdministratorController::getInventory');
    $routes->post('inventory/update', 'ITAdministratorController::updateInventory');
    $routes->post('inventory/delete', 'ITAdministratorController::deleteInventory');
    
    // Materials Management (Full CRUD)
    $routes->get('materials', 'ITAdministratorController::materials');
    $routes->post('materials/create', 'ITAdministratorController::createMaterial');
    $routes->post('materials/get', 'ITAdministratorController::getMaterial');
    $routes->post('materials/update', 'ITAdministratorController::updateMaterial');
    $routes->post('materials/delete', 'ITAdministratorController::deleteMaterial');
});
