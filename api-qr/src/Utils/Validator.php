<?php
namespace App\Utils;

class Validator
{
    public static function validateSize(int $size): bool
    {
        return $size >= 100 && $size <= 1000;
    }

    public static function validateErrorLevel(string $level): bool
    {
        return in_array($level, ['L','M','Q','H']);
    }

    public static function validateURL(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function validateCoordinates($lat, $lon): bool
    {
        return is_numeric($lat) && is_numeric($lon)
            && $lat >= -90 && $lat <= 90
            && $lon >= -180 && $lon <= 180;
    }

    public static function validateWifiSecurity(string $security): bool
    {
        return in_array($security, ['WPA','WPA2','WEP','nopass']);
    }
}