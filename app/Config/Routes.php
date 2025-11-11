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
$routes->get('/it-administrator/dashboard', 'Dashboard::itAdministrator');
$routes->get('/top-management/dashboard', 'Dashboard::topManagement');

// ==========================================
// WAREHOUSE MANAGER ROUTES
// ==========================================
$routes->group('warehouse-manager', function ($routes) {
    // Inventory Management
    $routes->get('inventory', 'WarehouseManagerController::inventory');
    $routes->get('inventory/add', 'WarehouseManagerController::inventoryAdd');
    $routes->post('inventory/store', 'WarehouseManagerController::inventoryStore');
    $routes->get('inventory/edit/(:num)', 'WarehouseManagerController::inventoryEdit/$1');
    $routes->post('inventory/update/(:num)', 'WarehouseManagerController::inventoryUpdate/$1');
    $routes->get('inventory/view/(:num)', 'WarehouseManagerController::inventoryView/$1');
    $routes->get('inventory/low-stock', 'WarehouseManagerController::inventoryLowStock');
    $routes->get('inventory/expiring', 'WarehouseManagerController::inventoryExpiring');
    
    // Materials Management
    $routes->get('materials', 'WarehouseManagerController::materials');
    $routes->get('materials/add', 'WarehouseManagerController::materialsAdd');
    $routes->post('materials/store', 'WarehouseManagerController::materialsStore');
    $routes->get('materials/edit/(:num)', 'WarehouseManagerController::materialsEdit/$1');
    $routes->post('materials/update/(:num)', 'WarehouseManagerController::materialsUpdate/$1');
    $routes->get('materials/view/(:num)', 'WarehouseManagerController::materialsView/$1');
    $routes->get('materials/deactivate/(:num)', 'WarehouseManagerController::materialsDeactivate/$1');
    $routes->get('materials/activate/(:num)', 'WarehouseManagerController::materialsActivate/$1');
});
