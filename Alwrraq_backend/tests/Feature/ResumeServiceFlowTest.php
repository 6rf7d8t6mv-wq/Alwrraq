<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ResumeDraft;
use App\Models\ServiceDefinition;
use App\Models\User;
use App\Services\ResumeDocumentService;
use App\Services\ServicePricingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeServiceFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->timestamps();
        });
        Schema::create('service_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->string('image_path')->nullable();
            $table->string('workflow_type');
            $table->boolean('requires_file')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('service_price_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->decimal('value', 12, 4);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('service_definition_id')->nullable();
            $table->string('service_type');
            $table->string('status');
            $table->string('payment_status');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('print_total', 10, 2)->default(0);
            $table->decimal('binding_total', 10, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('discount_applied_by')->nullable();
            $table->timestamp('discount_applied_at')->nullable();
            $table->string('delivery_method')->nullable();
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamp('customer_notification_seen_at')->nullable();
            $table->timestamps();
        });
        Schema::create('stationery_products', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
        Schema::create('order_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('file_type')->nullable();
            $table->string('image_print_type')->nullable();
            $table->unsignedInteger('pages')->default(1);
            $table->unsignedInteger('copies')->default(1);
            $table->string('print_sides')->nullable();
            $table->string('page_size')->nullable();
            $table->string('paper_color')->nullable();
            $table->decimal('print_price', 10, 2)->default(0);
            $table->decimal('binding_price', 10, 2)->default(0);
            $table->decimal('cd_price', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('order_product_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('order_delivered_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('customer_downloaded_at')->nullable();
            $table->timestamps();
        });
        Schema::create('resume_drafts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('template_id')->default('executive_classic');
            $table->string('language', 2)->default('ar');
            $table->json('content')->nullable();
            $table->json('section_order')->nullable();
            $table->json('hidden_sections')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('draft');
            $table->string('pdf_path')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('resume_drafts');
        Schema::dropIfExists('order_delivered_files');
        Schema::dropIfExists('order_product_items');
        Schema::dropIfExists('order_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('service_price_settings');
        Schema::dropIfExists('service_definitions');
        Schema::dropIfExists('stationery_products');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_resume_price_defaults_to_five_riyals_and_uses_the_saved_admin_price(): void
    {
        $service = ServiceDefinition::query()->create([
            'code' => 'resume-price-test',
            'title' => 'إنشاء سيرة ذاتية احترافية',
            'description' => 'خدمة سيرة ذاتية',
            'workflow_type' => 'resume',
            'requires_file' => false,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 85,
        ]);
        $pricing = app(ServicePricingService::class);

        $this->assertSame(5.0, $pricing->customServicePrice($service));

        $pricing->updateCustomServicePrices([$service->id => 12.75], 1);

        $this->assertSame(12.75, app(ServicePricingService::class)->customServicePrice($service->refresh()));
    }

    public function test_unpaid_resume_preview_has_strong_watermark_and_no_download_buttons(): void
    {
        [$user, $draft] = $this->createDraft('unpaid');

        $this->actingAs($user)
            ->get(route('resume.preview', $draft))
            ->assertOk()
            ->assertSee('معاينة غير مدفوعة — الورّاق')
            ->assertSee('معاينة محمية')
            ->assertDontSee('تحميل السيرة الذاتية PDF');
    }

    public function test_resume_editor_renders_all_requested_sections(): void
    {
        [$user, $draft] = $this->createDraft('unpaid');

        $this->actingAs($user)
            ->get(route('resume.edit', $draft))
            ->assertOk()
            ->assertSee('المعلومات الشخصية')
            ->assertSee('المؤهلات العلمية')
            ->assertSee('الخبرات العملية')
            ->assertSee('الدورات والشهادات')
            ->assertSee('العمل التطوعي')
            ->assertSee('الحقول المعلّمة بنجمة حمراء إلزامية')
            ->assertSee('validateCurrentStep')
            ->assertSee('reportValidity')
            ->assertSee('إضافة إلى السلة — 5 ريال')
            ->assertSee('الرجوع للخدمات')
            ->assertSee('executive_classic')
            ->assertSee('royal_gold')
            ->assertSee('midnight_luxury')
            ->assertSee('emerald_signature')
            ->assertSee('modern_silk');
    }

    public function test_resume_preview_displays_complete_personal_details_and_sparse_layout(): void
    {
        [$user, $draft] = $this->createDraft('unpaid');
        $content = $draft->content;
        $content['personal'] = array_merge($content['personal'], [
            'email' => 'resume@example.com',
            'city' => 'المدينة المنورة',
            'country' => 'المملكة العربية السعودية',
            'birth_date' => '2003-01-06',
            'nationality' => 'سعودي',
            'marital_status' => 'أعزب',
            'linkedin' => 'https://www.linkedin.com/in/resume',
            'website' => 'https://example.com',
        ]);
        $draft->forceFill(['content' => $content])->save();

        $this->actingAs($user)
            ->get(route('resume.preview', $draft))
            ->assertOk()
            ->assertSee('content-sparse')
            ->assertSee('2003-01-06')
            ->assertSee('سعودي')
            ->assertSee('أعزب')
            ->assertSee('المدينة المنورة')
            ->assertSee('resume@example.com')
            ->assertSee('https://www.linkedin.com/in/resume')
            ->assertSee('https://example.com');
    }

    public function test_long_resume_name_is_kept_on_one_adaptive_line(): void
    {
        [$user, $draft] = $this->createDraft('paid');
        $content = $draft->content;
        data_set($content, 'personal.full_name', 'عبدالمحسن عمر الحجيلي');
        $draft->forceFill(['content' => $content])->save();

        $this->actingAs($user)
            ->get(route('resume.preview', $draft))
            ->assertOk()
            ->assertSee('cv-name-long', false)
            ->assertSee('white-space:nowrap', false);
    }

    public function test_resume_draft_is_private_to_its_owner(): void
    {
        [, $draft] = $this->createDraft('unpaid');
        $other = User::query()->create([
            'name' => 'Other Customer',
            'phone' => '0511111111',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($other)->get(route('resume.preview', $draft))->assertForbidden();
    }

    public function test_resume_can_be_edited_before_payment_but_not_after_payment(): void
    {
        [$user, $unpaidDraft] = $this->createDraft('unpaid');

        $this->actingAs($user)
            ->get(route('resume.edit', $unpaidDraft))
            ->assertOk()
            ->assertSee('إضافة إلى السلة — 5 ريال');

        $unpaidDraft->order->forceFill(['payment_status' => 'paid'])->save();

        $this->actingAs($user)
            ->get(route('resume.edit', $unpaidDraft))
            ->assertRedirect(route('resume.preview', $unpaidDraft));

        $this->actingAs($user)
            ->patchJson(route('resume.update', $unpaidDraft), [
                'template_id' => 'royal_gold',
                'language' => 'ar',
                'content' => $unpaidDraft->content,
                'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
                'hidden_sections' => [],
            ])
            ->assertStatus(409);
    }

    public function test_resume_preview_opened_from_cart_returns_to_cart_and_hides_editing_after_payment(): void
    {
        [$user, $draft] = $this->createDraft('paid');

        $this->actingAs($user)
            ->get(route('resume.preview', ['resumeDraft' => $draft, 'from' => 'cart']))
            ->assertOk()
            ->assertSee('الرجوع للسلة')
            ->assertSee(route('cart.index'), false)
            ->assertDontSee('العودة لتعديل السيرة الذاتية');
    }

    public function test_admin_resume_preview_stays_in_admin_context_and_is_read_only(): void
    {
        [, $draft] = $this->createDraft('paid');
        $admin = User::query()->create([
            'name' => 'Resume Admin',
            'phone' => '0511111111',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get(route('resume.preview', ['resumeDraft' => $draft, 'from' => 'admin']))
            ->assertOk()
            ->assertSee('الرجوع لطلبات الإدارة')
            ->assertSee(route('admin.orders', ['open_order' => $draft->order_id]), false)
            ->assertSee('معاينة السيرة الذاتية من لوحة الإدارة')
            ->assertDontSee('العودة لتعديل السيرة الذاتية')
            ->assertDontSee('إنشاء وتحميل السيرة كصورة');
    }

    public function test_customer_can_save_any_available_luxury_template(): void
    {
        [$user, $draft] = $this->createDraft('unpaid');

        foreach (array_keys(ResumeDraft::TEMPLATES) as $templateId) {
            $this->actingAs($user)
                ->patchJson(route('resume.update', $draft), [
                    'template_id' => $templateId,
                    'language' => 'ar',
                    'content' => $draft->content,
                    'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
                    'hidden_sections' => [],
                ])
                ->assertOk();

            $this->assertSame($templateId, $draft->refresh()->template_id);
        }
    }

    public function test_paid_resume_can_be_generated_as_a_real_pdf(): void
    {
        Storage::fake('local');
        [$user, $draft] = $this->createDraft('paid');

        $response = $this->actingAs($user)->get(route('resume.download.pdf', $draft));

        $response
            ->assertOk()
            ->assertDownload('professional-resume-'.$draft->id.'.pdf');
        $draft->refresh();
        $this->assertNotNull($draft->pdf_path);
        Storage::disk('local')->assertExists($draft->pdf_path);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($draft->pdf_path));
    }

    public function test_resume_checkout_uses_configured_price_and_full_discount_confirmation(): void
    {
        Storage::fake('local');
        $user = User::query()->create([
            'name' => 'Resume Customer',
            'phone' => '0500000000',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $service = ServiceDefinition::query()->create([
            'code' => 'resume',
            'title' => 'إنشاء سيرة ذاتية احترافية',
            'description' => 'خدمة سيرة ذاتية',
            'workflow_type' => 'resume',
            'requires_file' => false,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 85,
        ]);
        DB::table('service_price_settings')->insert([
            'key' => 'service_definition_'.$service->id.'_price',
            'value' => 7.5,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $draft = ResumeDraft::query()->create([
            'user_id' => $user->id,
            'template_id' => 'executive_classic',
            'language' => 'ar',
            'content' => [
                'personal' => [
                    'full_name' => 'عميل السيرة',
                    'job_title' => 'مهندس',
                    'phone' => '0500000000',
                ],
            ],
            'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
            'hidden_sections' => [],
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('resume.landing'))
            ->assertOk()
            ->assertSee('7.5 ريال');
        $this->actingAs($user)
            ->get(route('resume.edit', $draft))
            ->assertOk()
            ->assertSee('إضافة إلى السلة — 7.5 ريال');

        $response = $this->actingAs($user)->post(route('resume.checkout', $draft));

        $response->assertRedirect(route('cart.index'));
        $draft->refresh();
        $this->assertNotNull($draft->order_id);
        $this->assertSame('pending_payment', $draft->status);
        $order = Order::query()->findOrFail($draft->order_id);
        $this->assertSame($service->id, $order->service_definition_id);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame(7.5, (float) $order->grand_total);
        $this->assertSame(1, Order::query()->where('service_type', 'resume')->count());

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('إنشاء سيرة ذاتية احترافية')
            ->assertSee('التنفيذي الفاخر')
            ->assertSee('from=cart', false)
            ->assertSee('تطبيق الخصم');

        $order->forceFill([
            'discount_code' => 'FULL5',
            'discount_amount' => 7.5,
            'discount_applied_at' => now(),
            'grand_total' => 0,
        ])->save();

        $confirmation = $this->actingAs($user)->post(route('cart.free.confirm'), [
            'order_ids' => [$order->id],
        ]);

        $confirmation->assertRedirect(route('orders.index', ['open_order' => $order->id]));
        $order->refresh();
        $draft->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('full_discount', $order->payment_method);
        $this->assertSame(0.0, (float) $order->grand_total);
        $this->assertSame('completed', $order->status);
        $this->assertNotNull($draft->pdf_path);
        Storage::disk('local')->assertExists($draft->pdf_path);

        $this->mock(ResumeDocumentService::class, function ($mock): void {
            $mock->shouldReceive('ensurePdf')->once()->andThrow(new \RuntimeException('Simulated renderer failure.'));
        });

        $this->actingAs($user)
            ->post(route('cart.free.confirm'), ['order_ids' => [$order->id]])
            ->assertRedirect(route('orders.index', ['open_order' => $order->id]))
            ->assertSessionHas('status');
        $this->assertSame(1, Order::query()->where('service_type', 'resume')->count());
    }

    /**
     * @return array{User, ResumeDraft}
     */
    private function createDraft(string $paymentStatus): array
    {
        $user = User::query()->create([
            'name' => 'Resume Customer',
            'phone' => '0500000000',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'service_type' => 'resume',
            'status' => 'new',
            'payment_status' => $paymentStatus,
            'grand_total' => 5,
        ]);
        $draft = ResumeDraft::query()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'template_id' => 'executive_classic',
            'language' => 'ar',
            'content' => [
                'personal' => [
                    'full_name' => 'عميل السيرة',
                    'job_title' => 'مهندس',
                    'phone' => '0500000000',
                ],
            ],
            'section_order' => ResumeDraft::DEFAULT_SECTION_ORDER,
            'hidden_sections' => [],
            'status' => 'pending_payment',
        ]);

        return [$user, $draft];
    }
}
