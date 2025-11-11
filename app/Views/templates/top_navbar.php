<?php
/**
 * Top Navigation Bar Template (Alternative to Sidebar)
 * 
 * Simple top navbar with logout button
 * Color scheme: Background #1a365d, Text #FFFFFF
 * 
 * Usage: <?= view('templates/top_navbar', ['user' => $user, 'page_title' => 'Dashboard Title']) ?>
 */

$userRole = $user['role'] ?? 'Unknown';
$pageTitle = $page_title ?? 'WITMS Dashboard';
?>

<style>
    .top-navbar {
        background-color: #1a365d;
        padding: 15px 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: fixed;
        top: 0;
        right: 0;
        left: 250px; /* Start exactly where sidebar ends */
        z-index: 999;
        margin-left: 0; /* Remove margin to eliminate gap */
        margin-bottom: 0 !important; /* Remove bottom margin */
    }
    
    .top-navbar .navbar-brand {
        color: #FFFFFF;
        font-weight: bold;
        font-size: 20px;
    }
    
    .top-navbar .navbar-brand:hover {
        color: #FFFFFF;
    }
    
    .top-navbar .navbar-brand i {
        margin-right: 8px;
    }
    
    .top-navbar .user-info {
        color: #FFFFFF;
        margin-right: 15px;
        display: flex;
        align-items: center;
    }
    
    .top-navbar .user-info i {
        margin-right: 8px;
        font-size: 20px;
    }
    
    .top-navbar .user-info .user-name {
        font-weight: 600;
        margin-right: 5px;
    }
    
    .top-navbar .user-info .user-role {
        font-size: 12px;
        opacity: 0.9;
    }
    
    .btn-logout-top {
        background-color: #dc3545;
        color: #FFFFFF;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-logout-top:hover {
        background-color: #c82333;
        color: #FFFFFF;
    }
    
    .btn-logout-top i {
        margin-right: 5px;
    }    /* Adjust main content to account for fixed navbar */
    .main-content {
        padding-top: 90px !important; /* Space for fixed navbar + gap */
    }
    
    /* Add spacing to container inside main-content */
    .main-content > .container-fluid {
        padding-top: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .top-navbar {
            margin-left: 0;
            left: 0;
            position: relative;
        }
        
        .main-content {
            padding-top: 20px !important;
        }
        
        .main-content > .container-fluid {
            padding-top: 0;
        }
    }
</style>

<nav class="navbar navbar-expand-lg top-navbar mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('/dashboard') ?>">
            <i class="bi bi-building"></i>
            <?= esc($pageTitle) ?>
        </a>
        
        <div class="d-flex align-items-center">
            <div class="user-info">
                <i class="bi bi-person-circle"></i>
                <div>
                    <div class="user-name"><?= esc($user['full_name'] ?? 'User') ?></div>
                    <div class="user-role"><?= esc($userRole) ?></div>
                </div>
            </div>
            
            <a href="<?= base_url('auth/logout') ?>" 
               class="btn btn-logout-top" 
               onclick="return confirm('Are you sure you want to logout?')">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>
        </div>
    </div>
</nav>
