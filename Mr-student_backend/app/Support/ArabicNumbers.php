<?php

namespace App\Support;

use Illuminate\Http\Request;

class ArabicNumbers
{
    public static function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => self::normalize($item), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        return strtr($value, [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);
    }

    public static function normalizeRequest(Request $request, array $keys): void
    {
        $normalized = [];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $normalized[$key] = self::normalize($request->input($key));
            }
        }

        if ($normalized !== []) {
            $request->merge($normalized);
        }
    }
}
