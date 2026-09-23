<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Draws a small abstract PNG so seeded records have real image files instead of
 * broken <img> tags in the storefront, wallet and backoffice screens.
 */
class PlaceholderImage
{
    public static function put(string $path, string $hex, int $width = 640, int $height = 420): string
    {
        [$red, $green, $blue] = sscanf($hex, '#%02x%02x%02x');

        $image = imagecreatetruecolor($width, $height);

        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, $red, $green, $blue));

        $light = imagecolorallocatealpha($image, 255, 255, 255, 45);
        imagefilledellipse($image, (int) ($width * 0.3), (int) ($height * 0.3), (int) ($width * 0.55), (int) ($width * 0.55), $light);

        $dark = imagecolorallocatealpha($image, 26, 19, 13, 70);
        imagefilledellipse($image, (int) ($width * 0.8), (int) ($height * 0.8), (int) ($width * 0.36), (int) ($width * 0.36), $dark);

        ob_start();
        imagepng($image);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
