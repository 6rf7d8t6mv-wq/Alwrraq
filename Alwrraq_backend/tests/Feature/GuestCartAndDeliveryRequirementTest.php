<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ServiceDefinition;
use App\Models\User;
use App\Services\GuestCartService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuestCartAndDeliveryRequirementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->uuid('guest_token')->nullable()->index();
            $table->string('service_type');
            $table->string('status')->default('new');
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_guest_orders_are_session_bound_and_claimed_after_login(): void
    {
        $request = Request::create('/home');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->start();
        $guestCart = $this->app->make(GuestCartService::class);

        $identity = $guestCart->orderIdentity($request);
        $order = Order::query()->create($identity + [
            'service_type' => 'notes',
            'status' => 'new',
            'payment_status' => 'unpaid',
        ]);

        $this->assertNull($order->user_id);
        $this->assertNotEmpty($order->guest_token);
        $this->assertTrue($guestCart->owns($request, $order));

        $user = User::query()->create(['name' => 'عميل']);
        $guestCart->claim($request, $user);

        $this->assertSame($user->id, $order->fresh()->user_id);
        $this->assertNull($order->fresh()->guest_token);
        $this->assertFalse($request->session()->has('guest_cart_token'));
    }

    public function test_service_definition_controls_delivery_requirement_with_legacy_fallback(): void
    {
        $configuredOrder = new Order(['service_type' => 'formatting']);
        $configuredOrder->setRelation('serviceDefinition', new ServiceDefinition([
            'requires_delivery' => true,
        ]));

        $this->assertTrue($configuredOrder->requiresDelivery());
        $this->assertFalse((new Order(['service_type' => 'formatting']))->requiresDelivery());
        $this->assertTrue((new Order(['service_type' => 'notes']))->requiresDelivery());
    }
}
