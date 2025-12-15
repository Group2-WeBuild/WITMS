<?php
// Simple test to check if the route is accessible
echo "Testing warehouse-staff scan-item route...\n";
echo "URL: " . base_url('warehouse-staff/scan-item') . "\n";

// Check if controller exists
if (file_exists(APPPATH . 'Controllers/WarehouseStaffController.php')) {
    echo "✓ WarehouseStaffController exists\n";
} else {
    echo "✗ WarehouseStaffController missing\n";
}

// Check if method exists
$controller = file_get_contents(APPPATH . 'Controllers/WarehouseStaffController.php');
if (strpos($controller, 'function scanItem') !== false) {
    echo "✓ scanItem method exists\n";
} else {
    echo "✗ scanItem method missing\n";
}

// Check if view exists
if (file_exists(APPPATH . 'Views/users/warehouse_staff/scan_item.php')) {
    echo "✓ scan_item view exists\n";
} else {
    echo "✗ scan_item view missing\n";
}
?>
