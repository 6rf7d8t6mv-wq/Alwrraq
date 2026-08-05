<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChatController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->isAdmin($user)) {
            $conversations = ChatConversation::query()
                ->with(['customer:id,name,phone,last_seen_at', 'latestMessage.sender:id,name'])
                ->withCount(['messages as unread_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
                }])
                ->orderByDesc('last_message_at')
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn (ChatConversation $conversation) => $this->conversationPayload($conversation, $user->id));

            return $this->json(['conversations' => $conversations]);
        }

        $conversation = ChatConversation::firstOrCreate(
            ['customer_id' => $user->id],
            ['last_message_at' => null]
        );

        $conversation->load(['customer:id,name,phone,last_seen_at', 'latestMessage.sender:id,name'])
            ->loadCount(['messages as unread_count' => function ($query) use ($user) {
                $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
            }]);

        return $this->json(['conversations' => [$this->conversationPayload($conversation, $user->id)]]);
    }

    public function show(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $user = $request->user();

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with('sender:id,name,role')
            ->oldest()
            ->get()
            ->map(fn (ChatMessage $message) => $this->messagePayload($message, $user, $request));

        $conversation->load(['customer:id,name,phone,last_seen_at', 'latestMessage.sender:id,name'])
            ->loadCount(['messages as unread_count' => function ($query) use ($user) {
                $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
            }]);

        return $this->json([
            'conversation' => $this->conversationPayload($conversation, $user->id),
            'messages' => $messages,
        ]);
    }

    public function stream(Request $request, ChatConversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);
        $afterMessageId = max(0, $request->integer('after'));

        return response()->stream(function () use ($conversation, $afterMessageId): void {
            @set_time_limit(30);
            $startedAt = microtime(true);

            while (! connection_aborted() && microtime(true) - $startedAt < 25) {
                $latestMessageId = (int) $conversation->messages()->max('id');
                if ($latestMessageId > $afterMessageId) {
                    echo "event: chat-update\n";
                    echo 'data: '.json_encode(['latest_message_id' => $latestMessageId])."\n\n";
                    @ob_flush();
                    flush();

                    return;
                }

                echo ": keepalive\n\n";
                @ob_flush();
                flush();
                usleep(700000);
            }

            echo "event: reconnect\n";
            echo "data: {}\n\n";
            @ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function store(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
            ],
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('private/chat-attachments/'.$conversation->id, 'local');
        }

        try {
            $message = $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'message' => trim((string) ($validated['message'] ?? '')),
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachment ? mb_substr($attachment->getClientOriginalName(), 0, 220) : null,
                'attachment_mime' => $attachment?->getMimeType(),
                'attachment_size' => $attachment?->getSize(),
            ]);
        } catch (Throwable $exception) {
            if ($attachmentPath) {
                Storage::disk('local')->delete($attachmentPath);
            }
            throw $exception;
        }

        $conversation->update(['last_message_at' => $message->created_at]);

        $message->setRelation('sender', $request->user());

        return $this->json([
            'message' => $this->messagePayload($message, $request->user(), $request),
        ], 201);
    }

    public function attachment(Request $request, ChatMessage $message)
    {
        $message->loadMissing('conversation');
        $this->authorizeConversation($request, $message->conversation);

        abort_unless(
            $message->attachment_path
                && str_starts_with($message->attachment_path, 'private/chat-attachments/')
                && Storage::disk('local')->exists($message->attachment_path),
            404
        );

        return response()->file(
            Storage::disk('local')->path($message->attachment_path),
            [
                'Content-Type' => $message->attachment_mime ?: 'application/octet-stream',
                'Content-Disposition' => "inline; filename*=UTF-8''".rawurlencode($message->attachment_name ?: 'attachment'),
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function authorizeConversation(Request $request, ChatConversation $conversation): void
    {
        $user = $request->user();

        if ($this->isAdmin($user)) {
            return;
        }

        abort_unless($conversation->customer_id === $user->id, 403);
    }

    private function isAdmin($user): bool
    {
        return $user?->role === 'admin';
    }

    private function displaySenderName($sender, $viewer): string
    {
        if ($sender?->role === 'admin') {
            return 'مسؤول خدمة العملاء';
        }

        return $sender?->name ?? 'مستخدم';
    }

    private function conversationPayload(ChatConversation $conversation, int $currentUserId): array
    {
        $latestMessage = $conversation->latestMessage;
        $lastMessageAt = $conversation->last_message_at ?? $conversation->updated_at;
        $isCustomerOwner = $conversation->customer_id === $currentUserId;
        $presence = $isCustomerOwner
            ? $this->supportPresence()
            : $this->presencePayload($conversation->customer?->last_seen_at);

        return [
            'id' => $conversation->id,
            'customer_name' => $conversation->customer?->name ?? 'عميل',
            'customer_phone' => $conversation->customer?->phone,
            'last_message' => filled($latestMessage?->message)
                ? $latestMessage->message
                : ($latestMessage?->attachment_name ? '📎 '.$latestMessage->attachment_name : null),
            'last_sender' => $latestMessage?->sender?->name,
            'last_message_at' => $lastMessageAt instanceof Carbon ? $lastMessageAt->toIso8601String() : null,
            'unread_count' => (int) ($conversation->unread_count ?? 0),
            'is_customer_owner' => $isCustomerOwner,
            ...$presence,
        ];
    }

    private function supportPresence(): array
    {
        $lastSeenAt = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->where('login_blocked', false)
            ->max('last_seen_at');

        return $this->presencePayload($lastSeenAt ? Carbon::parse($lastSeenAt) : null);
    }

    private function presencePayload(?Carbon $lastSeenAt): array
    {
        return [
            'is_online' => $lastSeenAt?->gte(now()->subSeconds(45)) ?? false,
            'last_seen_at' => $lastSeenAt?->toIso8601String(),
        ];
    }

    private function messagePayload(ChatMessage $message, $viewer, Request $request): array
    {
        $attachmentUrl = null;
        if ($message->attachment_path) {
            $attachmentUrl = $request->is('api/*')
                ? url('/api/chat/messages/'.$message->id.'/attachment')
                : route('chat.attachments.show', $message);
        }

        return [
            'id' => $message->id,
            'message' => $message->message,
            'sender_name' => $this->displaySenderName($message->sender, $viewer),
            'sender_role' => $message->sender?->role ?? 'customer',
            'is_mine' => $message->sender_id === $viewer->id,
            'attachment_url' => $attachmentUrl,
            'attachment_name' => $message->attachment_name,
            'attachment_mime' => $message->attachment_mime,
            'attachment_size' => $message->attachment_size,
            'attachment_is_image' => str_starts_with((string) $message->attachment_mime, 'image/'),
            'created_at' => $message->created_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
    }

    private function json(array $payload, int $status = 200): JsonResponse
    {
        return response()->json($payload, $status)
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }
}
