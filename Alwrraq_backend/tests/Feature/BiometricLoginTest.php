<?php

namespace Tests\Feature;

use App\Models\BiometricLoginToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BiometricLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('role')->default('customer');
            $table->boolean('is_active')->default(true);
            $table->boolean('login_blocked')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('biometric_login_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('device_id');
            $table->string('device_name')->nullable();
            $table->string('platform')->nullable();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function test_app_session_can_issue_a_device_token_without_storing_the_plain_value(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)
            ->withSession(['auth_surface' => 'app'])
            ->postJson('/app/biometric/token', [
                'device_id' => str_repeat('a', 32),
                'device_name' => 'iPhone',
                'platform' => 'ios',
            ]);

        $response->assertOk()->assertJsonStructure(['token', 'expires_at', 'user_name']);
        $plainToken = $response->json('token');
        $this->assertSame(96, strlen($plainToken));
        $this->assertDatabaseHas('biometric_login_tokens', [
            'user_id' => $user->id,
            'device_id' => str_repeat('a', 32),
            'token_hash' => hash('sha256', $plainToken),
        ]);
        $this->assertDatabaseMissing('biometric_login_tokens', ['token_hash' => $plainToken]);
    }

    public function test_valid_device_token_authenticates_and_invalid_token_does_not(): void
    {
        $user = $this->user();
        $plainToken = str_repeat('A', 96);
        BiometricLoginToken::query()->create([
            'user_id' => $user->id,
            'device_id' => str_repeat('b', 32),
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDay(),
        ]);

        $this->post('/app/biometric/login', [
            'token' => $plainToken,
            'device_id' => str_repeat('b', 32),
        ])->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout');
        $this->post('/app/biometric/login', [
            'token' => str_repeat('Z', 96),
            'device_id' => str_repeat('b', 32),
        ])->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_existing_webview_session_cannot_bypass_invalid_biometric_token(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)
            ->post('/app/biometric/login', [
                'token' => str_repeat('Z', 96),
                'device_id' => str_repeat('b', 32),
            ])
            ->assertUnauthorized();

        $this->assertAuthenticatedAs($user);
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_app_password_login_opens_internal_home_for_every_role(): void
    {
        foreach (['customer', 'admin'] as $index => $role) {
            $user = User::query()->create([
                'name' => $role === 'admin' ? 'مدير تجريبي' : 'عميل تجريبي',
                'email' => $role.'@example.com',
                'phone' => '050000000'.($index + 2),
                'password' => 'password',
                'role' => $role,
                'is_active' => true,
                'login_blocked' => false,
            ]);

            $this->withSession(['url.intended' => '/cart'])
                ->post('/app/login', [
                    'login_identifier' => $user->phone,
                    'password' => 'password',
                ])
                ->assertRedirect('/home')
                ->assertSessionMissing('url.intended');

            $this->post('/logout');
        }
    }

    public function test_changing_password_revokes_all_biometric_tokens(): void
    {
        $user = $this->user();
        BiometricLoginToken::query()->create([
            'user_id' => $user->id,
            'device_id' => str_repeat('c', 32),
            'token_hash' => hash('sha256', str_repeat('C', 96)),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->patch('/account/password', [
            'current_password' => 'password',
            'password' => 'newpass7',
            'password_confirmation' => 'newpass7',
        ])->assertRedirect('/account/settings');

        $this->assertDatabaseCount('biometric_login_tokens', 0);
        $this->assertTrue(Hash::check('newpass7', $user->fresh()->password));
    }

    private function user(): User
    {
        return User::query()->create([
            'name' => 'مستخدم تجريبي',
            'email' => 'user@example.com',
            'phone' => '0500000001',
            'password' => 'password',
            'role' => 'customer',
            'is_active' => true,
            'login_blocked' => false,
        ]);
    }
}
