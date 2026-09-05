<x-layouts.app title="Accommodation">
    <x-ui.page-header title="Accommodation" description="Rooms and flats near campus. No comments here — call the number listed." />

    @if (auth()->user()->isStudent())
        <x-post.composer :action="route('accommodations.store')" title="List accommodation" submit="Publish listing" multipart>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Area" name="area" required>
                    <x-ui.input name="area" :value="old('area')" placeholder="Merul Badda" required />
                </x-ui.field>

                <x-ui.field label="Walking distance" name="walking_distance" required>
                    <x-ui.input name="walking_distance" :value="old('walking_distance')" placeholder="10 min" required />
                </x-ui.field>

                <x-ui.field label="Phone number" name="phone_number" required>
                    <x-ui.input name="phone_number" :value="old('phone_number')" placeholder="01XXXXXXXXX" required />
                </x-ui.field>

                <x-ui.field label="Monthly rent (BDT)" name="rent">
                    <x-ui.input name="rent" type="number" step="0.01" min="0" :value="old('rent')" />
                </x-ui.field>
            </div>

            <x-ui.field label="Details" name="content" required>
                <x-ui.textarea name="content" rows="3" placeholder="Rooms, facilities, who it suits…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="Photo of the house" name="image">
                <input type="file" name="image" accept="image/*" data-preview="#accommodation-preview"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                <img id="accommodation-preview" alt="" class="mt-3 hidden max-h-48 rounded-xl object-cover ring-1 ring-ink-200">
            </x-ui.field>
        </x-post.composer>
    @endif

    <form method="GET" action="{{ route('accommodations.index') }}" class="flex gap-2">
        <div class="relative flex-1">
            <x-icon name="magnifier" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
            <x-ui.input name="q" :value="$search" placeholder="Search by area" class="pl-9" />
        </div>

        <x-ui.button type="submit" variant="secondary">Search</x-ui.button>

        @if ($search !== '')
            <x-ui.button as="a" href="{{ route('accommodations.index') }}" variant="ghost">Clear</x-ui.button>
        @endif
    </form>

    @forelse ($posts as $post)
        <x-post.card :post="$post" :title="$post->accommodation->area">
            <div class="mb-3 flex flex-wrap gap-2">
                <x-ui.badge tone="hive">
                    <x-icon name="map-pin" class="size-3.5" />
                    {{ $post->accommodation->walking_distance }} walk
                </x-ui.badge>

                @if ($post->accommodation->rent)
                    <x-ui.badge>{{ Number::currency((float) $post->accommodation->rent, 'BDT') }} / month</x-ui.badge>
                @endif
            </div>

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            <div class="mt-3">
                <x-post.image :post="$post" :path="$post->accommodation->image_path" :alt="$post->accommodation->area" />
            </div>

            <a href="tel:{{ $post->accommodation->phone_number }}"
               class="mt-3 inline-flex items-center gap-2 rounded-lg bg-hive-100 px-3 py-2 text-sm font-semibold text-hive-800 transition hover:bg-hive-200">
                <x-icon name="phone" class="size-4" />
                {{ $post->accommodation->phone_number }}
            </a>
        </x-post.card>
    @empty
        <x-ui.empty-state
            title="No listings yet"
            description="{{ $search !== '' ? 'Nothing matched that area.' : 'Share a place you know about.' }}" />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
