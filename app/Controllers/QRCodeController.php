<?php

namespace App\Controllers;

use App\Libraries\QRCodeLibrary;
use App\Controllers\BaseController;

class QRCodeController extends BaseController
{
    protected $qrLibrary;

    public function __construct()
    {
        $this->qrLibrary = new QRCodeLibrary();
    }

    /**
     * Serve QR code image
     */
    public function serve($filename)
    {
        $filepath = WRITEPATH . 'qrcodes/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('QR Code not found');
        }
        
        $mimeType = mime_content_type($filepath);
        
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string) filesize($filepath))
            ->setBody(file_get_contents($filepath));
    }

    /**
     * Generate material QR code
     */
    public function generateMaterial($id)
    {
        $materialModel = new \App\Models\MaterialModel();
        $material = $materialModel->find($id);
        
        if (!$material) {
            return $this->response->setJSON(['success' => false, 'message' => 'Material not found']);
        }
        
        $qrCode = $this->qrLibrary->generateMaterialQR(
            $material['id'],
            $material['code'],
            $material['name']
        );
        
        $filename = 'material_' . $id . '_' . time();
        $filepath = $this->qrLibrary->saveQRCode($qrCode, $filename);
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('qrcodes/' . basename($filepath)),
            'filename' => basename($filepath)
        ]);
    }

    /**
     * Generate inventory QR code
     */
    public function generateInventory($id)
    {
        $inventoryModel = new \App\Models\InventoryModel();
        $inventory = $inventoryModel->getInventoryWithDetails($id);
        
        if (!$inventory) {
            return $this->response->setJSON(['success' => false, 'message' => 'Inventory not found']);
        }
        
        $qrCode = $this->qrLibrary->generateInventoryQR(
            $inventory['id'],
            $inventory['material_code'],
            $inventory['warehouse_code'],
            $inventory['batch_number']
        );
        
        $filename = 'inventory_' . $id . '_' . time();
        $filepath = $this->qrLibrary->saveQRCode($qrCode, $filename);
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('qrcodes/' . basename($filepath)),
            'filename' => basename($filepath)
        ]);
    }

    /**
     * Generate warehouse QR code
     */
    public function generateWarehouse($id)
    {
        $warehouseModel = new \App\Models\WarehouseModel();
        $warehouse = $warehouseModel->find($id);
        
        if (!$warehouse) {
            return $this->response->setJSON(['success' => false, 'message' => 'Warehouse not found']);
        }
        
        $qrCode = $this->qrLibrary->generateWarehouseQR(
            $warehouse['id'],
            $warehouse['code'],
            $warehouse['name']
        );
        
        $filename = 'warehouse_' . $id . '_' . time();
        $filepath = $this->qrLibrary->saveQRCode($qrCode, $filename);
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('qrcodes/' . basename($filepath)),
            'filename' => basename($filepath)
        ]);
    }

    /**
     * Generate stock movement QR code
     */
    public function generateMovement($id)
    {
        $stockMovementModel = new \App\Models\StockMovementModel();
        $movement = $stockMovementModel->find($id);
        
        if (!$movement) {
            return $this->response->setJSON(['success' => false, 'message' => 'Movement not found']);
        }
        
        $qrCode = $this->qrLibrary->generateStockMovementQR(
            $movement['id'],
            $movement['reference_number']
        );
        
        $filename = 'movement_' . $id . '_' . time();
        $filepath = $this->qrLibrary->saveQRCode($qrCode, $filename);
        
        return $this->response->setJSON([
            'success' => true,
            'qr_code' => base_url('qrcodes/' . basename($filepath)),
            'filename' => basename($filepath)
        ]);
    }

    /**
     * Batch generate QR codes
     */
    public function batchGenerate()
    {
        $type = $this->request->getPost('type');
        $ids = $this->request->getPost('ids');
        
        if (!$type || !$ids) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters']);
        }
        
        $results = [];
        
        foreach ($ids as $id) {
            switch ($type) {
                case 'material':
                    $response = $this->generateMaterial($id);
                    break;
                case 'inventory':
                    $response = $this->generateInventory($id);
                    break;
                case 'warehouse':
                    $response = $this->generateWarehouse($id);
                    break;
                default:
                    $response = ['success' => false, 'message' => 'Invalid type'];
            }
            
            $results[$id] = $response;
        }
        
        return $this->response->setJSON(['success' => true, 'results' => $results]);
    }
}
