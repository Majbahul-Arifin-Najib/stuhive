<x-layouts.app title="Resources Library">
    <x-ui.page-header title="Resources Library" description="Share and download lecture notes by course code and faculty initial." />

    @if (auth()->user()->isStudent())
        <x-post.composer :action="route('resources.store')" title="Share notes" submit="Upload notes" multipart>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.field label="Title" name="title" class="sm:col-span-3" required>
                    <x-ui.input name="title" :value="old('title')" placeholder="Midterm summary — Trees & Graphs" required />
                </x-ui.field>

                <x-ui.field label="Course code" name="course_code" required>
                    <x-ui.input name="course_code" :value="old('course_code')" placeholder="CSE220" required />
                </x-ui.field>

                <x-ui.field label="Faculty initial" name="faculty_initial" required>
                    <x-ui.input name="faculty_initial" :value="old('faculty_initial')" placeholder="MAR" required />
                </x-ui.field>

                <x-ui.field label="PDF material" name="material" required>
                    <input type="file" name="material" accept="application/pdf" required
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                </x-ui.field>
            </div>

            <x-ui.field label="What is inside?" name="content" required>
                <x-ui.textarea name="content" rows="2" placeholder="Chapters covered, exam it helps with…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>
        </x-post.composer>
    @endif

    <form method="GET" action="{{ route('resources.index') }}" class="flex gap-2">
        <div class="relative flex-1">
            <x-icon name="magnifier" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
            <x-ui.input name="q" :value="$search" placeholder="Search by course code, faculty initial or title" class="pl-9" />
        </div>

        <x-ui.button type="submit" variant="secondary">Search</x-ui.button>

        @if ($search !== '')
            <x-ui.button as="a" href="{{ route('resources.index') }}" variant="ghost">Clear</x-ui.button>
        @endif
    </form>

    @forelse ($posts as $post)
        <x-post.card :post="$post" :title="$post->note->title">
            <div class="mb-3 flex flex-wrap gap-2">
                <x-ui.badge tone="hive">{{ $post->note->course_code }}</x-ui.badge>
                <x-ui.badge tone="info">{{ $post->note->faculty_initial }}</x-ui.badge>
                <x-ui.badge>{{ $post->note->readableSize() }}</x-ui.badge>
                <x-ui.badge>{{ $post->note->download_count }} downloads</x-ui.badge>
            </div>

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            <div class="mt-3 flex items-center gap-3 rounded-xl bg-ink-50 p-3 ring-1 ring-ink-200">
                <span class="rounded-lg bg-white p-2 text-hive-700 ring-1 ring-ink-200">
                    <x-icon name="book" />
                </span>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-ink-900">{{ $post->note->original_filename }}</p>
                    <p class="text-xs text-ink-500">PDF · {{ $post->note->readableSize() }}</p>
                </div>

                <x-ui.button as="a" href="{{ route('resources.download', $post) }}" size="sm">
                    <x-icon name="download" class="size-4" />
                    Download
                </x-ui.button>
            </div>
        </x-post.card>
    @empty
        <x-ui.empty-state
            title="No notes found"
            description="{{ $search !== '' ? 'Try a different course code or initial.' : 'Be the first to share your notes.' }}" />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
