<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('rel="canonical"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('max-image-preview:large', false)
            ->assertSee('جميع مناطق المملكة', false)
            ->assertSee('RedBox', false);
    }

    public function test_the_public_sitemap_is_available(): void
    {
        $response = $this->get('/sitemap.xml');

        $response
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee(route('public.home'), false);
    }

    public function test_stationery_images_are_served_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('stationery-products/test-product.png', 'image-content');

        $response = $this->get('/stationery-images/test-product.png');

        $response->assertStatus(200);
        $this->assertSame('image-content', $response->streamedContent());
        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));

        $this->get('/stationery-images/missing-product.png')->assertNotFound();
    }

    public function test_public_showcase_images_have_a_reliable_laravel_url(): void
    {
        $this->get('/showcase-images/mobile')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');

        $this->get('/showcase-images/desktop')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');

        $this->get('/showcase-images/unknown')->assertNotFound();
    }
}
