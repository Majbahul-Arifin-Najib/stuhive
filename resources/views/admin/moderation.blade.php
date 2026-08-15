@php
    use App\Enums\PostType;
@endphp

<x-layouts.app title="Moderation">
    <x-ui.page-header title="Moderation" description="Remove any post or poll at any time. Deleted records stay in the database." />

    <form method="GET" action="{{ route('admin.moderation.index') }}" class="flex flex-wrap gap-2">
        <div class="relative min-w-64 flex-1">
            <x-icon name="magnifier" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
            <x-ui.input name="q" :value="$search" placeholder="Search content or author" class="pl-9" />
        </div>

        <x-ui.select name="type" class="w-56" data-auto-submit>
            <option value="">All sections</option>
            @foreach (PostType::cases() as $case)
                <option value="{{ $case->value }}" @selected($type === $case)>{{ $case->label() }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
    </form>

    <x-ui.card padded="false">
        <div class="border-b border-ink-200 px-5 py-4">
            <p class="text-sm font-semibold text-ink-900">Posts</p>
        </div>

        @if ($posts->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-ink-500">No posts matched.</p>
        @else
            <ul class="divide-y divide-ink-200">
                @foreach ($posts as $post)
                    <li class="flex items-start gap-3 px-5 py-4">
                        <x-ui.avatar :user="$post->author" size="sm" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold text-ink-900">{{ $post->author->name }}</p>
                                <x-ui.badge tone="hive">{{ $post->type->label() }}</x-ui.badge>
                                <span class="text-xs text-ink-500">{{ $post->created_at->diffForHumans() }}</span>
                            </div>

                            <p class="mt-1 line-clamp-2 text-sm text-ink-600">{{ $post->content }}</p>

                            <p class="mt-1 text-xs text-ink-500">
                                {{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }} ·
                                {{ $post->reactions_count }} {{ Str::plural('reaction', $post->reactions_count) }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.moderation.posts.destroy', $post) }}"
                              data-confirm="Delete this post? It disappears from the frontend but stays in the database.">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm">
                                <x-icon name="trash" class="size-4" />
                                Delete
                            </x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <div>{{ $posts->links() }}</div>

    <x-ui.card padded="false">
        <div class="border-b border-ink-200 px-5 py-4">
            <p class="text-sm font-semibold text-ink-900">Recent polls</p>
        </div>

        @if ($polls->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-ink-500">No polls yet.</p>
        @else
            <ul class="divide-y divide-ink-200">
                @foreach ($polls as $poll)
                    <li class="flex items-start gap-3 px-5 py-4">
                        <x-ui.avatar :user="$poll->author" size="sm" />

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink-900">{{ $poll->author->name }}</p>
                            <p class="mt-0.5 line-clamp-2 text-sm text-ink-600">{{ $poll->question }}</p>
                            <p class="mt-1 text-xs text-ink-500">
                                {{ $poll->votes_count }} {{ Str::plural('vote', $poll->votes_count) }} ·
                                {{ $poll->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.moderation.polls.destroy', $poll) }}"
                              data-confirm="Delete this poll?">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm">
                                <x-icon name="trash" class="size-4" />
                                Delete
                            </x-ui.button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</x-layouts.app>
