<?php

namespace App\Libraries;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRGdImagePNG;

class QRCodeLibrary
{
    protected $options;

    public function __construct()
    {
        $this->options = new QROptions([
            // Use QRGdImagePNG for GD-based image generation
            'outputInterface' => QRGdImagePNG::class,
            'quality'         => 90,
            'eccLevel'         => EccLevel::M, // Medium error correction for better reliability
            'scale'            => 10, // Size of one QR module in pixels (reduced from 20 for better file size)
            'bgColor'          => [255, 255, 255], // White background
            'imageTransparent' => false,
            'outputBase64'     => false, // Return raw binary data, not base64
            'drawLightModules' => true,
            'drawCircularModules' => true,
            'circleRadius'     => 0.4,
            'logoSpaceWidth'   => 13,
            'logoSpaceHeight'  => 13,
            'keepAsSquare'     => [
                QRMatrix::M_FINDER_DARK,
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_ALIGNMENT_DARK,
            ],
            'moduleValues'     => [
                QRMatrix::M_FINDER_DARK    => [0, 63, 255],    // Dark blue for finder pattern
                QRMatrix::M_FINDER_DOT     => [0, 63, 255],    // Dark blue for finder dot
                QRMatrix::M_FINDER         => [233, 233, 233], // Light gray for finder
                QRMatrix::M_ALIGNMENT_DARK => [0, 63, 255],    // Dark blue for alignment
                QRMatrix::M_ALIGNMENT      => [233, 233, 233], // Light gray for alignment
                QRMatrix::M_DATA_DARK      => [0, 0, 0],       // Black for data dark
                QRMatrix::M_DATA           => [255, 255, 255], // White for data light
            ],
        ]);
    }

    /**
     * Create a fresh QRCode instance for each generation
     * This prevents data accumulation between renders
     */
    private function createQRCode()
    {
        return new QRCode($this->options);
    }

    /**
     * Generate QR code for a material
     */
    public function generateMaterialQR($materialId, $materialCode, $materialName, $filePath = null)
    {
        // Use compact format to reduce data size
        // Only include essential information
        $data = [
            't' => 'm', // type: material
            'i' => (int)$materialId, // id
            'c' => $materialCode, // code
        ];
        
        // Only include name if it's not too long (max 50 chars)
        if (strlen($materialName) <= 50) {
            $data['n'] = $materialName; // name
        }
        
        $qrData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Create a fresh QRCode instance to prevent data accumulation
        $qrCode = $this->createQRCode();
        
        // If file path is provided, let the library save directly
        if ($filePath !== null) {
            $qrCode->render($qrData, $filePath);
            return $filePath; // Return the file path
        }
        
        // Otherwise return the image data
        return $qrCode->render($qrData);
    }

    /**
     * Generate QR code for inventory item
     */
    public function generateInventoryQR($inventoryId, $materialCode, $materialName, $warehouseCode, $batchNumber = null, $filePath = null)
    {
        // Use compact format to reduce data size
        $data = [
            't' => 'i', // type: inventory
            'i' => (int)$inventoryId, // id
            'c' => $materialCode, // code
            'w' => $warehouseCode, // warehouse
        ];
        
        // Only include batch if provided and not too long
        if ($batchNumber && strlen($batchNumber) <= 30) {
            $data['b'] = $batchNumber; // batch
        }
        
        // Only include name if it's not too long (max 50 chars)
        if (strlen($materialName) <= 50) {
            $data['n'] = $materialName; // name
        }
        
        $qrData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Create a fresh QRCode instance to prevent data accumulation
        $qrCode = $this->createQRCode();
        
        // If file path is provided, let the library save directly
        if ($filePath !== null) {
            $qrCode->render($qrData, $filePath);
            return $filePath; // Return the file path
        }
        
        // Otherwise return the image data
        return $qrCode->render($qrData);
    }

    /**
     * Generate QR code for warehouse location
     */
    public function generateWarehouseQR($warehouseId, $warehouseCode, $warehouseName)
    {
        // Use compact format to reduce data size
        $data = [
            't' => 'w', // type: warehouse
            'i' => (int)$warehouseId, // id
            'c' => $warehouseCode, // code
        ];
        
        // Only include name if it's not too long (max 50 chars)
        if (strlen($warehouseName) <= 50) {
            $data['n'] = $warehouseName; // name
        }
        
        $qrData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Create a fresh QRCode instance to prevent data accumulation
        $qrCode = $this->createQRCode();
        return $qrCode->render($qrData);
    }

    /**
     * Generate QR code for stock movement
     */
    public function generateStockMovementQR($movementId, $referenceNumber)
    {
        // Use compact format to reduce data size
        $data = [
            't' => 'mv', // type: movement
            'i' => (int)$movementId, // id
            'r' => $referenceNumber, // reference
        ];
        
        $qrData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Create a fresh QRCode instance to prevent data accumulation
        $qrCode = $this->createQRCode();
        return $qrCode->render($qrData);
    }

    /**
     * Save QR code to file
     * This method is kept for backward compatibility but is deprecated.
     * Use the filePath parameter in generate methods instead.
     */
    public function saveQRCode($qrCodeData, $filename, $path = null)
    {
        if (!$path) {
            $path = WRITEPATH . 'qrcodes/';
        }
        
        // Create directory if it doesn't exist
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \RuntimeException('Failed to create QR code directory: ' . $path);
            }
        }
        
        // Ensure filename doesn't already have .png extension
        if (substr($filename, -4) === '.png') {
            $filename = substr($filename, 0, -4);
        }
        
        $filepath = $path . $filename . '.png';
        
        // If qrCodeData is a file path (string starting with / or containing path separators), it's already saved
        if (is_string($qrCodeData) && (strpos($qrCodeData, '/') !== false || strpos($qrCodeData, '\\') !== false)) {
            // It's already a file path, just verify it exists
            if (file_exists($qrCodeData)) {
                return $qrCodeData;
            }
        }
        
        // Otherwise, treat it as binary data and write it
        $result = @file_put_contents($filepath, $qrCodeData);
        
        if ($result === false) {
            throw new \RuntimeException('Failed to save QR code file: ' . $filepath);
        }
        
        // Verify file was created
        if (!file_exists($filepath)) {
            throw new \RuntimeException('QR code file was not created: ' . $filepath);
        }
        
        // Verify it's a valid PNG file (check file size and magic bytes)
        if (filesize($filepath) < 100) {
            throw new \RuntimeException('QR code file is too small (possibly corrupted): ' . $filepath);
        }
        
        // Check PNG magic bytes (first 8 bytes should be: 89 50 4E 47 0D 0A 1A 0A)
        $handle = @fopen($filepath, 'rb');
        if ($handle) {
            $header = fread($handle, 8);
            fclose($handle);
            if (substr($header, 0, 8) !== "\x89PNG\r\n\x1a\n") {
                throw new \RuntimeException('Invalid PNG file (magic bytes check failed): ' . $filepath);
            }
        }
        
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
        $qrCode = $this->createQRCode();
        return $qrCode->render($text);
    }

    /**
     * Generate URL QR code
     */
    public function generateURLQR($url)
    {
        $qrCode = $this->createQRCode();
        return $qrCode->render($url);
    }
}