<x-layouts.app title="Course Discussion &amp; Review">
    <x-ui.page-header title="Course Discussion &amp; Review" description="Share your experience of a course and its faculty. Not visible to faculty accounts." />

    @if (auth()->user()->isStudent())
        <x-post.composer :action="route('course_reviews.store')" title="Write a review" submit="Share review" multipart>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.field label="Course code" name="course_code" required>
                    <x-ui.input name="course_code" :value="old('course_code')" placeholder="CSE370" required />
                </x-ui.field>

                <x-ui.field label="Faculty initial" name="faculty_initial">
                    <x-ui.input name="faculty_initial" :value="old('faculty_initial')" placeholder="MAR" />
                </x-ui.field>

                <x-ui.field label="Rating" name="rating">
                    <x-ui.select name="rating">
                        <option value="">No rating</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((int) old('rating') === $i)>{{ $i }} / 5</option>
                        @endfor
                    </x-ui.select>
                </x-ui.field>
            </div>

            <x-ui.field label="Your experience" name="content" required>
                <x-ui.textarea name="content" rows="4" placeholder="Workload, grading, teaching style, tips for future students…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>

            <x-ui.field label="Attachment" name="image">
                <input type="file" name="image" accept="image/*" data-preview="#review-preview"
                       class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                <img id="review-preview" alt="" class="mt-3 hidden max-h-48 rounded-xl object-cover ring-1 ring-ink-200">
            </x-ui.field>
        </x-post.composer>
    @endif

    <form method="GET" action="{{ route('course_reviews.index') }}" class="flex gap-2">
        <div class="relative flex-1">
            <x-icon name="magnifier" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
            <x-ui.input name="q" :value="$search" placeholder="Search by course code or faculty initial" class="pl-9" />
        </div>

        <x-ui.button type="submit" variant="secondary">Search</x-ui.button>

        @if ($search !== '')
            <x-ui.button as="a" href="{{ route('course_reviews.index') }}" variant="ghost">Clear</x-ui.button>
        @endif
    </form>

    @forelse ($posts as $post)
        <x-post.card :post="$post">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <x-ui.badge tone="hive">{{ $post->courseReview->course_code }}</x-ui.badge>

                @if ($post->courseReview->faculty_initial)
                    <x-ui.badge tone="info">{{ $post->courseReview->faculty_initial }}</x-ui.badge>
                @endif

                @if ($post->courseReview->rating)
                    <span class="inline-flex items-center gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-icon name="star"
                                    class="size-4 {{ $i <= $post->courseReview->rating ? 'text-hive-500' : 'text-ink-300' }}" />
                        @endfor
                    </span>
                @endif
            </div>

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            <div class="mt-3">
                <x-post.image :post="$post" :path="$post->courseReview->image_path" alt="Review attachment" />
            </div>
        </x-post.card>
    @empty
        <x-ui.empty-state
            title="No reviews yet"
            description="{{ $search !== '' ? 'Nothing matched your search.' : 'Share your experience to help other students choose.' }}" />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
