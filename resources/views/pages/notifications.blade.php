@extends('layouts.app')

@section('title', 'Notifications')
@section('content')

<section class="bg-base-100">
  <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-8">
      <div>
        <h2 class="text-3xl font-bold text-base-content">Notifications</h2>
        <p class="mt-1 text-base text-base-content/60">Stay updated on your bookings and reviews.</p>
        @if(($unreadCount ?? 0) > 0)
          <span class="badge badge-info mt-2">{{ $unreadCount }} unread</span>
        @endif
      </div>
      @if($notifications->where('read_at', null)->count())
        <form method="POST" action="{{ route('notifications.readAll') }}">
          @csrf
          <button type="submit" class="btn btn-ghost btn-sm text-primary">Mark all as read</button>
        </form>
      @endif
    </div>

    <div class="space-y-3">
      @forelse($notifications as $notification)
        @php
          $data = $notification->data;
          $isUnread = is_null($notification->read_at);
          $iconMap = [
            'clipboard-document-check' => 'heroicon-o-clipboard-document-check',
            'check-circle' => 'heroicon-o-check-circle',
            'x-circle' => 'heroicon-o-x-circle',
            'arrow-path' => 'heroicon-o-arrow-path',
            'information-circle' => 'heroicon-o-information-circle',
            'star' => 'heroicon-s-star',
          ];
          $iconComponent = $iconMap[$data['icon'] ?? ''] ?? 'heroicon-o-bell';
        @endphp

        <div class="flex items-start gap-4 rounded-xl p-4 transition
          {{ $isUnread ? 'bg-primary/5 border border-primary/20' : 'bg-base-200/50' }}">

          {{-- Icon --}}
          <div class="flex-shrink-0 mt-1">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full
              {{ $isUnread ? 'bg-primary/15 text-primary' : 'bg-base-300 text-base-content/40' }}">
              <x-dynamic-component :component="$iconComponent" class="h-5 w-5" />
            </div>
          </div>

          {{-- Content --}}
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div>
                <h3 class="text-sm font-semibold text-base-content {{ $isUnread ? '' : 'text-base-content/70' }}">
                  {{ $data['title'] ?? 'Notification' }}
                </h3>
                <p class="text-sm text-base-content/60 mt-0.5">
                  {{ $data['message'] ?? '' }}
                </p>
                <span class="text-xs text-base-content/40 mt-1 block">
                  {{ $notification->created_at->diffForHumans() }}
                </span>
              </div>

              @if($isUnread)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                  @csrf
                  <button type="submit" class="btn btn-ghost btn-xs text-primary" title="Mark as read">
                    <x-heroicon-o-check class="w-4 h-4" />
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="text-center py-16">
          <x-heroicon-o-bell-slash class="h-12 w-12 text-base-content/20 mx-auto" />
          <p class="mt-4 text-base-content/50 text-lg">No notifications yet</p>
          <p class="text-sm text-base-content/40 mt-1">You'll see updates about bookings and reviews here.</p>
        </div>
      @endforelse
    </div>

    @if($notifications->hasPages())
      <div class="mt-8">
        {{ $notifications->links() }}
      </div>
    @endif

  </div>
</section>

@endsection
