@extends('layouts.app')

@section('content')
@php $userId = auth()->id(); @endphp

<section class="bg-base-200">
  <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-base-content">Booking Chat</h1>
        <p class="mt-1 text-base-content/60">Booking #{{ $booking->id }} • {{ $booking->service->name ?? 'Service' }}</p>
      </div>
      <a href="{{ route('booking.show', $booking) }}" class="btn btn-outline btn-sm">Back to Booking</a>
    </div>

    @if(session('success'))
      <div class="alert alert-success mt-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error mt-4">{{ session('error') }}</div>
    @endif

    <div class="mt-6 rounded-2xl border border-base-300 bg-base-100 p-4">
      <div class="max-h-[60vh] space-y-3 overflow-y-auto p-2">
        @forelse($messages as $msg)
          <div class="chat {{ $msg->sender_id === $userId ? 'chat-end' : 'chat-start' }}">
            <div class="chat-header text-xs opacity-70">{{ $msg->sender->name ?? 'User' }} • {{ $msg->created_at->diffForHumans() }}</div>
            <div class="chat-bubble {{ $msg->sender_id === $userId ? 'chat-bubble-primary' : '' }}">
              @if($msg->message)
                <p class="whitespace-pre-wrap">{{ $msg->message }}</p>
              @endif
              @if($msg->attachment_path)
                <a class="link mt-2 block" target="_blank" href="{{ asset('storage/' . $msg->attachment_path) }}">Open attachment</a>
              @endif
            </div>
          </div>
        @empty
          <p class="text-base-content/50">No messages yet. Start the conversation.</p>
        @endforelse
      </div>

      <form method="POST" action="{{ route('booking.chat.store', $booking) }}" enctype="multipart/form-data" class="mt-4 grid gap-3">
        @csrf
        <textarea name="message" rows="3" class="textarea textarea-bordered w-full" placeholder="Write message..."></textarea>
        <input name="attachment" type="file" class="file-input file-input-bordered w-full" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
        <div>
          <button type="submit" class="btn btn-primary btn-sm">Send</button>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection
