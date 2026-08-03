<?php

namespace Tests\Feature;

use App\Services\AutomaticTranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutomaticTranslationServiceTest extends TestCase
{
    public function test_it_translates_arabic_semantically_and_caches_the_result(): void
    {
        config([
            'cache.default' => 'array',
            'services.google_translation.api_key' => 'test-key',
            'services.google_translation.endpoint' => 'https://translation.googleapis.com/language/translate/v2',
            'services.google_translation.timeout' => 2,
        ]);

        Cache::flush();
        Http::fake([
            'translation.googleapis.com/*' => Http::response([
                'data' => [
                    'translations' => [
                        ['translatedText' => 'Book'],
                        ['translatedText' => 'New service &amp; delivery'],
                    ],
                ],
            ]),
        ]);

        $translator = app(AutomaticTranslationService::class);

        $this->assertSame([
            'كتاب' => 'Book',
            'خدمة جديدة وتوصيل' => 'New service & delivery',
        ], $translator->translateArabicToEnglish([
            'كتاب',
            'خدمة جديدة وتوصيل',
        ]));

        Http::assertSentCount(1);

        $this->assertSame([
            'كتاب' => 'Book',
        ], $translator->translateArabicToEnglish(['كتاب']));

        Http::assertSentCount(1);
    }

    public function test_it_keeps_the_site_working_when_no_api_key_is_configured(): void
    {
        config([
            'cache.default' => 'array',
            'services.google_translation.api_key' => null,
            'services.mymemory_translation.enabled' => false,
            'services.google_keyless_translation.enabled' => false,
        ]);

        Cache::flush();
        Http::fake();

        $translator = app(AutomaticTranslationService::class);

        $this->assertSame([], $translator->translateArabicToEnglish(['خدمة جديدة']));
        Http::assertNothingSent();
    }

    public function test_the_public_translation_endpoint_fails_safely_without_a_key(): void
    {
        config([
            'cache.default' => 'array',
            'services.google_translation.api_key' => null,
            'services.mymemory_translation.enabled' => false,
            'services.google_keyless_translation.enabled' => false,
        ]);

        $this->postJson(route('language.translate'), [
            'texts' => ['خدمة جديدة'],
        ])->assertOk()->assertExactJson([
            'translations' => [],
            'configured' => false,
        ]);
    }

    public function test_it_uses_the_keyless_semantic_fallback_when_google_has_no_key(): void
    {
        config([
            'cache.default' => 'array',
            'services.google_translation.api_key' => null,
            'services.mymemory_translation.enabled' => true,
            'services.mymemory_translation.endpoint' => 'https://api.mymemory.translated.net/get',
        ]);
        Cache::flush();
        Http::fake([
            'api.mymemory.translated.net/*' => Http::response([
                'responseStatus' => 200,
                'responseData' => ['translatedText' => 'Book'],
            ]),
        ]);

        $this->assertSame(
            ['كتاب' => 'Book'],
            app(AutomaticTranslationService::class)->translateArabicToEnglish(['كتاب'])
        );
        $this->assertTrue(app(AutomaticTranslationService::class)->isConfigured());
    }
}
