<x-layouts.app title="Polls &amp; Voting">
    <x-ui.page-header title="Polls &amp; Voting" description="Ask the student body a yes or no question." />

    @can('create', App\Models\Poll::class)
        <x-post.composer :action="route('polls.store')" title="Start a poll" submit="Publish poll" multipart>
            <x-ui.field label="Question" name="question" required>
                <x-ui.textarea name="question" rows="2" placeholder="Should the library stay open until midnight?" required>{{ old('question') }}</x-ui.textarea>
            </x-ui.field>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Image" name="image">
                    <input type="file" name="image" accept="image/*" data-preview="#poll-preview"
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-hive-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hive-800 hover:file:bg-hive-200">
                    <img id="poll-preview" alt="" class="mt-3 hidden max-h-40 rounded-xl object-cover ring-1 ring-ink-200">
                </x-ui.field>

                <x-ui.field label="Closes at" name="closes_at" hint="Optional — leave blank to keep it open.">
                    <x-ui.input name="closes_at" type="datetime-local" :value="old('closes_at')" />
                </x-ui.field>
            </div>
        </x-post.composer>
    @endcan

    @forelse ($polls as $poll)
        @php
            $myVote = $poll->votes->firstWhere('user_id', auth()->id());
            $total = $poll->yes_count + $poll->no_count;
            $yesPercent = $total > 0 ? round($poll->yes_count / $total * 100) : 0;
            $noPercent = $total > 0 ? 100 - $yesPercent : 0;
        @endphp

        <x-ui.card>
            <div class="flex items-start gap-3">
                <x-ui.avatar :user="$poll->author" size="md" />

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink-900">{{ $poll->author->name }}</p>
                    <p class="text-xs text-ink-500">{{ $poll->created_at->diffForHumans() }}</p>
                </div>

                @can('delete', $poll)
                    <form method="POST" action="{{ route('polls.destroy', $poll) }}" data-confirm="Delete this poll?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg p-1.5 text-ink-400 transition hover:bg-rose-50 hover:text-rose-600">
                            <x-icon name="trash" class="size-4" />
                            <span class="sr-only">Delete poll</span>
                        </button>
                    </form>
                @endcan
            </div>

            <p class="mt-4 text-base font-semibold text-ink-900">{{ $poll->question }}</p>

            @if ($poll->image_path)
                <img src="{{ Storage::disk(config('stuhive.uploads.disk'))->url($poll->image_path) }}" alt=""
                     loading="lazy" class="mt-3 max-h-80 w-full rounded-xl object-cover ring-1 ring-ink-200">
            @endif

            @php
                $options = [
                    ['label' => 'Yes', 'value' => true, 'count' => $poll->yes_count, 'percent' => $yesPercent,
                        'bar' => 'bg-emerald-200', 'tick' => 'text-emerald-700'],
                    ['label' => 'No', 'value' => false, 'count' => $poll->no_count, 'percent' => $noPercent,
                        'bar' => 'bg-rose-200', 'tick' => 'text-rose-700'],
                ];
            @endphp

            <div class="mt-4 space-y-2">
                @foreach ($options as $option)
                    <div class="relative overflow-hidden rounded-lg bg-ink-100">
                        <div class="absolute inset-y-0 left-0 transition-all {{ $option['bar'] }}"
                             style="width: {{ $option['percent'] }}%"></div>

                        <div class="relative flex items-center justify-between px-3 py-2">
                            <span class="flex items-center gap-2 text-sm font-medium text-ink-800">
                                @if ($myVote && $myVote->choice === $option['value'])
                                    <x-icon name="check-circle" class="size-4 {{ $option['tick'] }}" />
                                @endif
                                {{ $option['label'] }}
                            </span>

                            <span class="text-sm font-semibold text-ink-700">{{ $option['count'] }} · {{ $option['percent'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                @can('vote', $poll)
                    @foreach ([['Vote yes', 1, 'success'], ['Vote no', 0, 'danger']] as [$label, $value, $variant])
                        <form method="POST" action="{{ route('polls.vote', $poll) }}">
                            @csrf
                            <input type="hidden" name="choice" value="{{ $value }}">
                            <x-ui.button type="submit" size="sm" :variant="$variant">{{ $label }}</x-ui.button>
                        </form>
                    @endforeach
                @elseif ($poll->isClosed())
                    <x-ui.badge tone="neutral">Voting closed</x-ui.badge>
                @endcan

                <p class="ml-auto text-xs text-ink-500">{{ $total }} {{ Str::plural('vote', $total) }}</p>
            </div>
        </x-ui.card>
    @empty
        <x-ui.empty-state title="No polls yet" description="Start one to collect opinions from other students." />
    @endforelse

    <div>{{ $polls->links() }}</div>
</x-layouts.app>
