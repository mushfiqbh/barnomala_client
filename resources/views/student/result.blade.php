@extends('layouts.app')

@section('title', 'Result')

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('content')
<section class="py-6 print:py-0 bg-slate-50 print:bg-white min-h-screen">
    <div class="w-full md:max-w-5xl mx-auto sm:px-6 lg:px-8 print:px-0">

        <!-- Header -->
        <div class="mb-5 print:mb-3 mx-4 md:mx-0">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">
                        Result Lookup
                    </h1>
                </div>
            </div>
        </div>

        <!-- Search Panel -->
        <div class="bg-white border border-slate-50 rounded-2xl p-4 mx-4 md:mx-0 shadow-sm print:hidden">

            <form action="{{ route('student.result') }}" method="GET">

                <!-- Class & Roll Mode -->
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">

                        <!-- Class -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Class</label>
                            <select id="class_id" name="class_id"
                                class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Select</option>
                                @forelse($filterOptions['classes'] ?? [] as $class)
                                    <option value="{{ $class['id'] }}"
                                        {{ $filters['class_id'] == $class['id'] ? 'selected' : '' }}>
                                        {{ $class['name'] }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>

                        <!-- Section -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Section</label>
                            <select id="section_id" name="section_id"
                                class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Select</option>
                            </select>
                        </div>

                        <!-- Year -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Year</label>
                            <select id="year" name="year"
                                class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="">Select</option>
                            </select>
                        </div>

                        <!-- Roll -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Roll</label>
                            <input type="number" name="roll" id="roll"
                                value="{{ $filters['roll'] ?? '' }}"
                                placeholder="12"
                                class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <!-- Student ID -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">OR Student ID</label>
                            <input type="text" name="student_id" id="student_id"
                                value="{{ $filters['student_id'] ?? '' }}"
                                placeholder="CUST-1001"
                                class="w-full h-10 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <button type="submit"
                            class="h-10 px-5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            const filterOptions = @json($filterOptions ?? []);
            const selectedClassId = '{{ $filters['class_id'] ?? '' }}';
            const selectedSectionId = '{{ $filters['section_id'] ?? '' }}';
            const selectedYear = '{{ $filters['year'] ?? '' }}';

            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section_id');
            const yearSelect = document.getElementById('year');

            function updateSectionsAndYears() {
                const classId = parseInt(classSelect.value);
                const selectedClass = filterOptions.classes?.find(c => c.id === classId);

                sectionSelect.innerHTML = '<option value="">Select</option>';
                yearSelect.innerHTML = '<option value="">Select</option>';

                if (selectedClass?.sections) {
                    selectedClass.sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        if (section.id == selectedSectionId) option.selected = true;
                        sectionSelect.appendChild(option);
                    });
                }

                if (selectedClass?.years) {
                    selectedClass.years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        if (year == selectedYear) option.selected = true;
                        yearSelect.appendChild(option);
                    });
                }
            }

            classSelect.addEventListener('change', updateSectionsAndYears);

            if (selectedClassId) updateSectionsAndYears();
        </script>

        <!-- Error -->
        @if($error)
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 print:hidden">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $error }}
            </div>
        @endif

        <!-- Results -->
        @if($student)
            <!-- Exams -->
            <div class="w-full mt-5 space-y-4">
                @forelse($exams as $exam)
                    @php
                        $isUnpublished = ($exam['status'] ?? '') === 'unpublished';
                    @endphp
                    <div x-data="{ open: {{ !$isUnpublished && $loop->first ? 'true' : 'false' }} }"
                        class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                        <!-- Exam Summary -->
                        <button type="button" @disabled($isUnpublished)
                            @if(!$isUnpublished) @click="open = !open" @endif
                            class="w-full text-left p-4 flex flex-wrap items-center gap-x-6 gap-y-3 {{ $isUnpublished ? 'cursor-not-allowed bg-slate-50' : 'hover:bg-slate-50 transition' }}">
                            <div class="flex-1 min-w-50">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold {{ $isUnpublished ? 'text-slate-500' : 'text-slate-900' }}">
                                        {{ $exam['name'] }}
                                    </h3>
                                    @php
                                        $status = $exam['status'] ?? 'unknown';
                                        $statusClasses = match ($status) {
                                            'published' => 'bg-green-100 text-green-700',
                                            'withheld' => 'bg-amber-100 text-amber-700',
                                            'unpublished' => 'bg-slate-200 text-slate-500',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $statusClasses }}">
                                        @if($isUnpublished)
                                            <i class="fas fa-lock mr-1"></i>
                                        @endif
                                        {{ $status }}
                                    </span>
                                </div>

                                @if($isUnpublished)
                                    <p class="text-xs text-slate-400 mt-1.5">
                                        Result has not been published yet.
                                    </p>
                                @else
                                    <p class="text-xs text-slate-500 mt-1.5">
                                        @if($exam['total_marks'] !== null)
                                            Total: <span class="font-semibold text-slate-600">{{ $exam['total_marks'] }}</span> ·
                                        @endif
                                        Fail: <span class="font-semibold {{ ($exam['fail_count'] ?? 0) > 0 ? 'text-red-600' : 'text-slate-600' }}">{{ $exam['fail_count'] ?? 0 }}</span> ·
                                        Absent: <span class="font-semibold text-slate-600">{{ $exam['absent_count'] ?? 0 }}</span>
                                    </p>
                                @endif
                            </div>

                            @if($isUnpublished)
                                <div class="text-right shrink-0">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wide">
                                        <i class="fas fa-lock"></i>
                                        Not Released
                                    </span>
                                </div>
                            @else
                                <div class="text-right shrink-0">
                                    <div class="flex items-baseline gap-2 justify-end">
                                        <span class="text-2xl font-black text-slate-900">
                                            {{ $exam['grade'] ?? '—' }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            GPA {{ $exam['gpa'] ?? '—' }}
                                        </span>
                                    </div>
                                    @if(($exam['class_position'] ?? null) !== null || ($exam['section_position'] ?? null) !== null)
                                        <p class="text-xs text-slate-500 mt-1">
                                            <i class="fas fa-trophy text-amber-400 mr-1"></i>
                                            Class #{{ $exam['class_position'] ?? '—' }}
                                            · Section #{{ $exam['section_position'] ?? '—' }}
                                        </p>
                                    @endif
                                </div>

                                <i class="fas fa-chevron-down text-slate-400 transition-transform"
                                    :class="open ? 'rotate-180' : ''"></i>
                            @endif
                        </button>

                        <!-- Marksheet -->
                        @if(!$isUnpublished)
                            <div x-show="open" x-cloak>
                                <div class="border-t border-slate-200 p-0">
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                        @if(!empty($exam['print_url']))
                                            <a href="{{ $exam['print_url'] }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-2 mx-2 my-4 px-3 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700 print:hidden">
                                                <i class="fas fa-print"></i>
                                                Print Marksheet
                                            </a>
                                        @endif
                                    </div>

                                    @include('student.partials.marksheet', [
                                        'marksheet' => $exam['marksheet'] ?? [],
                                        'headKeys' => $headKeys,
                                    ])
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-400">
                        <i class="fas fa-clipboard-check text-4xl mb-3"></i>
                        <p class="font-semibold text-slate-500">No exam results found</p>
                        <p class="text-sm mt-1">No terminal exam results are available for this enrollment.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>
@endsection