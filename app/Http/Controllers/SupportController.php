<?php

namespace App\Http\Controllers;

use App\Models\SupportConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open']
        );

        $conversation->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = $conversation->messages()
            ->with('sender:id,name,first_name,last_name,role')
            ->oldest()
            ->get();

        return view('pages.support_chat', compact('conversation', 'messages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $user = Auth::user();

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open']
        );

        if ($conversation->status === 'closed') {
            $conversation->status = 'open';
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('support-attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'is_read' => false,
            'read_at' => null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        $conversation->last_message_at = now();
        $conversation->last_message_by = $user->id;
        $conversation->save();

        return redirect()
            ->route('support.index')
            ->with('success', 'Your message has been sent to support.');
    }

    public function messages(): JsonResponse
    {
        $user = Auth::user();

        $conversation = SupportConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'open']
        );

        $conversation->messages()
            ->where('is_read', false)
            ->where('sender_id', '!=', $user->id)
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
            'messages' => $messages->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name ?? 'Support Team',
                    'is_mine' => (int) $message->sender_id === (int) $user->id,
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
