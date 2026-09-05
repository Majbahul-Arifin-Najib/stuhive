@php
    $user = auth()->user();
@endphp

<x-layouts.app title="Consultation Hub">
    <x-ui.page-header
        title="Consultation Hub"
        description="{{ $user->isFaculty() ? 'Publish slots and see who booked them.' : 'Book a slot with your faculty.' }}" />

    @if ($user->isFaculty())
        <x-post.composer :action="route('consultations.store')" title="Publish a consultation slot" submit="Publish slot">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="Course code" name="course_code" required>
                    <x-ui.input name="course_code" :value="old('course_code')" placeholder="CSE220" required />
                </x-ui.field>

                <x-ui.field label="Room" name="room">
                    <x-ui.input name="room" :value="old('room')" placeholder="UB4-08" />
                </x-ui.field>

                <x-ui.field label="Date" name="consultation_date" required>
                    <x-ui.input name="consultation_date" type="date" :value="old('consultation_date')" required />
                </x-ui.field>

                <x-ui.field label="Time" name="consultation_time" required>
                    <x-ui.input name="consultation_time" type="time" :value="old('consultation_time')" required />
                </x-ui.field>

                <x-ui.field label="Capacity" name="capacity" hint="How many students can book this slot." required>
                    <x-ui.input name="capacity" type="number" min="1" max="100" :value="old('capacity', 10)" required />
                </x-ui.field>
            </div>

            <x-ui.field label="Notes for students" name="content" required>
                <x-ui.textarea name="content" rows="2" placeholder="What this consultation covers…" required>{{ old('content') }}</x-ui.textarea>
            </x-ui.field>
        </x-post.composer>
    @endif

    @forelse ($posts as $post)
        @php
            $consultation = $post->consultation;
            $bookings = $consultation->bookings;
            $myBooking = $bookings->firstWhere('user_id', $user->id);
            $isFull = $bookings->count() >= $consultation->capacity;
        @endphp

        <x-post.card :post="$post" :title="$consultation->course_code.' consultation'">
            <div class="mb-3 flex flex-wrap gap-2">
                <x-ui.badge tone="hive">
                    <x-icon name="calendar" class="size-3.5" />
                    {{ $consultation->consultation_day }}, {{ $consultation->consultation_date->format('j M Y') }}
                </x-ui.badge>

                <x-ui.badge>
                    <x-icon name="clock" class="size-3.5" />
                    {{ $consultation->startsAt()->format('g:i A') }}
                </x-ui.badge>

                @if ($consultation->room)
                    <x-ui.badge>
                        <x-icon name="map-pin" class="size-3.5" />
                        {{ $consultation->room }}
                    </x-ui.badge>
                @endif

                <x-ui.badge :tone="$isFull ? 'danger' : 'success'">
                    {{ $bookings->count() }} / {{ $consultation->capacity }} booked
                </x-ui.badge>

                @if ($consultation->wasPostponed())
                    <x-ui.badge tone="info">Rescheduled</x-ui.badge>
                @endif
            </div>

            <p class="whitespace-pre-line text-sm text-ink-700">{{ $post->content }}</p>

            @if ($consultation->postpone_reason)
                <p class="mt-2 text-xs italic text-ink-500">Reason: {{ $consultation->postpone_reason }}</p>
            @endif

            @if ($user->isFaculty() && $user->id === $post->user_id)
                <div class="mt-4 rounded-xl bg-ink-50 p-4 ring-1 ring-ink-200">
                    <p class="text-sm font-semibold text-ink-900">
                        {{ $bookings->count() }} {{ Str::plural('student', $bookings->count()) }} applied
                    </p>

                    @if ($bookings->isNotEmpty())
                        <ul class="mt-3 space-y-2">
                            @foreach ($bookings as $booking)
                                <li class="flex items-start gap-2.5">
                                    <x-ui.avatar :user="$booking->user" size="sm" />

                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink-900">{{ $booking->user->name }}</p>
                                        @if ($booking->topic)
                                            <p class="text-xs text-ink-500">{{ $booking->topic }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <button type="button" data-toggle="#postpone-{{ $post->id }}"
                            class="mt-3 text-xs font-semibold text-hive-700 hover:text-hive-800">
                        Postpone this consultation
                    </button>

                    <form id="postpone-{{ $post->id }}" method="POST"
                          action="{{ route('consultations.postpone', $post) }}" class="mt-3 hidden space-y-3">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-ui.field label="New date" name="consultation_date" required>
                                <x-ui.input name="consultation_date" type="date"
                                            :value="$consultation->consultation_date->toDateString()" required />
                            </x-ui.field>

                            <x-ui.field label="New time" name="consultation_time" required>
                                <x-ui.input name="consultation_time" type="time"
                                            :value="substr($consultation->consultation_time, 0, 5)" required />
                            </x-ui.field>
                        </div>

                        <x-ui.field label="Reason" name="postpone_reason">
                            <x-ui.input name="postpone_reason" placeholder="Faculty meeting clash" />
                        </x-ui.field>

                        <x-ui.button type="submit" size="sm">Reschedule &amp; notify students</x-ui.button>
                    </form>
                </div>
            @elseif ($user->isStudent())
                <div class="mt-4">
                    @if ($myBooking)
                        <div class="flex flex-wrap items-center gap-3">
                            <x-ui.badge tone="success">
                                <x-icon name="check-circle" class="size-3.5" />
                                You are booked
                            </x-ui.badge>

                            <form method="POST" action="{{ route('consultations.cancel', $post) }}"
                                  data-confirm="Cancel your booking?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">
                                    Cancel booking
                                </button>
                            </form>
                        </div>
                    @elseif ($isFull)
                        <x-ui.badge tone="danger">This slot is full</x-ui.badge>
                    @else
                        <form method="POST" action="{{ route('consultations.book', $post) }}" class="flex flex-wrap items-end gap-3">
                            @csrf

                            <x-ui.field label="What do you want to discuss?" name="topic" class="min-w-64 flex-1">
                                <x-ui.input name="topic" placeholder="Doubts about assignment 2" />
                            </x-ui.field>

                            <x-ui.button type="submit">Book slot</x-ui.button>
                        </form>
                    @endif
                </div>
            @endif
        </x-post.card>
    @empty
        <x-ui.empty-state
            title="No consultation slots"
            description="{{ $user->isFaculty() ? 'Publish a slot so students can book time with you.' : 'Faculty have not published any slots yet.' }}" />
    @endforelse

    <div>{{ $posts->links() }}</div>
</x-layouts.app>
