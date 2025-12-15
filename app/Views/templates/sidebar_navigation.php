<?php
/**
 * Sidebar Navigation Template
 * 
 * Dynamic left sidebar navigation based on user role
 * Color scheme: Background #1a365d, Text #FFFFFF
 * 
 * Usage: <?= view('templates/sidebar_navigation', ['user' => $user]) ?>
 */

$userRole = $user['role'] ?? 'Unknown';
$currentUrl = current_url();

// Define navigation items for each role
$navigationItems = [    
    'Warehouse Manager' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/warehouse-manager/dashboard'],
        ['icon' => 'bi-box-seam', 'label' => 'Inventory', 'url' => '/warehouse-manager/inventory'],
        ['icon' => 'bi-box-seam-fill', 'label' => 'Low Stock Items', 'url' => '/warehouse-manager/inventory/low-stock'],
        ['icon' => 'bi-clock-history', 'label' => 'Expiring Items', 'url' => '/warehouse-manager/inventory/expiring'],
        ['icon' => 'bi-boxes', 'label' => 'Materials', 'url' => '/warehouse-manager/materials'],
        ['icon' => 'bi-arrow-left-right', 'label' => 'Stock Movements', 'url' => '/warehouse-manager/stock-movements'],
        ['icon' => 'bi-exclamation-triangle', 'label' => 'Stock Alerts', 'url' => '/warehouse-manager/stock-alerts'],
        ['icon' => 'bi-building', 'label' => 'Warehouse Management', 'url' => '/warehouse-manager/warehouse-management'],
        ['icon' => 'bi-map', 'label' => 'Warehouse Map', 'url' => '/warehouse-manager/warehouse/map'],
        ['icon' => 'bi-people', 'label' => 'Staff Management', 'url' => '/warehouse-manager/staff-management'],
        ['icon' => 'bi-file-earmark-text', 'label' => 'Reports', 'url' => '/warehouse-manager/reports'],
    ],
    
    'Warehouse Staff' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/warehouse-staff/dashboard'],
        ['icon' => 'bi-box-seam', 'label' => 'Inventory', 'url' => '/inventory'],
        ['icon' => 'bi-boxes', 'label' => 'Materials', 'url' => '/materials'],
        ['icon' => 'bi-upc-scan', 'label' => 'Scan Items', 'url' => '/warehouse-staff/scan'],
        ['icon' => 'bi-box-arrow-in-down', 'label' => 'Receive Stock', 'url' => '/warehouse-staff/receive'],
        ['icon' => 'bi-box-arrow-up', 'label' => 'Issue Stock', 'url' => '/warehouse-staff/issue'],
        ['icon' => 'bi-arrow-left-right', 'label' => 'Stock Transfer', 'url' => '/warehouse-staff/transfer'],
        ['icon' => 'bi-clock-history', 'label' => 'My Activity', 'url' => '/warehouse-staff/activity'],
    ],
    
    'Inventory Auditor' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/inventory-auditor/dashboard'],
        ['icon' => 'bi-clipboard-check', 'label' => 'Audit Schedule', 'url' => '/inventory-auditor/schedule'],
        ['icon' => 'bi-list-check', 'label' => 'Conduct Audit', 'url' => '/inventory-auditor/conduct'],
        ['icon' => 'bi-file-earmark-text', 'label' => 'Audit Reports', 'url' => '/inventory-auditor/reports'],
        ['icon' => 'bi-exclamation-circle', 'label' => 'Discrepancies', 'url' => '/inventory-auditor/discrepancies'],
        ['icon' => 'bi-graph-up-arrow', 'label' => 'Audit Analytics', 'url' => '/inventory-auditor/analytics'],
        ['icon' => 'bi-archive', 'label' => 'Audit History', 'url' => '/inventory-auditor/history'],
    ],
    
    'Procurement Officer' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/procurement-officer/dashboard'],
        ['icon' => 'bi-cart-plus', 'label' => 'Purchase Orders', 'url' => '/procurement-officer/purchase-orders'],
        ['icon' => 'bi-file-earmark-plus', 'label' => 'Create PO', 'url' => '/procurement-officer/create-po'],
        ['icon' => 'bi-shop', 'label' => 'Suppliers', 'url' => '/procurement-officer/suppliers'],
        ['icon' => 'bi-tag', 'label' => 'Quotations', 'url' => '/procurement-officer/quotations'],
        ['icon' => 'bi-box-seam', 'label' => 'Materials Catalog', 'url' => '/procurement-officer/materials'],
        ['icon' => 'bi-bell', 'label' => 'Reorder Alerts', 'url' => '/procurement-officer/reorder-alerts'],
        ['icon' => 'bi-graph-down', 'label' => 'Price Analysis', 'url' => '/procurement-officer/price-analysis'],
    ],
    
    'Accounts Payable Clerk' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/accounts-payable/dashboard'],
        ['icon' => 'bi-receipt', 'label' => 'Vendor Invoices', 'url' => '/accounts-payable/invoices'],
        ['icon' => 'bi-cash-coin', 'label' => 'Payments', 'url' => '/accounts-payable/payments'],
        ['icon' => 'bi-calendar-check', 'label' => 'Payment Schedule', 'url' => '/accounts-payable/schedule'],
        ['icon' => 'bi-people', 'label' => 'Suppliers', 'url' => '/accounts-payable/suppliers'],
        ['icon' => 'bi-file-text', 'label' => 'AP Reports', 'url' => '/accounts-payable/reports'],
        ['icon' => 'bi-clock-history', 'label' => 'Payment History', 'url' => '/accounts-payable/history'],
    ],
    
    'Accounts Receivable Clerk' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/accounts-receivable/dashboard'],
        ['icon' => 'bi-file-earmark-text', 'label' => 'Customer Invoices', 'url' => '/accounts-receivable/invoices'],
        ['icon' => 'bi-cash-stack', 'label' => 'Receipts', 'url' => '/accounts-receivable/receipts'],
        ['icon' => 'bi-people', 'label' => 'Clients', 'url' => '/accounts-receivable/clients'],
        ['icon' => 'bi-calendar-x', 'label' => 'Overdue Accounts', 'url' => '/accounts-receivable/overdue'],
        ['icon' => 'bi-file-text', 'label' => 'AR Reports', 'url' => '/accounts-receivable/reports'],
        ['icon' => 'bi-clock-history', 'label' => 'Receipt History', 'url' => '/accounts-receivable/history'],
    ],
    
    'IT Administrator' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/it-administrator/dashboard'],
        ['icon' => 'bi-people', 'label' => 'User Management', 'url' => '/it-administrator/users'],
        ['icon' => 'bi-shield-check', 'label' => 'Roles & Permissions', 'url' => '/it-administrator/roles'],
        ['icon' => 'bi-building', 'label' => 'Departments', 'url' => '/it-administrator/departments'],
        ['icon' => 'bi-hdd-network', 'label' => 'System Settings', 'url' => '/it-administrator/settings'],
        ['icon' => 'bi-database', 'label' => 'Database Backup', 'url' => '/it-administrator/backup'],
        ['icon' => 'bi-activity', 'label' => 'System Logs', 'url' => '/it-administrator/logs'],
        ['icon' => 'bi-gear', 'label' => 'Configuration', 'url' => '/it-administrator/config'],
    ],
    
    'Top Management' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/top-management/dashboard'],
        ['icon' => 'bi-graph-up', 'label' => 'Company Analytics', 'url' => '/top-management/analytics'],
        ['icon' => 'bi-cash-coin', 'label' => 'Financial Overview', 'url' => '/top-management/financial'],
        ['icon' => 'bi-building', 'label' => 'Warehouse Overview', 'url' => '/top-management/warehouses'],
        ['icon' => 'bi-people', 'label' => 'Employee Overview', 'url' => '/top-management/employees'],
        ['icon' => 'bi-clipboard-data', 'label' => 'Executive Reports', 'url' => '/top-management/reports'],
        ['icon' => 'bi-trophy', 'label' => 'Performance KPIs', 'url' => '/top-management/kpis'],
        ['icon' => 'bi-gear', 'label' => 'Strategic Planning', 'url' => '/top-management/planning'],
    ],
];

