<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class QRCode extends BaseConfig
{
    /**
     * QR Code default settings
     */
    public $defaultOptions = [
        'version'        => 7,
        'outputType'     => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'       => \chillerlan\QRCode\Common\EccLevel::L,
        'scale'          => 5,
        'imageBase64'    => false,
        'imageTransparent' => false,
        'drawLightModules' => true,
        'drawCircularModules' => true,
        'circleRadius' => 0.4,
        'logoSpaceWidth' => 13,
        'logoSpaceHeight' => 13,
    ];

    /**
     * Storage path for generated QR codes
     */
    public $storagePath = WRITEPATH . 'qrcodes/';

    /**
     * URL path for accessing QR codes
     */
    public $urlPath = 'qrcodes/';

    /**
     * QR Code types
     */
    public $types = [
        'material' => 'Material QR Code',
        'inventory' => 'Inventory QR Code',
        'warehouse' => 'Warehouse QR Code',
        'movement' => 'Stock Movement QR Code',
        'location' => 'Location QR Code',
    ];

    /**
     * Whether to automatically generate QR codes
     */
    public $autoGenerate = true;

    /**
     * QR Code size settings
     */
    public $sizes = [
        'small' => 3,
        'medium' => 5,
        'large' => 8,
    ];

    /**
     * Default size
     */
    public $defaultSize = 'medium';
}
