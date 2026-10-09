<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    /**
     * Render captcha 5 karakter sebagai gambar PNG (GD).
     * Kode disimpan di session dan bersifat sekali pakai.
     */
    public function show()
    {
        // Karakter tanpa yang mudah tertukar (0/O, 1/I/L)
        $chars  = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $code   = substr(str_shuffle(str_repeat($chars, 5)), 0, 5);

        session(['captcha_code' => $code]);

        $width  = 190;
        $height = 64;

        $img = imagecreatetruecolor($width, $height);

        // Gradien latar
        $bg1 = imagecolorallocate($img, 15, 23, 42);   // slate-900
        $bg2 = imagecolorallocate($img, 30, 41, 59);   // slate-800
        for ($x = 0; $x < $width; $x++) {
            $r = (int) (15 + (30 - 15) * $x / $width);
            $g = (int) (23 + (41 - 23) * $x / $width);
            $b = (int) (42 + (59 - 42) * $x / $width);
            imageline($img, $x, 0, $x, $height, imagecolorallocate($img, $r, $g, $b));
        }

        // Bintik noise
        for ($i = 0; $i < 120; $i++) {
            imagesetpixel($img, rand(0, $width - 1), rand(0, $height - 1),
                imagecolorallocate($img, rand(80, 160), rand(90, 170), rand(140, 220)));
        }

        // Garis-garis pengacau
        $colors = [
            imagecolorallocate($img, 56, 189, 248),   // sky
            imagecolorallocate($img, 52, 211, 153),   // emerald
            imagecolorallocate($img, 167, 139, 250),  // violet
            imagecolorallocate($img, 251, 191, 36),   // amber
        ];
        for ($i = 0; $i < 5; $i++) {
            imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height),
                $colors[array_rand($colors)]);
        }

        // Karakter: ukuran, rotasi & warna acak per huruf
        $font = $this->fontPath();
        $len  = strlen($code);
        $slot = ($width - 24) / $len;
        foreach (str_split($code) as $i => $char) {
            $size  = rand(24, 30);
            $angle = rand(-22, 22);
            $x     = 14 + $slot * $i + rand(-3, 3);
            $y     = $height / 2 + $size / 2.6 + rand(-4, 4);
            $color = $colors[array_rand($colors)];

            if ($font !== '') {
                imagettftext($img, $size, $angle, (int) $x, (int) $y, $color, $font, $char);
            } else {
                // Fallback internal bitmap font (tanpa TTF)
                imagestring($img, 5, (int) $x, (int) ($height / 2 - 14), $char, $color);
            }
        }

        return response()->stream(function () use ($img) {
            imagepng($img);
            imagedestroy($img);
        }, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }

    private function fontPath(): string
    {
        // Prioritas: font TTF sistem; fallback font internal GD
        $candidates = [
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            '/System/Library/Fonts/Supplemental/Comic Sans MS.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($candidates as $font) {
            if (is_readable($font)) {
                return $font;
            }
        }

        return ''; // tanpa TTF → fallback imagestring
    }
}
