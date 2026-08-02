<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\StationeryProduct;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminManagementPermissionsTest extends TestCase
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
            $table->json('admin_permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('login_blocked')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('new');
            $table->string('payment_status')->default('paid');
            $table->timestamps();
        });
        Schema::create('stationery_products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('company_name');
            $table->string('product_type');
            $table->decimal('price', 10, 2);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('stationery_products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_price_permission_can_change_only_the_stationery_price(): void
    {
        $employee = $this->employee([
            'stationery_products_view',
            'stationery_products_price_update',
        ]);
        $product = $this->product();

        $this->actingAs($employee)
            ->patch(route('admin.stationery-products.update', $product), [
                'name' => $product->name,
                'company_name' => $product->company_name,
                'product_type' => $product->product_type,
                'price' => 15,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertSame('15.00', $product->fresh()->price);

        $this->actingAs($employee)
            ->patch(route('admin.stationery-products.update', $product), [
                'name' => 'اسم غير مسموح',
                'company_name' => $product->company_name,
                'product_type' => $product->product_type,
                'price' => 15,
                'is_active' => 1,
            ])
            ->assertForbidden();

        $this->assertSame('المنتج الأصلي', $product->fresh()->name);
    }

    public function test_order_view_permission_does_not_allow_completing_an_order(): void
    {
        $employee = $this->employee(['orders_view']);
        $order = Order::query()->create([
            'user_id' => $employee->id,
            'status' => 'new',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($employee)
            ->patch(route('admin.orders.complete', $order))
            ->assertForbidden();

        $this->assertSame('new', $order->fresh()->status);
    }

    public function test_admin_can_rename_customer_without_sending_or_changing_password(): void
    {
        $employee = $this->employee(['customers_view', 'customers_update']);
        $customer = User::query()->create([
            'name' => 'الاسم القديم',
            'phone' => '0500000011',
            'password' => 'OriginalPassword1',
            'role' => 'customer',
        ]);
        $originalPassword = $customer->getRawOriginal('password');

        $this->actingAs($employee)
            ->patch(route('admin.users.update', $customer), [
                'first_name' => 'الاسم',
                'second_name' => 'الجديد',
                'phone' => $customer->phone,
                'role' => 'customer',
            ])
            ->assertRedirect();

        $customer->refresh();
        $this->assertSame('الاسم الجديد', $customer->name);
        $this->assertSame($originalPassword, $customer->getRawOriginal('password'));
    }

    private function employee(array $permissions): User
    {
        return User::query()->create([
            'name' => 'مستخدم إداري',
            'phone' => fake()->unique()->numerify('05########'),
            'password' => 'password',
            'role' => 'admin',
            'admin_permissions' => $permissions,
            'is_active' => true,
            'login_blocked' => false,
        ]);
    }

    private function product(): StationeryProduct
    {
        return StationeryProduct::query()->create([
            'name' => 'المنتج الأصلي',
            'company_name' => 'الشركة',
            'product_type' => 'النوع',
            'price' => 10,
            'is_active' => true,
        ]);
    }
}
