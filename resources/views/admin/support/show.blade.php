@extends('admin.layouts.app')

@section('title', 'Support Conversation')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h3 class="text-xl font-semibold">{{ $conversation->user->name ?? 'Unknown User' }}</h3>
        <p class="text-sm text-base-content/60">{{ $conversation->user->email ?? '-' }}</p>
    </div>

    <div class="flex items-center gap-2">
        <span id="conversation-status-badge" class="badge {{ $conversation->status === 'closed' ? 'badge-warning' : 'badge-success' }}">
            {{ ucfirst($conversation->status) }}
        </span>

        @if($conversation->status === 'open')
            <form method="POST" action="{{ route('admin.support.close', $conversation) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline btn-warning">Close</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.support.reopen', $conversation) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline btn-success">Reopen</button>
            </form>
        @endif

        <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-ghost">Back</a>
    </div>
</div>

<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <div id="chat-messages" data-last-id="{{ optional($conversation->messages->last())->id ?? 0 }}" class="max-h-[30rem] overflow-y-auto space-y-4">
            @forelse($conversation->messages as $message)
                @php $isAdmin = optional($message->sender)->role === 'admin'; @endphp
                <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $isAdmin ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
                        <p class="text-xs opacity-80 mb-1">{{ $isAdmin ? 'Admin' : ($message->sender->name ?? 'User') }}</p>
                        <p class="whitespace-pre-wrap break-words">{{ $message->message }}</p>
                        @if($message->attachment_path)
                            <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank" class="mt-3 inline-flex items-center gap-2 text-sm underline opacity-90">
                                <x-heroicon-o-paper-clip class="w-4 h-4" />
                                {{ $message->attachment_name ?? 'View attachment' }}
                            </a>
                        @endif
                        <p class="text-[11px] opacity-70 mt-2 text-right">{{ $message->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            @empty
                <div class="rounded-lg bg-base-200 p-4 text-base-content/70">No messages in this conversation yet.</div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.support.reply', $conversation) }}" class="mt-6 border-t border-base-300 pt-5">
            @csrf
            <label class="label" for="admin-reply-message">
                <span class="label-text font-semibold">Reply to user</span>
            </label>
            <textarea
                id="admin-reply-message"
                name="message"
                rows="4"
                class="textarea textarea-bordered w-full @error('message') textarea-error @enderror"
                placeholder="Write your reply..."
                required
            >{{ old('message') }}</textarea>
            @error('message')
                <p class="text-error text-sm mt-2">{{ $message }}</p>
            @enderror
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messagesEl = document.getElementById('chat-messages');
    const statusBadge = document.getElementById('conversation-status-badge');
    const messagesUrl = "{{ route('admin.support.messages', $conversation) }}";
    let lastId = Number(messagesEl.dataset.lastId || 0);

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function renderMessages(messages) {
        if (!Array.isArray(messages) || messages.length === 0) {
            messagesEl.innerHTML = '<div class="rounded-lg bg-base-200 p-4 text-base-content/70">No messages in this conversation yet.</div>';
            return;
        }

        messagesEl.innerHTML = messages.map((message) => {
            const isAdmin = !!message.is_admin;
            return `
                <div class="flex ${isAdmin ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[85%] rounded-2xl px-4 py-3 ${isAdmin ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content'}">
                        <p class="text-xs opacity-80 mb-1">${escapeHtml(message.sender_name || (isAdmin ? 'Admin' : 'User'))}</p>
                        <p class="whitespace-pre-wrap break-words">${escapeHtml(message.message || '')}</p>
                        ${message.attachment_url ? `<a href="${escapeHtml(message.attachment_url)}" target="_blank" class="mt-3 inline-flex items-center gap-2 text-sm underline opacity-90"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a5.5 5.5 0 10-7.778 7.778l.707.707m-2.828-2.828l-1.414-1.414a3.5 3.5 0 015.657-4.243l.707.707m4.243 4.243l1.414 1.414a3.5 3.5 0 01-5.657 4.243l-.707-.707" /></svg>${escapeHtml(message.attachment_name || 'View attachment')}</a>` : ''}
                        <p class="text-[11px] opacity-70 mt-2 text-right">${escapeHtml(message.sent_at || '')}</p>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateStatus(status) {
        if (!statusBadge || !status) {
            return;
        }

        const normalized = String(status).toLowerCase();
        statusBadge.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1);
        statusBadge.classList.remove('badge-success', 'badge-warning');
        statusBadge.classList.add(normalized === 'closed' ? 'badge-warning' : 'badge-success');
    }

    async function pollMessages() {
        if (document.hidden) {
            return;
        }

        try {
            const response = await fetch(messagesUrl, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const newLastId = Number(data.last_message_id || 0);

            updateStatus(data.status);

            if (newLastId !== lastId) {
                renderMessages(data.messages || []);
                lastId = newLastId;
                messagesEl.dataset.lastId = String(lastId);
                scrollToBottom();
            }
        } catch (error) {
            // Silent fail for transient network issues.
        }
    }

    scrollToBottom();
    setInterval(pollMessages, 5000);
});
</script>
@endpush
