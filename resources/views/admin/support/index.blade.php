@extends('admin.layouts.app')

@section('title', 'Support Inbox')

@section('content')
<div class="card bg-base-100 shadow-lg">
    <div class="card-body">
        <h3 class="card-title mb-4">Support Conversations</h3>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>Unread</th>
                        <th>Last Message</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr class="hover:bg-base-200">
                            <td>
                                <div class="font-semibold">{{ $conversation->user->name ?? 'Unknown User' }}</div>
                                <div class="text-xs text-base-content/60">{{ $conversation->user->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $conversation->status === 'closed' ? 'badge-warning' : 'badge-success' }}">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                            </td>
                            <td>
                                @if($conversation->unread_count > 0)
                                    <span class="badge badge-error">{{ $conversation->unread_count }}</span>
                                @else
                                    <span class="text-base-content/50">0</span>
                                @endif
                            </td>
                            <td class="max-w-xs">
                                @if($conversation->latestMessage)
                                    <p class="truncate">{{ $conversation->latestMessage->message }}</p>
                                    <p class="text-xs text-base-content/60 mt-1">
                                        {{ $conversation->latestMessage->created_at->diffForHumans() }}
                                    </p>
                                @else
                                    <span class="text-base-content/50">No messages yet</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.support.show', $conversation) }}" class="btn btn-sm btn-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60 py-8">No support conversations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
@endsection
