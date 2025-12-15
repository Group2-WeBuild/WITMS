<style>
/* ==========================================
   MINIMAL MOBILE STYLES FOR WITMS
   Sidebar styles moved to sidebar_navigation.php
   ========================================== */

/* Print styles - clean and minimal */
@media print {
    .sidebar-nav,
    .top-navbar,
    .btn,
    .mobile-menu-toggle {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
}
</style>
