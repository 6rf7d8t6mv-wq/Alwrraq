<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ServiceDefinition;
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
            ->assertSee('RedBox', false)
            ->assertSee('الطباعة والتصوير وتجليد الرسائل', false)
            ->assertSee('داخل الجامعة الإسلامية', false)
            ->assertSee('من ساعة إلى 3 ساعات عمل', false)
            ->assertSee('من 3 إلى 8 أيام عمل', false)
            ->assertSee(route('public.privacy'), false)
            ->assertSee(route('public.terms'), false)
            ->assertSee(route('public.refund'), false);
    }

    public function test_public_legal_pages_are_available_in_arabic_and_english(): void
    {
        $pages = [
            '/privacy-policy' => ['سياسة الخصوصية – تطبيق الورّاق', 'Privacy Policy – Alwrraq App', '13. التواصل معنا'],
            '/terms-and-conditions' => ['الشروط والأحكام – الورّاق', 'Terms and Conditions – Alwrraq', '15. التواصل معنا'],
            '/cancellation-and-refund-policy' => ['سياسة الإلغاء والاسترجاع – الورّاق', 'Cancellation and Refund Policy – Alwrraq', '10. التواصل معنا'],
        ];

        foreach ($pages as $path => [$arabicTitle, $englishTitle, $lastArabicSection]) {
            $this->withSession(['ui_locale' => 'ar'])
                ->get($path)
                ->assertOk()
                ->assertSee('<html lang="ar" dir="rtl">', false)
                ->assertSee($arabicTitle, false)
                ->assertSee('آخر تحديث: 9 أغسطس 2026', false)
                ->assertSee($lastArabicSection, false)
                ->assertSee(route('public.privacy'), false)
                ->assertSee(route('public.terms'), false)
                ->assertSee(route('public.refund'), false);

            $this->withSession(['ui_locale' => 'en'])
                ->get($path)
                ->assertOk()
                ->assertSee('<html lang="en" dir="ltr">', false)
                ->assertSee($englishTitle, false)
                ->assertSee('Last updated: August 9, 2026', false)
                ->assertSee('Contact Us', false);
        }
    }

    public function test_live_updates_do_not_render_connection_status_overlays(): void
    {
        $customerUpdates = file_get_contents(resource_path('views/shared/live-page-updates.blade.php'));
        $adminUpdates = file_get_contents(resource_path('views/shared/admin-live-updates.blade.php'));

        $this->assertStringNotContainsString('التحديث المباشر متصل', $customerUpdates);
        $this->assertStringNotContainsString('livePageIndicator', $customerUpdates);
        $this->assertStringNotContainsString('التحديث المباشر يعمل', $adminUpdates);
        $this->assertStringNotContainsString('adminLiveIndicator', $adminUpdates);
    }

    public function test_mobile_sidebar_is_shared_by_customer_and_admin_headers(): void
    {
        $sidebar = $this->view('shared.mobile-sidebar');

        $sidebar
            ->assertSee('mobile-sidebar-toggle', false)
            ->assertSee('mobile-sidebar-backdrop', false)
            ->assertSee("event.key === 'Escape'", false)
            ->assertSee("event.target.closest('a')", false);

        foreach ([
            'grades.blade.php',
            'account/settings.blade.php',
            'orders/index.blade.php',
            'cart/show.blade.php',
            'stationery/index.blade.php',
            'resume/_customer_header.blade.php',
            'admin/layout.blade.php',
        ] as $viewPath) {
            $source = file_get_contents(resource_path('views/'.$viewPath));
            $this->assertStringContainsString("@include('shared.mobile-sidebar')", $source);
        }
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
            ->assertSee('alwrraq-ui-translations-v4', false)
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
            ->assertSee(route('public.home'), false)
            ->assertSee(route('public.privacy'), false)
            ->assertSee(route('public.terms'), false)
            ->assertSee(route('public.refund'), false);
    }

    public function test_mobile_upload_inputs_use_strict_mime_filters(): void
    {
        $view = (string) file_get_contents(resource_path('views/grades.blade.php'));

        $this->assertSame(5, substr_count($view, 'accept="application/pdf"'));
        $this->assertSame(3, substr_count(
            $view,
            'accept="application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"'
        ));
        $this->assertSame(2, substr_count($view, 'accept="image/*"'));
        $this->assertStringNotContainsString('accept=".pdf"', $view);
        $this->assertStringNotContainsString('id="imagesFilesPicker" multiple', $view);
    }

    public function test_mobile_share_import_filters_services_and_receives_uploaded_files(): void
    {
        $view = (string) file_get_contents(resource_path('views/grades.blade.php'));

        $this->assertStringContainsString('data-share-workflow=', $view);
        $this->assertStringContainsString("notes: ['pdf']", $view);
        $this->assertStringContainsString("thesis: ['pdf', 'word']", $view);
        $this->assertStringContainsString("formatting: ['word']", $view);
        $this->assertStringContainsString("images: ['image']", $view);
        $this->assertStringContainsString('window.AlwrraqShareService.postMessage', $view);
        $this->assertStringContainsString('window.alwrraqReceiveSharedUpload', $view);
        $this->assertStringContainsString('window.alwrraqFinishSharedImport', $view);
    }

    public function test_registration_is_minimal_and_mobile_header_is_a_drawer(): void
    {
        $authView = (string) file_get_contents(resource_path('views/auth.blade.php'));
        $header = (string) file_get_contents(resource_path('views/shared/language-switcher.blade.php'));
        $services = (string) file_get_contents(resource_path('views/grades.blade.php'));

        $this->assertStringContainsString('name="first_name"', $authView);
        $this->assertStringContainsString('name="second_name"', $authView);
        $this->assertStringContainsString('name="phone"', $authView);
        $this->assertStringContainsString('name="password"', $authView);
        $this->assertStringContainsString('name="password_confirmation"', $authView);
        $this->assertStringNotContainsString('name="institution_name"', $authView);
        $this->assertStringNotContainsString('name="institution_not_interested"', $authView);
        $this->assertStringNotContainsString('name="email"', $authView);
        $this->assertStringContainsString('رقم الجوال أو البريد الإلكتروني', $authView);
        $this->assertStringContainsString('customer-menu-toggle', $header);
        $this->assertStringContainsString('customer-menu-backdrop', $header);
        $this->assertStringContainsString('خدمات الورّاق', $services);
        $this->assertStringNotContainsString('اختر الخدمة المطلوبة', $services);
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
        $customService = new ServiceDefinition;
        $customService->forceFill([
            'id' => 99,
            'is_system' => false,
        ]);

        $systemService = new ServiceDefinition;
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
