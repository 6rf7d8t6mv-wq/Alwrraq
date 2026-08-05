<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['user_id', 'updated_at'], 'orders_user_updated_at_index');
            $table->index(['admin_notification_seen_at', 'status'], 'orders_admin_notice_status_index');
            $table->index(['user_id', 'customer_notification_seen_at'], 'orders_customer_notice_index');
        });

        Schema::table('order_delivered_files', function (Blueprint $table): void {
            $table->index(['order_id', 'customer_downloaded_at'], 'delivered_files_order_download_index');
        });

        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->index('last_message_at', 'chat_conversations_last_message_index');
        });

        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->index(['chat_conversation_id', 'id'], 'chat_messages_conversation_id_index');
            $table->index(['chat_conversation_id', 'read_at', 'sender_id'], 'chat_messages_unread_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table): void {
            $table->dropIndex('chat_messages_conversation_id_index');
            $table->dropIndex('chat_messages_unread_lookup_index');
        });
        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->dropIndex('chat_conversations_last_message_index');
        });
        Schema::table('order_delivered_files', function (Blueprint $table): void {
            $table->dropIndex('delivered_files_order_download_index');
        });
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_user_updated_at_index');
            $table->dropIndex('orders_admin_notice_status_index');
            $table->dropIndex('orders_customer_notice_index');
        });
    }
};
