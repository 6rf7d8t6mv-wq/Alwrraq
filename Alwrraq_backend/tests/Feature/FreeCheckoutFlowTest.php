<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderFile;
use App\Models\User;
use App\Services\LivePageUpdateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreeCheckoutFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(LivePageUpdateService::class, new class extends LivePageUpdateService
        {
            public function snapshot(User $user): array
            {
                return [
                    'revision' => 'free-checkout-test',
                    'orders_count' => 1,
                    'unseen_count' => 0,
                    'role' => 'customer',
                    'pricing_revision' => 'free-checkout-pricing-test',
                ];
            }
        });

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
            $table->string('title')->nullable();
            $table->boolean('is_system')->default(true);
            $table->string('workflow_type')->nullable();
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
            $table->string('delivery_unit')->nullable();
            $table->string('delivery_floor')->nullable();
            $table->string('delivery_room')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_district')->nullable();
            $table->string('delivery_street')->nullable();
            $table->string('delivery_map_url')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
        Schema::create('order_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('file_type')->default('other');
            $table->string('image_print_type')->nullable();
            $table->unsignedInteger('pages')->default(1);
            $table->unsignedInteger('copies')->default(1);
            $table->string('print_sides')->nullable();
            $table->string('page_size')->nullable();
            $table->string('paper_color')->nullable();
            $table->string('thesis_project_type')->nullable();
            $table->string('cover_color')->nullable();
            $table->string('writing_color')->nullable();
            $table->string('binding_type')->nullable();
            $table->decimal('print_price', 10, 2)->default(0);
            $table->decimal('binding_price', 10, 2)->default(0);
            $table->decimal('cd_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('order_product_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('stationery_product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('product_type')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('order_delivered_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('original_name');
            $table->string('path');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('order_delivered_files');
        Schema::dropIfExists('order_product_items');
        Schema::dropIfExists('order_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('service_definitions');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_a_fully_discounted_order_is_confirmed_once_without_moyasar(): void
    {
        [$user, $order] = $this->createOrder(10);

        $paymentPage = $this->actingAs($user)->get(route('cart.payment', [
            'order_ids' => [$order->id],
        ]));

        $paymentPage
            ->assertOk()
            ->assertSee('تأكيد الطلب')
            ->assertSee('تمت تغطية المبلغ بالكامل بواسطة الخصم')
            ->assertDontSee('moyasarPaymentForm')
            ->assertDontSee('moyasar-payment-form@2.2.10');

        $this->actingAs($user)
            ->postJson(route('cart.moyasar.prepare'), ['order_ids' => [$order->id]])
            ->assertStatus(409)
            ->assertJsonPath('confirm_url', route('cart.free.confirm'));

        $firstResponse = $this->actingAs($user)->post(route('cart.free.confirm'), [
            'order_ids' => [$order->id],
        ]);

        $firstResponse
            ->assertRedirect(route('orders.index', ['open_order' => $order->id]))
            ->assertSessionHas('status');

        $order->refresh();
        $firstPaidAt = $order->paid_at?->toDateTimeString();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame('full_discount', $order->payment_method);
        $this->assertSame(0.0, (float) $order->grand_total);
        $this->assertNotNull($firstPaidAt);

        $secondResponse = $this->actingAs($user)->post(route('cart.free.confirm'), [
            'order_ids' => [$order->id],
        ]);

        $secondResponse->assertRedirect(route('orders.index', ['open_order' => $order->id]));
        $this->assertSame(1, Order::query()->count());
        $this->assertSame($firstPaidAt, $order->fresh()->paid_at?->toDateTimeString());
    }

    public function test_a_positive_final_amount_stays_in_the_existing_payment_flow(): void
    {
        [$user, $order] = $this->createOrder(4);

        $response = $this->actingAs($user)->post(route('cart.free.confirm'), [
            'order_ids' => [$order->id],
        ]);

        $response
            ->assertRedirect(route('cart.payment', ['order_ids' => [$order->id]]))
            ->assertSessionHasErrors('payment');

        $order->refresh();
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertNull($order->payment_method);
        $this->assertSame(6.0, (float) $order->grand_total);
    }

    /**
     * @return array{User, Order}
     */
    private function createOrder(float $discount): array
    {
        $user = User::query()->create([
            'name' => 'Free Checkout Customer',
            'phone' => '0500000000',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'service_type' => 'research',
            'status' => 'new',
            'payment_status' => 'unpaid',
            'print_total' => 0,
            'binding_total' => 10,
            'discount_code' => 'FULL10',
            'discount_amount' => $discount,
            'discount_applied_at' => now(),
            'grand_total' => max(0, 10 - $discount),
        ]);
        OrderFile::query()->create([
            'order_id' => $order->id,
            'file_type' => 'other',
            'pages' => 1,
            'copies' => 1,
            'binding_price' => 10,
            'total_price' => 10,
        ]);

        return [$user, $order];
    }
}
