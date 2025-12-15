<?php

namespace App\Libraries;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

class QRCodeLibrary
{
    protected $qrCode;
    protected $options;

    public function __construct()
    {
        $this->options = new QROptions([
            'version'        => 7,
            'outputType'     => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'       => EccLevel::L,
            'scale'          => 5,
            'imageBase64'    => false,
            'imageTransparent' => false,
            'drawLightModules' => true,
            'drawCircularModules' => true,
            'circleRadius' => 0.4,
            'logoSpaceWidth' => 13,
            'logoSpaceHeight' => 13,
        ]);
        
        $this->qrCode = new QRCode($this->options);
    }

    /**
     * Generate QR code for a material
     */
    public function generateMaterialQR($materialId, $materialCode, $materialName)
    {
        $data = [
            'type' => 'material',
            'id' => $materialId,
            'code' => $materialCode,
            'name' => $materialName,
            'url' => base_url('materials/view/' . $materialId)
        ];
        
        $qrData = json_encode($data);
        return $this->qrCode->render($qrData);
    }

    /**
     * Generate QR code for inventory item
     */
    public function generateInventoryQR($inventoryId, $materialCode, $warehouseCode, $batchNumber = null)
    {
        $data = [
            'type' => 'inventory',
            'id' => $inventoryId,
            'material' => $materialCode,
            'warehouse' => $warehouseCode,
            'batch' => $batchNumber,
            'url' => base_url('inventory/view/' . $inventoryId)
        ];
        
        $qrData = json_encode($data);
        return $this->qrCode->render($qrData);
    }

    /**
     * Generate QR code for warehouse location
     */
    public function generateWarehouseQR($warehouseId, $warehouseCode, $warehouseName)
    {
        $data = [
            'type' => 'warehouse',
            'id' => $warehouseId,
            'code' => $warehouseCode,
            'name' => $warehouseName,
            'url' => base_url('warehouse/view/' . $warehouseId)
        ];
        
        $qrData = json_encode($data);
        return $this->qrCode->render($qrData);
    }

    /**
     * Generate QR code for stock movement
     */
    public function generateStockMovementQR($movementId, $referenceNumber)
    {
        $data = [
            'type' => 'movement',
            'id' => $movementId,
            'reference' => $referenceNumber,
            'url' => base_url('stock-movements/view/' . $movementId)
        ];
        
        $qrData = json_encode($data);
        return $this->qrCode->render($qrData);
    }

    /**
     * Save QR code to file
     */
    public function saveQRCode($qrCodeData, $filename, $path = null)
    {
        if (!$path) {
            $path = WRITEPATH . 'qrcodes/';
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $filepath = $path . $filename . '.png';
        file_put_contents($filepath, $qrCodeData);
        
        return $filepath;
    }

    /**
     * Get QR code as base64
     */
    public function getQRCodeBase64($qrCodeData)
    {
        return 'data:image/png;base64,' . base64_encode($qrCodeData);
    }

    /**
     * Generate simple text QR code
     */
    public function generateTextQR($text)
    {
        return $this->qrCode->render($text);
    }

    /**
     * Generate URL QR code
     */
    public function generateURLQR($url)
    {
        return $this->qrCode->render($url);
    }
}
