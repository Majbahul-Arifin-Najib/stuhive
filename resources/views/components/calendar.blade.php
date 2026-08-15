@props(['month', 'weeks', 'entries'])

<x-ui.card padded="false" {{ $attributes }}>
    <div class="flex items-center justify-between border-b border-ink-200 px-5 py-4">
        <div>
            <p class="text-sm font-semibold text-ink-900">{{ $month->format('F Y') }}</p>
            <p class="text-xs text-ink-500">Events you are interested in, exams and consultations.</p>
        </div>

        <div class="flex items-center gap-1">
            <a href="{{ route('dashboard', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}"
               class="rounded-lg p-1.5 text-ink-500 transition hover:bg-ink-100 hover:text-ink-900">
                <x-icon name="chevron-left" class="size-4" />
                <span class="sr-only">Previous month</span>
            </a>

            <a href="{{ route('dashboard') }}"
               class="rounded-lg px-2 py-1 text-xs font-medium text-ink-600 transition hover:bg-ink-100">Today</a>

            <a href="{{ route('dashboard', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}"
               class="rounded-lg p-1.5 text-ink-500 transition hover:bg-ink-100 hover:text-ink-900">
                <x-icon name="chevron-right" class="size-4" />
                <span class="sr-only">Next month</span>
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <div class="min-w-[36rem]">
            <div class="grid grid-cols-7 border-b border-ink-200 bg-ink-50">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $label)
                    <div class="px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide text-ink-500">
                        {{ $label }}
                    </div>
                @endforeach
            </div>

            @foreach ($weeks as $week)
                <div class="grid grid-cols-7 border-b border-ink-200 last:border-0">
                    @foreach ($week as $day)
                        @php
                            $dayEntries = $entries->get($day['date']->toDateString(), collect());
                        @endphp

                        <div @class([
                            'min-h-24 border-r border-ink-200 p-1.5 last:border-0',
                            'bg-ink-50/60' => ! $day['inMonth'],
                        ])>
                            <span @class([
                                'inline-flex size-6 items-center justify-center rounded-full text-xs font-medium',
                                'bg-hive-600 text-white' => $day['isToday'],
                                'text-ink-400' => ! $day['inMonth'] && ! $day['isToday'],
                                'text-ink-700' => $day['inMonth'] && ! $day['isToday'],
                            ])>{{ $day['date']->day }}</span>

                            <div class="mt-1 space-y-1">
                                @foreach ($dayEntries->take(3) as $entry)
                                    <a href="{{ $entry->url }}"
                                       class="block truncate rounded px-1.5 py-0.5 text-[11px] font-medium {{ $entry->toneClasses() }}"
                                       title="{{ $entry->title }} · {{ $entry->time() }}">
                                        {{ $entry->title }}
                                    </a>
                                @endforeach

                                @if ($dayEntries->count() > 3)
                                    <p class="px-1.5 text-[11px] text-ink-500">+{{ $dayEntries->count() - 3 }} more</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-ui.card>
