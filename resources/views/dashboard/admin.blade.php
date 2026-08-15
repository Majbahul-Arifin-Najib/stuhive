<x-layouts.app title="Dashboard">
    @include('dashboard.partials.greeting')

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Students" :value="$userCounts['student'] ?? 0" icon="cap" />
        <x-stat label="Faculty" :value="$userCounts['faculty'] ?? 0" icon="user" />
        <x-stat label="Posts" :value="$postCount" icon="megaphone" :hint="$postsToday.' today'" />
        <x-stat label="Polls" :value="$pollCount" icon="hand" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-calendar :month="$month" :weeks="$weeks" :entries="$entries" />
        </div>

        <div class="space-y-6">
            <x-ui.card padded="false">
                <div class="flex items-center justify-between border-b border-ink-200 px-5 py-4">
                    <p class="text-sm font-semibold text-ink-900">Latest posts</p>
                    <a href="{{ route('admin.moderation.index') }}" class="text-xs font-medium text-hive-700 hover:text-hive-800">Moderate</a>
                </div>

                @if ($latestPosts->isEmpty())
                    <p class="px-5 py-6 text-center text-sm text-ink-500">No posts yet.</p>
                @else
                    <ul class="divide-y divide-ink-200">
                        @foreach ($latestPosts as $post)
                            <li class="flex items-start gap-3 px-5 py-3">
                                <x-ui.avatar :user="$post->author" size="sm" />

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-ink-900">{{ $post->author->name }}</p>
                                    <p class="truncate text-xs text-ink-500">
                                        {{ $post->type->label() }} · {{ $post->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            @include('dashboard.partials.notes')
        </div>
    </div>
</x-layouts.app>