$menuItems = $navigationItems[$userRole] ?? [];
?>

<!-- Sidebar Navigation -->
<style>
    .sidebar-nav {
        background-color: #1a365d;
        height: 100vh;
        width: 250px;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 1000;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    
    /* Custom scrollbar for webkit browsers */
    .sidebar-nav::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-nav::-webkit-scrollbar-track {
        background: #1a365d;
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: #2d4a7c;
        border-radius: 3px;
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
        background: #3d5a8c;
    }
    
    .sidebar-brand {
        background-color: #152a4a;
        color: #FFFFFF;
        padding: 20px;
        text-align: center;
        border-bottom: 2px solid #2d4a7c;
    }
    
    .sidebar-brand h4 {
        color: #FFFFFF;
        margin: 0;
        font-weight: bold;
        font-size: 24px;
    }
    
    .sidebar-brand p {
        color: #FFFFFF;
        margin: 5px 0 0 0;
        font-size: 12px;
        opacity: 0.9;
    }
    
    .sidebar-user {
        background-color: #152a4a;
        color: #FFFFFF;
        padding: 15px;
        border-bottom: 1px solid #2d4a7c;
    }
    
    .sidebar-user .user-avatar {
        width: 50px;
        height: 50px;
        background-color: #2d4a7c;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
    }
    
    .sidebar-user .user-avatar i {
        font-size: 24px;
        color: #FFFFFF;
    }
    
    .sidebar-user .user-name {
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        margin-bottom: 5px;
    }
    
    .sidebar-user .user-role {
        color: #FFFFFF;
        font-size: 12px;
        text-align: center;
        opacity: 0.8;
    }
    
    .sidebar-menu {
        padding: 15px 0;
        flex: 1 !important;
        overflow-y: auto !important;
    }
    
    .sidebar-menu-item {
        margin: 5px 10px;
    }
    
    .sidebar-menu-item a {
        display: flex !important;
        align-items: center !important;
        padding: 12px 15px !important;
        color: #FFFFFF !important;
        text-decoration: none !important;
        border-radius: 5px !important;
        transition: all 0.3s ease !important;
        pointer-events: auto !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 1 !important;
    }
    
    .sidebar-menu-item a:hover {
        background-color: #2d4a7c;
        transform: translateX(5px);
    }
    
    .sidebar-menu-item a.active {
        background-color: #2d4a7c;
        border-left: 3px solid #4a90e2;
    }
    
    .sidebar-menu-item a i {
        margin-right: 12px;
        font-size: 18px;
        width: 20px;
        text-align: center;
    }
    
    .sidebar-menu-item a span {
        font-size: 14px;
    }
    
    .sidebar-footer {
        position: sticky;
        bottom: 0;
        background-color: #152a4a;
        padding: 15px;
        border-top: 1px solid #2d4a7c;
    }
    
    .btn-logout {
        width: 100%;
        background-color: #dc3545;
        color: #FFFFFF;
        border: none;
        padding: 12px;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .btn-logout:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-logout i {
        margin-right: 8px;
    }
    
    .main-content {
        margin-left: 250px;
        padding: 20px;
    }
    
    @media (max-width: 768px) {
        /* Mobile Sidebar Styles - Integrated to avoid cascade issues */
        .sidebar-nav {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            z-index: 1055 !important;
            width: 280px !important;
            background-color: #1a365d !important;
            visibility: visible !important;
            opacity: 1 !important;
            overflow-y: hidden !important;
            overflow-x: hidden !important;
            pointer-events: auto !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        .sidebar-nav * {
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        
        .sidebar-nav.show {
            transform: translateX(0) !important;
        }
        
        .mobile-menu-toggle {
            display: block !important;
            position: fixed;
            top: 70px;
            right: 15px;
            z-index: 1050;
            background: #0d6efd !important;
            color: white !important;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-content {
            margin-left: 0;
            padding-top: 20px !important;
        }
        
        .top-navbar {
            display: none !important;
        }
        
        .sidebar-footer {
            background-color: #152a4a;
            padding: 15px;
            border-top: 1px solid #2d4a7c;
            flex-shrink: 0;
        }
    }
    
    /* Hide mobile menu toggle on desktop */
    @media (min-width: 769px) {
        .mobile-menu-toggle {
            display: none !important;
        }
    }
</style>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
    <i class="bi bi-list"></i>
</button>

<script>
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar-nav');
    console.log('Toggle clicked, sidebar:', sidebar);
    if (sidebar) {
        sidebar.classList.toggle('show');
        console.log('Sidebar classes after toggle:', sidebar.classList);
        // Prevent body scroll when menu is open
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    }
    // Prevent event bubbling to document click handler
    event.stopPropagation();
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar-nav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    // Don't close if clicking on the toggle button
    if (toggle && toggle.contains(event.target)) {
        return;
    }
    
    // Only close if sidebar is open and clicking outside of it
    if (window.innerWidth <= 768 && 
        sidebar && 
        sidebar.classList.contains('show') &&
        !sidebar.contains(event.target)) {
        sidebar.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar-nav');
    if (window.innerWidth > 768 && sidebar) {
        sidebar.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// Close menu when clicking on a link
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar-nav');
    if (window.innerWidth <= 768 && 
        sidebar && 
        sidebar.classList.contains('show') &&
        event.target.closest('.sidebar-nav a')) {
        sidebar.classList.remove('show');
        document.body.style.overflow = '';
    }
});
</script>

<div class="sidebar-nav">
    <!-- Brand/Logo -->
    <div class="sidebar-brand">
        <h4><i class="bi bi-building"></i> WITMS</h4>
        <p>WeBuild Construction</p>
    </div>
    
    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <?php foreach ($menuItems as $item): ?>
            <?php 
                $itemUrl = base_url($item['url']);
                $isActive = (strpos($currentUrl, $item['url']) !== false) ? 'active' : '';
            ?>
            <div class="sidebar-menu-item">
                <a href="<?= $itemUrl ?>" class="<?= $isActive ?>" onclick="console.log('Link clicked: <?= $item['label'] ?>')">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <span><?= esc($item['label']) ?></span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Sidebar Footer with Logout -->
    <div class="sidebar-footer">
        <a href="<?= base_url('/auth/logout') ?>" class="btn-logout" onclick="return confirm('Are you sure you want to logout?')">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
