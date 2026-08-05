<?php

namespace App\Http\Controllers;

use App\Services\AutomaticTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AutomaticTranslationController extends Controller
{
    public function __invoke(Request $request, AutomaticTranslationService $translator): JsonResponse
    {
        $validated = $request->validate([
            'texts' => ['required', 'array', 'max:50'],
            'texts.*' => ['required', 'string', 'max:1000'],
            'proper_names' => ['sometimes', 'array', 'max:100'],
            'proper_names.*' => ['required', 'string', 'max:150'],
        ]);

        $texts = array_values(array_unique($validated['texts']));

        if (array_sum(array_map(fn (string $text) => mb_strlen($text), $texts)) > 10000) {
            throw ValidationException::withMessages([
                'texts' => 'Translation request text is too long.',
            ]);
        }

        return response()->json([
            'translations' => $translator->translateInterfaceTexts($texts, $validated['proper_names'] ?? []),
            'configured' => $translator->isConfigured(),
        ]);
    }
}
