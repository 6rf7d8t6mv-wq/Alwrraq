<?php

namespace App\Http\Controllers;

class PublicAssetController extends Controller
{
    private const SHOWCASE_IMAGES = [
        'desktop' => 'alwrraq-desktop-preview.png',
        'mobile' => 'alwrraq-mobile-preview.png',
    ];

    public function showcase(string $device)
    {
        abort_unless(isset(self::SHOWCASE_IMAGES[$device]), 404);

        $filename = self::SHOWCASE_IMAGES[$device];
        $localPath = public_path('images/'.$filename);

        if (is_file($localPath)) {
            return response()->file($localPath, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return redirect()->away(
            'https://raw.githubusercontent.com/6rf7d8t6mv-wq/Alwrraq/main/Alwrraq_backend/public/images/'.$filename,
            302,
            ['Cache-Control' => 'no-cache']
        );
    }
}
