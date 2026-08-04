<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\ServiceDefinition;
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
            ->assertSee('RedBox', false)
            ->assertSee('الطباعة والتصوير وتجليد الرسائل', false)
            ->assertSee('داخل الجامعة الإسلامية', false)
            ->assertSee('من ساعة إلى 3 ساعات عمل', false)
            ->assertSee('من 3 إلى 8 أيام عمل', false);
    }

    public function test_english_homepage_is_ltr_and_prevents_translated_copy_overflow(): void
    {
        $response = $this->withSession(['ui_locale' => 'en'])->get('/');

        $response
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('html[dir="ltr"] .hero-title-line { white-space: normal; }', false)
            ->assertSee('overflow-x: hidden; overflow-x: clip;', false)
            ->assertSee('html[dir="ltr"] .nav { direction: rtl; }', false)
            ->assertSee('html[dir="ltr"] .nav > * { direction: ltr; }', false)
            ->assertSee('html[dir="ltr"] .nav-actions { order: 2; }', false)
            ->assertSee('html[dir="ltr"] .nav-links { order: 3; }', false)
            ->assertSee('@media (min-width: 901px)', false)
            ->assertSee('html[dir="ltr"] .nav-links { grid-column: 1; grid-row: 1; justify-self: start; }', false)
            ->assertSee('html[dir="ltr"] .brand { grid-column: 3; grid-row: 1; justify-self: end; }', false)
            ->assertSee('@media (max-width: 560px)', false)
            ->assertSee('html[dir="ltr"] .nav { direction: ltr; }', false)
            ->assertSee('html[dir="ltr"] .hero-grid', false)
            ->assertSee('overflow-wrap: anywhere;', false)
            ->assertSee("const brandToken = 'ALWRRAQBRANDTOKEN';", false)
            ->assertSee("new RegExp(brandToken, 'gi'), 'Alwrraq'", false)
            ->assertSee("alwrraq-ui-translations-v3", false)
            ->assertSee('@media (min-width: 821px)', false)
            ->assertSee('html[dir="ltr"] body.customer-app-page .page-header', false)
            ->assertSee('left: 0 !important;', false)
            ->assertSee('padding-left: calc(var(--sidebar-width) + var(--page-gap)) !important;', false)
            ->assertSee('html[dir="ltr"] body.customer-app-page .header-actions > *', false)
            ->assertSee('html[dir="ltr"] body.customer-app-page .header-actions .language-switcher-button', false)
            ->assertSee('direction: ltr !important;', false);
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

    public function test_service_images_are_served_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('service-images/test-service.png', 'service-image-content');

        $response = $this->get('/service-images/test-service.png');

        $response->assertStatus(200);
        $this->assertSame('service-image-content', $response->streamedContent());
        $this->assertStringContainsString('max-age=31536000', (string) $response->headers->get('Cache-Control'));

        $this->get('/service-images/missing-service.png')->assertNotFound();
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

    public function test_public_app_revision_is_available_without_cache(): void
    {
        $response = $this->get('/app-revision');

        $response
            ->assertOk()
            ->assertJsonStructure(['revision']);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
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

    public function test_custom_services_receive_an_automatic_default_price(): void
    {
        $customService = new ServiceDefinition();
        $customService->forceFill([
            'id' => 99,
            'is_system' => false,
        ]);

        $systemService = new ServiceDefinition();
        $systemService->forceFill([
            'id' => 1,
            'is_system' => true,
        ]);

        $pricing = app(ServicePricingService::class);

        $this->assertSame(1.0, $pricing->customServicePrice($customService));
        $this->assertSame(0.0, $pricing->customServicePrice($systemService));
    }

    public function test_books_service_uses_the_photocopying_and_natural_leather_title(): void
    {
        $expectedTitle = 'تصوير وتجليد الكتب كعب جلد طبيعي';

        $this->assertSame($expectedTitle, ServiceDefinition::WORKFLOWS['books']);
        $this->assertSame(
            $expectedTitle,
            ServicePricingService::DEFINITIONS['books_white_pages']['group']
        );
    }

    public function test_admin_order_pages_are_private(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->get('/admin/orders/unpaid')->assertRedirect('/login');
        $this->get('/admin/orders/cancelled')->assertRedirect('/login');
    }

    public function test_restricted_admin_cannot_access_ungranted_management_pages(): void
    {
        $admin = User::factory()->make([
            'role' => 'admin',
            'admin_permissions' => ['orders_view'],
        ]);

        $this->actingAs($admin);

        $this->get('/admin')->assertRedirect('/admin/orders');
        $this->get('/admin/services')->assertForbidden();
        $this->get('/admin/stationery-products')->assertForbidden();
        $this->get('/admin/service-pricing')->assertForbidden();
    }
}
