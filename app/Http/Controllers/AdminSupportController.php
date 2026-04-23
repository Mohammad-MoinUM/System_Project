<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminSupportController extends Controller
{
    public function index(): View
    {
        $conversations = SupportConversation::with([
                'user:id,name,email,role',
                'latestMessage.sender:id,name,role',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) {
                    $query->where('is_read', false)
                        ->whereHas('sender', function ($senderQuery) {
                            $senderQuery->where('role', '!=', 'admin');
                        });
                },
            ])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(20);

        return view('admin.support.index', compact('conversations'));
    }

    public function show(SupportConversation $conversation): View
    {
        $conversation->load([
            'user:id,name,email,role',
            'messages.sender:id,name,first_name,last_name,role',
        ]);

        $conversation->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', Auth::id())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('admin.support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'is_read' => false,
            'read_at' => null,
        ]);

        $conversation->update([
            'status' => 'open',
            'last_message_at' => now(),
            'last_message_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.support.show', $conversation)
            ->with('success', 'Reply sent successfully.');
    }

    public function close(SupportConversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => 'closed']);

        return redirect()
            ->route('admin.support.show', $conversation)
            ->with('success', 'Conversation marked as closed.');
    }

    public function reopen(SupportConversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => 'open']);

        return redirect()
            ->route('admin.support.show', $conversation)
            ->with('success', 'Conversation reopened.');
    }

    public function messages(SupportConversation $conversation): JsonResponse
    {
        $conversation->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', Auth::id())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,role')
            ->oldest()
            ->get();

        return response()->json([
            'status' => $conversation->status,
            'messages' => $messages->map(function ($message) {
                $isAdmin = optional($message->sender)->role === 'admin';

                return [
                    'id' => $message->id,
                    'sender_name' => $isAdmin ? 'Admin' : ($message->sender->name ?? 'User'),
                    'is_admin' => $isAdmin,
                    'message' => $message->message,
                    'attachment_name' => $message->attachment_name,
                    'attachment_url' => $message->attachment_path ? Storage::url($message->attachment_path) : null,
                    'sent_at' => $message->created_at?->format('M d, Y h:i A'),
                ];
            }),
            'last_message_id' => optional($messages->last())->id,
        ]);
    }
}
