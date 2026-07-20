<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Services\LivePageUpdateService;
use App\Services\ServicePricingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
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

    public function test_live_status_is_private(): void
    {
        $this->get('/live-status')->assertRedirect('/login');
    }

    public function test_cart_view_renders_with_service_pricing(): void
    {
        $this->actingAs(User::factory()->make());
        $this->app->instance(LivePageUpdateService::class, new class extends LivePageUpdateService
        {
            public function snapshot(User $user): array
            {
                return [
                    'revision' => 'test-revision',
                    'orders_count' => 0,
                    'unseen_count' => 0,
                    'role' => 'customer',
                    'pricing_revision' => 'test-pricing-revision',
                ];
            }
        });

        $servicePricing = collect(ServicePricingService::DEFINITIONS)
            ->mapWithKeys(fn (array $definition, string $key) => [$key => (float) $definition['default']])
            ->all();

        $this->view('cart.show', [
            'cartOrders' => collect(),
            'cartSummary' => [
                'orders_count' => 0,
                'files_count' => 0,
                'products_count' => 0,
                'print_total' => 0,
                'binding_total' => 0,
                'cd_total' => 0,
                'discount_amount' => 0,
                'delivery_fee' => 0,
                'grand_total' => 0,
            ],
            'errors' => new ViewErrorBag,
            'servicePricing' => $servicePricing,
            'paymentPage' => false,
        ])->assertSee('السلة فارغة');
    }

    public function test_paid_and_unpaid_admin_order_pages_are_private(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->get('/admin/orders/unpaid')->assertRedirect('/login');
    }
}
