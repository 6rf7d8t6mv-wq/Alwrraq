<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use Throwable;

class AutomaticTranslationService
{
    /**
     * @param  array<int, string>  $texts
     * @return array<string, string>
     */
    public function translateArabicToEnglish(array $texts): array
    {
        return $this->translate($texts, 'ar', 'en');
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<string, string>
     */
    public function translate(array $texts, string $sourceLanguage = 'ar', string $targetLanguage = 'en'): array
    {
        $texts = collect($texts)
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn (string $text) => $text !== '' && ($sourceLanguage !== 'ar' || preg_match('/[\x{0600}-\x{06FF}]/u', $text)))
            ->unique()
            ->take(128)
            ->values();

        $translations = [];
        $missing = [];

        foreach ($texts as $text) {
            $cached = Cache::get($this->cacheKey($text, $sourceLanguage, $targetLanguage));

            if (is_string($cached) && $cached !== '') {
                $translations[$text] = $cached;
            } else {
                $missing[] = $text;
            }
        }

        if ($missing === []) {
            return $translations;
        }

        if ($this->googleIsConfigured()) {
            try {
                $response = Http::acceptJson()
                    ->asJson()
                    ->timeout((int) config('services.google_translation.timeout', 8))
                    ->retry(1, 250)
                    ->post(
                        rtrim((string) config('services.google_translation.endpoint'), '/').'?key='.urlencode((string) config('services.google_translation.api_key')),
                        [
                            'q' => $missing,
                            'source' => $sourceLanguage,
                            'target' => $targetLanguage,
                            'format' => 'text',
                        ]
                    )
                    ->throw();

                $translatedItems = $response->json('data.translations', []);

                foreach ($missing as $index => $sourceText) {
                    $translated = html_entity_decode(
                        trim((string) data_get($translatedItems, $index.'.translatedText', '')),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );

                    if ($translated === '') {
                        continue;
                    }

                    $this->storeTranslation($translations, $sourceText, $translated, $sourceLanguage, $targetLanguage);
                }
            } catch (Throwable $exception) {
                Log::warning('Google interface translation failed; trying the keyless fallback.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        $stillMissing = array_values(array_filter($missing, fn (string $text) => ! isset($translations[$text])));
        if ($stillMissing !== [] && config('services.mymemory_translation.enabled', true)) {
            $translations += $this->translateWithMyMemory($stillMissing, $sourceLanguage, $targetLanguage);
        }

        return $translations;
    }

    public function isConfigured(): bool
    {
        return $this->googleIsConfigured()
            || (bool) config('services.mymemory_translation.enabled', true);
    }

    private function googleIsConfigured(): bool
    {
        return filled(config('services.google_translation.api_key'))
            && filled(config('services.google_translation.endpoint'));
    }

    /**
     * @param  array<int, string>  $texts
     * @return array<string, string>
     */
    private function translateWithMyMemory(array $texts, string $sourceLanguage, string $targetLanguage): array
    {
        $translations = [];
        $endpoint = (string) config('services.mymemory_translation.endpoint');
        $timeout = (int) config('services.mymemory_translation.timeout', 8);

        foreach (array_chunk($texts, 20) as $batch) {
            try {
                $responses = Http::pool(fn (Pool $pool) => collect($batch)
                    ->mapWithKeys(fn (string $text, int $index) => [
                        (string) $index => $pool->as((string) $index)
                            ->acceptJson()
                            ->timeout($timeout)
                            ->get($endpoint, [
                                'q' => mb_substr($text, 0, 490),
                                'langpair' => $sourceLanguage.'|'.$targetLanguage,
                            ]),
                    ])->all());

                foreach ($batch as $index => $sourceText) {
                    $response = $responses[(string) $index] ?? null;
                    if (! $response || ! $response->successful()) {
                        continue;
                    }

                    $translated = html_entity_decode(
                        trim((string) $response->json('responseData.translatedText', '')),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    );
                    if ($translated === '' || ($sourceLanguage === 'ar' && preg_match('/[\x{0600}-\x{06FF}]/u', $translated))) {
                        continue;
                    }

                    $this->storeTranslation($translations, $sourceText, $translated, $sourceLanguage, $targetLanguage);
                }
            } catch (Throwable $exception) {
                Log::warning('Keyless automatic translation fallback failed.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        return $translations;
    }

    /** @param array<string, string> $translations */
    private function storeTranslation(array &$translations, string $sourceText, string $translated, string $sourceLanguage, string $targetLanguage): void
    {
        $translations[$sourceText] = $translated;
        Cache::forever($this->cacheKey($sourceText, $sourceLanguage, $targetLanguage), $translated);
    }

    private function cacheKey(string $text, string $source, string $target): string
    {
        return "ui-translation:{$source}:{$target}:".hash('sha256', $text);
    }
}
