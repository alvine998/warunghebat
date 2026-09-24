<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'Warung Hebat — Belanja Dekat, Hidup Hebat',
            'short_name' => 'Warung Hebat',
            'description' => 'Marketplace digital mobile-first: belanja makanan, minuman & kebutuhan harian dari warung terdekatmu.',
            'lang' => 'id',
            'dir' => 'ltr',
            'id' => '/',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#FFF9EF',
            'theme_color' => '#1A130D',
            'categories' => ['food', 'shopping'],
            'icons' => [
                [
                    'src' => asset('icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
                [
                    'src' => asset('icons/maskable-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts' => [
                [
                    'name' => 'Warung Terdekat',
                    'url' => '/warung',
                    'icons' => [
                        ['src' => asset('icons/icon-192.png'), 'sizes' => '192x192'],
                    ],
                ],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json; charset=UTF-8'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function offline(): View
    {
        return view('offline');
    }
}
