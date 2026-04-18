@extends('layouts.app')

@section('content')
<section class="bg-base-200 min-h-[70vh]">
  <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-3xl font-bold text-base-content">Support Chat</h1>
        <p class="text-base-content/60 mt-1">Chat directly with our support team.</p>
      </div>
      <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">Back to Home</a>
    </div>

    <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 shadow-sm">
      <div class="border-b border-base-300 px-5 py-4 flex items-center justify-between">
        <div>
          <p class="font-semibold text-base-content">Conversation Status</p>
          <p id="conversation-status" class="text-sm {{ $conversation->status === 'closed' ? 'text-warning' : 'text-success' }}">
            {{ ucfirst($conversation->status) }}
          </p>
        </div>
      </div>

      <div id="chat-messages" data-last-id="{{ optional($messages->last())->id ?? 0 }}" class="max-h-[28rem] overflow-y-auto p-5 space-y-4">
        @forelse($messages as $message)
          @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
          <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $mine ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
              <p class="text-xs opacity-80 mb-1">
                {{ $mine ? 'You' : ($message->sender->name ?? 'Support Team') }}
              </p>
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
          <div class="rounded-xl bg-base-200 p-5 text-base-content/70">
            No messages yet. Start the conversation and our team will respond.
          </div>
        @endforelse
      </div>

      <form method="POST" action="{{ route('support.store') }}" enctype="multipart/form-data" class="border-t border-base-300 p-5">
        @csrf
        <label class="label" for="support-message">
          <span class="label-text font-semibold">Your message</span>
        </label>
        <textarea
          id="support-message"
          name="message"
          rows="4"
          class="textarea textarea-bordered w-full @error('message') textarea-error @enderror"
          placeholder="Describe your issue or ask for a recommendation..."
          required
        >{{ old('message') }}</textarea>
        @error('message')
          <p class="text-error text-sm mt-2">{{ $message }}</p>
        @enderror
        <div class="mt-4">
          <label class="label" for="support-attachment">
            <span class="label-text font-semibold">Attachment (optional)</span>
          </label>
          <input id="support-attachment" type="file" name="attachment" class="file-input file-input-bordered w-full" />
          @error('attachment')
            <p class="text-error text-sm mt-2">{{ $message }}</p>
          @enderror
        </div>
        <div class="mt-4 flex justify-end">
          <button type="submit" class="btn btn-primary">Send Message</button>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const messagesEl = document.getElementById('chat-messages');
  const statusEl = document.getElementById('conversation-status');
  const messagesUrl = "{{ route('support.messages') }}";
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
      messagesEl.innerHTML = '<div class="rounded-xl bg-base-200 p-5 text-base-content/70">No messages yet. Start the conversation and our team will respond.</div>';
      return;
    }

    messagesEl.innerHTML = messages.map((message) => {
      const mine = !!message.is_mine;
      const attachmentLink = message.attachment_url
        ? `<a href="${escapeHtml(message.attachment_url)}" target="_blank" class="mt-3 inline-flex items-center gap-2 text-sm underline opacity-90"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636a5.5 5.5 0 10-7.778 7.778l.707.707m-2.828-2.828l-1.414-1.414a3.5 3.5 0 015.657-4.243l.707.707m4.243 4.243l1.414 1.414a3.5 3.5 0 01-5.657 4.243l-.707-.707" /></svg>${escapeHtml(message.attachment_name || 'View attachment')}</a>`
        : '';

      return `
        <div class="flex ${mine ? 'justify-end' : 'justify-start'}">
          <div class="max-w-[85%] rounded-2xl px-4 py-3 ${mine ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content'}">
            <p class="text-xs opacity-80 mb-1">${mine ? 'You' : escapeHtml(message.sender_name || 'Support Team')}</p>
            <p class="whitespace-pre-wrap break-words">${escapeHtml(message.message || '')}</p>
            ${attachmentLink}
            <p class="text-[11px] opacity-70 mt-2 text-right">${escapeHtml(message.sent_at || '')}</p>
          </div>
        </div>
      `;
    }).join('');
  }

  function updateStatus(status) {
    if (!statusEl || !status) {
      return;
    }
    const normalized = String(status).toLowerCase();
    statusEl.textContent = normalized.charAt(0).toUpperCase() + normalized.slice(1);
    statusEl.classList.remove('text-warning', 'text-success');
    statusEl.classList.add(normalized === 'closed' ? 'text-warning' : 'text-success');
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
