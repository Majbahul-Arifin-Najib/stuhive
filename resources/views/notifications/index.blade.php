<x-layouts.app title="Notifications">
    <x-ui.page-header title="Notifications" description="New posts, consultation bookings and schedule changes.">
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read_all') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary">Mark all as read</x-ui.button>
            </form>
        @endif
    </x-ui.page-header>

    @if ($notifications->isEmpty())
        <x-ui.empty-state title="You are all caught up" description="Activity from around campus will show up here." />
    @else
        <x-ui.card padded="false">
            <ul class="divide-y divide-ink-200">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $unread = $notification->read_at === null;
                    @endphp

                    <li @class(['flex items-start gap-3 px-5 py-4', 'bg-hive-50/60' => $unread])>
                        <span class="rounded-lg bg-white p-2 text-hive-700 ring-1 ring-ink-200">
                            <x-icon :name="$data['icon'] ?? 'bell'" class="size-4" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink-900">{{ $data['title'] ?? 'Notification' }}</p>

                            @if (! empty($data['body']))
                                <p class="mt-0.5 text-sm text-ink-600">{{ $data['body'] }}</p>
                            @endif

                            <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-ink-500">
                                <span>{{ $notification->created_at->diffForHumans() }}</span>

                                @if (! empty($data['url']))
                                    <a href="{{ $data['url'] }}" class="font-medium text-hive-700 hover:text-hive-800">View</a>
                                @endif

                                @if ($unread)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="font-medium text-ink-500 hover:text-ink-800">Mark as read</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if ($unread)
                            <span class="mt-1.5 size-2 shrink-0 rounded-full bg-hive-600"></span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <div>{{ $notifications->links() }}</div>
    @endif
</x-layouts.app>
