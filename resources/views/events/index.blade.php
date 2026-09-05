<x-layouts.app title="Event Announcements">
    <x-ui.page-header title="Event Announcements" description="Club events around campus. Tap Interested to add one to your calendar." />

    @if (App\Enums\PostType::Event->creatableBy(auth()->user()->role))
        <x-post.composer :action="route('events.store')" title="Announce an event" submit="Publish event" multipart>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Event name" name="event_name" required>
                    <x-ui.input name="event_name" :value="old('event_name')" placeholder="Intra Hackathon" required />
                </x-ui.field>

                <x-ui.field label="Club" name="club_name">
                    <x-ui.input name="club_name" :value="old('club_name')" placeholder="Computer Club" />
                </x-ui.field>

                <x-ui.field label="Date" name="event_date" required>
                    <x-ui.input name="event_date" type="date" :value="old('event_date')" required />
                </x-ui.field>

                <x-ui.field label="Time" name="event_time" required>
                    <x-ui.input name="event_time" type="time" :value="old('event_time')" required />
                </x-ui.field>

                <x-ui.field label="Venue" name="venue" class="sm:col-span-2">
                    <x-ui.input name="venue" :value="old('venue')" placeholder="Auditorium" />
                </x-ui.field>
            </div>

            <x-ui.field label="Details" name="content" required>
                <x-ui.textarea name="content" rows="3" placeholder="Tell students what to expect…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="Poster" name="image">
                <input type="file" name="image" accept="image/*" data-preview="#event-preview"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                <img id="event-preview" alt="" class="mt-3 hidden max-h-48 rounded-xl object-cover ring-1 ring-ink-200">
            </x-ui.field>
        </x-post.composer>
    @endif

    @forelse ($posts as $post)
        <x-post.card :post="$post" :title="$post->event->event_name">
            <div class="mb-3 flex flex-wrap gap-2">
                @if ($post->event->club_name)
                    <x-ui.badge tone="hive">{{ $post->event->club_name }}</x-ui.badge>
                @endif

                <x-ui.badge>
                    <x-icon name="calendar" class="size-3.5" />
                    {{ $post->event->event_date->format('D, j M Y') }}
                </x-ui.badge>

                <x-ui.badge>
                    <x-icon name="clock" class="size-3.5" />
                    {{ $post->event->startsAt()->format('g:i A') }}
                </x-ui.badge>

                @if ($post->event->venue)
                    <x-ui.badge>
                        <x-icon name="map-pin" class="size-3.5" />
                        {{ $post->event->venue }}
                    </x-ui.badge>
                @endif
            </div>

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            <div class="mt-3">
                <x-post.image :post="$post" :path="$post->event->image_path" :alt="$post->event->event_name" />
            </div>

            @php
                $interested = $post->eventInterests->isNotEmpty();
            @endphp

            <div class="mt-3 flex items-center gap-3">
                <form method="POST" action="{{ route('events.interest', $post) }}">
                    @csrf
                    <x-ui.button type="submit" size="sm" :variant="$interested ? 'success' : 'primary'">
                        <x-icon :name="$interested ? 'check-circle' : 'star'" class="size-4" />
                        {{ $interested ? 'Interested' : 'Interested?' }}
                    </x-ui.button>
                </form>

                <p class="text-xs text-ink-500">
                    {{ $post->event_interests_count }} {{ Str::plural('student', $post->event_interests_count) }} interested
                </p>
            </div>
        </x-post.card>
    @empty
        <x-ui.empty-state title="No events announced" description="Club announcements will appear here." />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
