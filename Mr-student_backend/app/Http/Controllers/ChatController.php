<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->isAdmin($user)) {
            $conversations = ChatConversation::query()
                ->with(['customer:id,name,phone', 'latestMessage.sender:id,name'])
                ->withCount(['messages as unread_count' => function ($query) use ($user) {
                    $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
                }])
                ->orderByDesc('last_message_at')
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn (ChatConversation $conversation) => $this->conversationPayload($conversation, $user->id));

            return response()->json(['conversations' => $conversations]);
        }

        $conversation = ChatConversation::firstOrCreate(
            ['customer_id' => $user->id],
            ['last_message_at' => null]
        );

        $conversation->load(['customer:id,name,phone', 'latestMessage.sender:id,name'])
            ->loadCount(['messages as unread_count' => function ($query) use ($user) {
                $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
            }]);

        return response()->json(['conversations' => [$this->conversationPayload($conversation, $user->id)]]);
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
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'sender_name' => $this->displaySenderName($message->sender, $user),
                    'sender_role' => $message->sender?->role ?? 'customer',
                    'is_mine' => $message->sender_id === $user->id,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'read_at' => $message->read_at?->toIso8601String(),
                ];
            });

        $conversation->load(['customer:id,name,phone', 'latestMessage.sender:id,name'])
            ->loadCount(['messages as unread_count' => function ($query) use ($user) {
                $query->whereNull('read_at')->where('sender_id', '!=', $user->id);
            }]);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation, $user->id),
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_name' => $this->displaySenderName($request->user(), $request->user()),
                'sender_role' => $request->user()->role,
                'is_mine' => true,
                'created_at' => $message->created_at?->toIso8601String(),
                'read_at' => null,
            ],
        ], 201);
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
        if ($sender?->role === 'admin' && $viewer?->role !== 'admin') {
            return 'مسؤول خدمة العملاء';
        }

        return $sender?->name ?? 'مستخدم';
    }

    private function conversationPayload(ChatConversation $conversation, int $currentUserId): array
    {
        $latestMessage = $conversation->latestMessage;
        $lastMessageAt = $conversation->last_message_at ?? $conversation->updated_at;

        return [
            'id' => $conversation->id,
            'customer_name' => $conversation->customer?->name ?? 'عميل',
            'customer_phone' => $conversation->customer?->phone,
            'last_message' => $latestMessage?->message,
            'last_sender' => $latestMessage?->sender?->name,
            'last_message_at' => $lastMessageAt instanceof Carbon ? $lastMessageAt->toIso8601String() : null,
            'unread_count' => (int) ($conversation->unread_count ?? 0),
            'is_customer_owner' => $conversation->customer_id === $currentUserId,
        ];
    }
}
