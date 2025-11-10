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

