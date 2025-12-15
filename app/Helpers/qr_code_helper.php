<?php

if (!function_exists('generate_material_qr')) {
    /**
     * Generate QR code for material
     */
    function generate_material_qr($materialId, $materialCode, $materialName, $saveToFile = false)
    {
        $qrLibrary = new \App\Libraries\QRCodeLibrary();
        $qrCode = $qrLibrary->generateMaterialQR($materialId, $materialCode, $materialName);
        
        if ($saveToFile) {
            $filename = 'material_' . $materialId;
            return $qrLibrary->saveQRCode($qrCode, $filename);
        }
        
        return $qrLibrary->getQRCodeBase64($qrCode);
    }
}

if (!function_exists('generate_inventory_qr')) {
    /**
     * Generate QR code for inventory item
     */
    function generate_inventory_qr($inventoryId, $materialCode, $warehouseCode, $batchNumber = null, $saveToFile = false)
    {
        $qrLibrary = new \App\Libraries\QRCodeLibrary();
        $qrCode = $qrLibrary->generateInventoryQR($inventoryId, $materialCode, $warehouseCode, $batchNumber);
        
        if ($saveToFile) {
            $filename = 'inventory_' . $inventoryId;
            return $qrLibrary->saveQRCode($qrCode, $filename);
        }
        
        return $qrLibrary->getQRCodeBase64($qrCode);
    }
}

if (!function_exists('generate_warehouse_qr')) {
    /**
     * Generate QR code for warehouse
     */
    function generate_warehouse_qr($warehouseId, $warehouseCode, $warehouseName, $saveToFile = false)
    {
        $qrLibrary = new \App\Libraries\QRCodeLibrary();
        $qrCode = $qrLibrary->generateWarehouseQR($warehouseId, $warehouseCode, $warehouseName);
        
        if ($saveToFile) {
            $filename = 'warehouse_' . $warehouseId;
            return $qrLibrary->saveQRCode($qrCode, $filename);
        }
        
        return $qrLibrary->getQRCodeBase64($qrCode);
    }
}

if (!function_exists('generate_movement_qr')) {
    /**
     * Generate QR code for stock movement
     */
    function generate_movement_qr($movementId, $referenceNumber, $saveToFile = false)
    {
        $qrLibrary = new \App\Libraries\QRCodeLibrary();
        $qrCode = $qrLibrary->generateStockMovementQR($movementId, $referenceNumber);
        
        if ($saveToFile) {
            $filename = 'movement_' . $movementId;
            return $qrLibrary->saveQRCode($qrCode, $filename);
        }
        
        return $qrLibrary->getQRCodeBase64($qrCode);
    }
}

if (!function_exists('qr_code_img')) {
    /**
     * Display QR code image
     */
    function qr_code_img($qrCodeData, $attributes = [])
    {
        $attr = '';
        foreach ($attributes as $key => $value) {
            $attr .= ' ' . $key . '="' . esc($value) . '"';
        }
        
        return '<img src="' . $qrCodeData . '"' . $attr . '>';
    }
}
