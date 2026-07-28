<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AutomaticTranslationService
{
    /**
     * @param  array<int, string>  $texts
     * @return array<string, string>
     */
    public function translateArabicToEnglish(array $texts): array
    {
        $texts = collect($texts)
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn (string $text) => $text !== '' && preg_match('/[\x{0600}-\x{06FF}]/u', $text))
            ->unique()
            ->take(128)
            ->values();

        $translations = [];
        $missing = [];

        foreach ($texts as $text) {
            $cached = Cache::get($this->cacheKey($text));

            if (is_string($cached) && $cached !== '') {
                $translations[$text] = $cached;
            } else {
                $missing[] = $text;
            }
        }

        if ($missing === [] || ! $this->isConfigured()) {
            return $translations;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.google_translation.timeout', 8))
                ->retry(1, 250)
                ->post(
                    rtrim((string) config('services.google_translation.endpoint'), '/').'?key='.urlencode((string) config('services.google_translation.api_key')),
                    [
                        'q' => $missing,
                        'source' => 'ar',
                        'target' => 'en',
                        'format' => 'text',
                    ]
                )
                ->throw();

            $translatedItems = $response->json('data.translations', []);

            foreach ($missing as $index => $source) {
                $translated = html_entity_decode(
                    trim((string) data_get($translatedItems, $index.'.translatedText', '')),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                if ($translated === '') {
                    continue;
                }

                $translations[$source] = $translated;
                Cache::forever($this->cacheKey($source), $translated);
            }
        } catch (Throwable $exception) {
            Log::warning('Automatic interface translation failed.', [
                'exception' => $exception::class,
            ]);
        }

        return $translations;
    }

    public function isConfigured(): bool
    {
        return filled(config('services.google_translation.api_key'))
            && filled(config('services.google_translation.endpoint'));
    }

    private function cacheKey(string $text): string
    {
        return 'ui-translation:ar:en:'.hash('sha256', $text);
    }
}
