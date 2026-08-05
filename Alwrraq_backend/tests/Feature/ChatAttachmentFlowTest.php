<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatAttachmentFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['customer', 'admin'])->default('customer');
            $table->json('admin_permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('login_blocked')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('chat_conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('chat_conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime', 120)->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_customer_and_admin_can_exchange_and_open_protected_attachments(): void
    {
        $customer = User::query()->create([
            'name' => 'عميل المرفقات',
            'phone' => '0500000000',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $admin = User::query()->create([
            'name' => 'مدير المحادثة',
            'phone' => '0511111111',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $otherCustomer = User::query()->create([
            'name' => 'عميل آخر',
            'phone' => '0522222222',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $conversationsResponse = $this->actingAs($customer)
            ->getJson(route('chat.conversations'))
            ->assertOk();
        $this->assertStringContainsString('no-store', (string) $conversationsResponse->headers->get('Cache-Control'));

        $conversation = ChatConversation::query()->where('customer_id', $customer->id)->firstOrFail();
        $customerResponse = $this->actingAs($customer)->post(route('chat.messages.store', $conversation), [
            'message' => 'هذه صورة',
            'attachment' => UploadedFile::fake()->image('example.jpg', 900, 700),
        ]);
        $customerResponse
            ->assertCreated()
            ->assertJsonPath('message.attachment_name', 'example.jpg')
            ->assertJsonPath('message.attachment_is_image', true);

        $messageId = $customerResponse->json('message.id');
        $this->actingAs($admin)
            ->getJson(route('chat.conversations.show', $conversation))
            ->assertOk()
            ->assertJsonPath('conversation.customer_name', 'عميل المرفقات')
            ->assertJsonPath('messages.0.attachment_name', 'example.jpg');
        $this->actingAs($admin)
            ->get(route('chat.attachments.show', $messageId))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($otherCustomer)
            ->get(route('chat.attachments.show', $messageId))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('chat.messages.store', $conversation), [
                'attachment' => UploadedFile::fake()->createWithContent('instructions.txt', 'Admin attachment'),
            ])
            ->assertCreated()
            ->assertJsonPath('message.attachment_name', 'instructions.txt')
            ->assertJsonPath('message.is_mine', true);

        $this->actingAs($customer)
            ->getJson(route('chat.conversations.show', $conversation))
            ->assertOk()
            ->assertJsonCount(2, 'messages')
            ->assertJsonPath('messages.1.attachment_name', 'instructions.txt');
    }
}
