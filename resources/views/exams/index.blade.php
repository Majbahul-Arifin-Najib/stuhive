@php
    $canManage = auth()->user()->isAdmin() || auth()->user()->isFaculty();
@endphp

<x-layouts.app title="Exam Schedule">
    <x-ui.page-header title="Exam Schedule" description="Search the published schedule. Exam dates appear on every student's calendar.">
        @if ($canManage)
            <x-ui.button type="button" data-modal-open="exam-create">
                <x-icon name="plus" class="size-4" />
                Add exam
            </x-ui.button>
        @endif
    </x-ui.page-header>

    @if ($canManage)
        <dialog id="exam-create" class="w-full max-w-lg rounded-2xl p-0 backdrop:bg-ink-900/40">
            <form method="POST" action="{{ route('exams.store') }}">
                @csrf

                <div class="border-b border-ink-200 px-5 py-4">
                    <p class="font-semibold text-ink-900">Add an exam</p>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <x-ui.field label="Course code" name="course_code" required>
                        <x-ui.input name="course_code" placeholder="CSE220" required />
                    </x-ui.field>

                    <x-ui.field label="Section" name="section" required>
                        <x-ui.input name="section" placeholder="07" required />
                    </x-ui.field>

                    <x-ui.field label="Date" name="exam_date" required>
                        <x-ui.input name="exam_date" type="date" required />
                    </x-ui.field>

                    <x-ui.field label="Time" name="exam_time" required>
                        <x-ui.input name="exam_time" type="time" required />
                    </x-ui.field>

                    <x-ui.field label="Room" name="room_number" class="sm:col-span-2" required>
                        <x-ui.input name="room_number" placeholder="UB4-08" required />
                    </x-ui.field>
                </div>

                <div class="flex justify-end gap-2 border-t border-ink-200 px-5 py-4">
                    <x-ui.button type="button" variant="secondary" data-modal-close>Cancel</x-ui.button>
                    <x-ui.button type="submit">Add exam</x-ui.button>
                </div>
            </form>
        </dialog>
    @endif

    <form method="GET" action="{{ route('exams.index') }}" class="flex flex-wrap gap-2">
        <div class="relative min-w-64 flex-1">
            <x-icon name="magnifier" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
            <x-ui.input name="q" :value="$search" placeholder="Search course, section, room or day" class="pl-9" />
        </div>

        <label class="flex items-center gap-2 rounded-lg bg-white px-3 text-sm text-ink-600 ring-1 ring-inset ring-ink-300">
            <input type="checkbox" name="past" value="1" @checked($showPast) data-auto-submit
                   class="size-4 rounded border-ink-300 text-hive-600 focus:ring-hive-600">
            Include past
        </label>

        <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
    </form>

    @if ($exams->isEmpty())
        <x-ui.empty-state
            title="No exams found"
            description="{{ $search !== '' ? 'Nothing matched your search.' : 'The schedule has not been published yet.' }}" />
    @else
        <x-ui.card padded="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200 text-sm">
                    <thead class="bg-ink-50 text-left text-xs font-semibold uppercase tracking-wide text-ink-500">
                        <tr>
                            <th class="px-5 py-3">Course</th>
                            <th class="px-5 py-3">Section</th>
                            <th class="px-5 py-3">Day</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Time</th>
                            <th class="px-5 py-3">Room</th>
                            @if ($canManage)
                                <th class="px-5 py-3"><span class="sr-only">Actions</span></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-ink-200">
                        @foreach ($exams as $exam)
                            <tr class="transition hover:bg-ink-50">
                                <td class="px-5 py-3 font-semibold text-ink-900">{{ $exam->course_code }}</td>
                                <td class="px-5 py-3 text-ink-600">{{ $exam->section }}</td>
                                <td class="px-5 py-3 text-ink-600">{{ $exam->day }}</td>
                                <td class="px-5 py-3 text-ink-600">{{ $exam->exam_date->format('j M Y') }}</td>
                                <td class="px-5 py-3 text-ink-600">{{ $exam->startsAt()->format('g:i A') }}</td>
                                <td class="px-5 py-3 text-ink-600">{{ $exam->room_number }}</td>

                                @if ($canManage)
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('exams.destroy', $exam) }}"
                                              data-confirm="Remove this exam from the schedule?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-ink-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                <x-icon name="trash" class="size-4" />
                                                <span class="sr-only">Remove exam</span>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    <div>{{ $exams->links() }}</div>
</x-layouts.app>
