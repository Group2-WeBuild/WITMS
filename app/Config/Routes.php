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
$routes->get('/dashboard/index', 'Dashboard::index');

// Role-specific dashboard routes
$routes->get('/dashboard/warehouse-manager', 'Dashboard::warehouseManager');
$routes->get('/dashboard/warehouse-staff', 'Dashboard::warehouseStaff');
$routes->get('/dashboard/inventory-auditor', 'Dashboard::inventoryAuditor');
$routes->get('/dashboard/procurement-officer', 'Dashboard::procurementOfficer');
$routes->get('/dashboard/accounts-payable', 'Dashboard::accountsPayable');
$routes->get('/dashboard/accounts-receivable', 'Dashboard::accountsReceivable');
$routes->get('/dashboard/it-administrator', 'Dashboard::itAdministrator');
$routes->get('/dashboard/top-management', 'Dashboard::topManagement');

