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

        $stillMissing = array_values(array_filter($missing, fn (string $text) => ! isset($translations[$text])));
        if ($stillMissing !== [] && config('services.google_keyless_translation.enabled', true)) {
            $translations += $this->translateWithGoogleKeyless($stillMissing, $sourceLanguage, $targetLanguage);
        }

        return $translations;
    }

    public function isConfigured(): bool
    {
        return $this->googleIsConfigured()
            || (bool) config('services.mymemory_translation.enabled', true)
            || (bool) config('services.google_keyless_translation.enabled', true);
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
                $partsByText = collect($batch)->map(fn (string $text) => $this->splitForFallback($text))->all();
                $responses = Http::pool(fn (Pool $pool) => collect($partsByText)
                    ->flatMap(fn (array $parts, int $textIndex) => collect($parts)->mapWithKeys(fn (string $part, int $partIndex) => [
                        $textIndex.'-'.$partIndex => $pool->as($textIndex.'-'.$partIndex)
                            ->acceptJson()
                            ->timeout($timeout)
                            ->get($endpoint, [
                                'q' => $part,
                                'langpair' => $sourceLanguage.'|'.$targetLanguage,
                            ]),
                    ]))->all());

                foreach ($batch as $index => $sourceText) {
                    $translatedParts = [];
                    foreach ($partsByText[$index] as $partIndex => $part) {
                        $response = $responses[$index.'-'.$partIndex] ?? null;
                        if (! $response || ! $response->successful()) {
                            $translatedParts = [];
                            break;
                        }
                        $translatedPart = html_entity_decode(
                            trim((string) $response->json('responseData.translatedText', '')),
                            ENT_QUOTES | ENT_HTML5,
                            'UTF-8'
                        );
                        if ($translatedPart === '') {
                            $translatedParts = [];
                            break;
                        }
                        $translatedParts[] = $translatedPart;
                    }
                    $translated = trim(implode(' ', $translatedParts));
                    $wrongScript = ($targetLanguage === 'en' && preg_match('/[\x{0600}-\x{06FF}]/u', $translated))
                        || ($targetLanguage === 'ar' && ! preg_match('/[\x{0600}-\x{06FF}]/u', $translated));
                    if ($translated === '' || $wrongScript) {
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

    /**
     * A second keyless provider prevents a temporary MyMemory limit or outage
     * from leaving one language column in the customer's original language.
     *
     * @param  array<int, string>  $texts
     * @return array<string, string>
     */
    private function translateWithGoogleKeyless(array $texts, string $sourceLanguage, string $targetLanguage): array
    {
        $translations = [];
        $endpoint = (string) config('services.google_keyless_translation.endpoint');
        $timeout = (int) config('services.google_keyless_translation.timeout', 8);

        foreach (array_chunk($texts, 20) as $batch) {
            try {
                $partsByText = collect($batch)->map(fn (string $text) => $this->splitForFallback($text))->all();
                $responses = Http::pool(fn (Pool $pool) => collect($partsByText)
                    ->flatMap(fn (array $parts, int $textIndex) => collect($parts)->mapWithKeys(fn (string $part, int $partIndex) => [
                        $textIndex.'-'.$partIndex => $pool->as($textIndex.'-'.$partIndex)
                            ->acceptJson()
                            ->timeout($timeout)
                            ->get($endpoint, [
                                'client' => 'gtx',
                                'sl' => $sourceLanguage,
                                'tl' => $targetLanguage,
                                'dt' => 't',
                                'q' => $part,
                            ]),
                    ]))->all());

                foreach ($batch as $index => $sourceText) {
                    $translatedParts = [];
                    foreach ($partsByText[$index] as $partIndex => $part) {
                        $response = $responses[$index.'-'.$partIndex] ?? null;
                        if (! $response || ! $response->successful()) {
                            $translatedParts = [];
                            break;
                        }
                        $segments = $response->json('0', []);
                        $translatedPart = trim(collect(is_array($segments) ? $segments : [])
                            ->map(fn ($segment) => is_array($segment) ? (string) ($segment[0] ?? '') : '')
                            ->implode(''));
                        if ($translatedPart === '') {
                            $translatedParts = [];
                            break;
                        }
                        $translatedParts[] = $translatedPart;
                    }
                    $translated = trim(implode(' ', $translatedParts));
                    $wrongScript = ($targetLanguage === 'en' && preg_match('/[\x{0600}-\x{06FF}]/u', $translated))
                        || ($targetLanguage === 'ar' && ! preg_match('/[\x{0600}-\x{06FF}]/u', $translated));
                    if ($translated === '' || $wrongScript) {
                        continue;
                    }

                    $this->storeTranslation($translations, $sourceText, $translated, $sourceLanguage, $targetLanguage);
                }
            } catch (Throwable $exception) {
                Log::warning('Keyless Google automatic translation fallback failed.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        return $translations;
    }

    /** @return array<int, string> */
    private function splitForFallback(string $text): array
    {
        if (strlen($text) <= 450) {
            return [$text];
        }

        $parts = [];
        $current = '';
        foreach (preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [$text] as $token) {
            if ($current !== '' && strlen($current.$token) > 450) {
                $parts[] = trim($current);
                $current = '';
            }
            if (strlen($token) > 450) {
                while ($token !== '') {
                    $chunk = mb_strcut($token, 0, 450, 'UTF-8');
                    $parts[] = trim($chunk);
                    $token = (string) mb_strcut($token, strlen($chunk), null, 'UTF-8');
                }
                continue;
            }
            $current .= $token;
        }
        if (trim($current) !== '') {
            $parts[] = trim($current);
        }

        return array_values(array_filter($parts));
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
