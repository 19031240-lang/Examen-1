<?php
namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelQuartile;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class QrService
{
    public function generate(string $content, int $size, string $errorLevel)
    {
        // Validar contenido demasiado grande
        if(strlen($content) > 1000){
            http_response_code(413);
            throw new \Exception("Contenido demasiado grande");
        }

        // Asignar nivel de corrección
        $errorCorrection = match($errorLevel){
            'L' => new ErrorCorrectionLevelLow(),
            'M' => new ErrorCorrectionLevelMedium(),
            'Q' => new ErrorCorrectionLevelQuartile(),
            'H' => new ErrorCorrectionLevelHigh(),
            default => new ErrorCorrectionLevelMedium()
        };

        // Construir QR
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($content)
            ->errorCorrectionLevel($errorCorrection)
            ->size($size)
            ->build();

        $filename = 'qr_' . time() . '.png';
        $path = __DIR__ . '/../../storage/' . $filename;

        $result->saveToFile($path);

        return [
            "file" => $filename,
            "url" => "http://localhost/api-qr/storage/" . $filename
        ];
    }

    public function generateWifi($ssid, $password, $security, $size, $errorLevel)
    {
        $content = "WIFI:T:$security;S:$ssid;P:$password;;";
        return $this->generate($content, $size, $errorLevel);
    }

    public function generateGeo($lat, $lon, $size, $errorLevel)
    {
        $content = "geo:$lat,$lon";
        return $this->generate($content, $size, $errorLevel);
    }
}