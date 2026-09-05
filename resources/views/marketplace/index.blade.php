@php
    $conditions = ['new' => 'New', 'like_new' => 'Like new', 'used' => 'Used'];
@endphp

<x-layouts.app title="Marketplace">
    <x-ui.page-header title="Marketplace" description="Buy and sell within campus. Reactions only — contact the seller directly.">
        <x-ui.button as="a" variant="secondary"
                     href="{{ $showSold ? route('marketplace.index') : route('marketplace.index', ['sold' => 1]) }}">
            {{ $showSold ? 'Hide sold' : 'Show sold' }}
        </x-ui.button>
    </x-ui.page-header>

    @if (auth()->user()->isStudent())
        <x-post.composer :action="route('marketplace.store')" title="List a product" submit="List product" multipart>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Product" name="product_name" required>
                    <x-ui.input name="product_name" :value="old('product_name')" placeholder="Scientific calculator" required />
                </x-ui.field>

                <x-ui.field label="Price (BDT)" name="price" required>
                    <x-ui.input name="price" type="number" step="0.01" min="0" :value="old('price')" required />
                </x-ui.field>

                <x-ui.field label="Condition" name="condition" required>
                    <x-ui.select name="condition" required>
                        @foreach ($conditions as $value => $label)
                            <option value="{{ $value }}" @selected(old('condition') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                <x-ui.field label="Contact number" name="contact_number">
                    <x-ui.input name="contact_number" :value="old('contact_number')" placeholder="01XXXXXXXXX" />
                </x-ui.field>
            </div>

            <x-ui.field label="Description" name="content" required>
                <x-ui.textarea name="content" rows="3" placeholder="Condition, age, why you are selling…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="Photo" name="image">
                <input type="file" name="image" accept="image/*" data-preview="#market-preview"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                <img id="market-preview" alt="" class="mt-3 hidden max-h-48 rounded-xl object-cover ring-1 ring-ink-200">
            </x-ui.field>
        </x-post.composer>
    @endif

    @if ($posts->isEmpty())
        <x-ui.empty-state title="Nothing for sale" description="Listings from other students will appear here." />
    @else
        <div class="grid gap-5 md:grid-cols-2">
            @foreach ($posts as $post)
                <x-post.card :post="$post" :title="$post->marketplace->product_name">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="text-lg font-bold text-hive-700">
                            {{ Number::currency((float) $post->marketplace->price, 'BDT') }}
                        </span>

                        <x-ui.badge>{{ $conditions[$post->marketplace->condition] ?? $post->marketplace->condition }}</x-ui.badge>

                        @if ($post->marketplace->is_sold)
                            <x-ui.badge tone="danger">Sold</x-ui.badge>
                        @endif
                    </div>

                    <x-post.image :post="$post" :path="$post->marketplace->image_path" :alt="$post->marketplace->product_name" />

                    <p class="mt-3 whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

                    @if ($post->marketplace->contact_number)
                        <a href="tel:{{ $post->marketplace->contact_number }}"
                           class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-hive-700 hover:text-hive-800">
                            <x-icon name="phone" class="size-4" />
                            {{ $post->marketplace->contact_number }}
                        </a>
                    @endif

                    @if (! $post->marketplace->is_sold && auth()->id() === $post->user_id)
                        <form method="POST" action="{{ route('marketplace.sold', $post) }}" class="mt-3"
                              data-confirm="Mark this product as sold?">
                            @csrf
                            @method('PATCH')
                            <x-ui.button type="submit" variant="secondary" size="sm">Mark as sold</x-ui.button>
                        </form>
                    @endif
                </x-post.card>
            @endforeach
        </div>
    @endif

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
