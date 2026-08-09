<x-layouts.app title="Campus Pets">
    <x-ui.page-header title="Campus Pets" description="The cats and dogs that actually own this campus." />

    @if (auth()->user()->isStudent())
        <x-post.composer :action="route('pets.store')" title="Share a pet post" submit="Post" multipart>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Pet name" name="pet_name">
                    <x-ui.input name="pet_name" :value="old('pet_name')" placeholder="Kitkat" />
                </x-ui.field>

                <x-ui.field label="Spotted at" name="spotted_at">
                    <x-ui.input name="spotted_at" :value="old('spotted_at')" placeholder="Cafeteria back gate" />
                </x-ui.field>
            </div>

            <x-ui.field label="Post" name="content" required>
                <x-ui.textarea name="content" rows="3" placeholder="Needs food, needs a vet, or just being adorable…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="Photo" name="image">
                <input type="file" name="image" accept="image/*" data-preview="#pet-preview"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                <img id="pet-preview" alt="" class="mt-3 hidden max-h-48 rounded-xl object-cover ring-1 ring-ink-200">
            </x-ui.field>
        </x-post.composer>
    @endif

    @forelse ($posts as $post)
        <x-post.card :post="$post" :title="$post->pet->pet_name">
            @if ($post->pet->spotted_at)
                <p class="mb-2 inline-flex items-center gap-1 text-xs text-ink-500">
                    <x-icon name="map-pin" class="size-3.5" />
                    {{ $post->pet->spotted_at }}
                </p>
            @endif

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            <div class="mt-3">
                <x-post.image :post="$post" :path="$post->pet->image_path" :alt="$post->pet->pet_name ?? 'Campus pet'" />
            </div>
        </x-post.card>
    @empty
        <x-ui.empty-state title="No pet posts yet" description="Share the campus regulars everyone should know." />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
