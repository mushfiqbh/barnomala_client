@extends('layouts.app')

@section('title', 'Student Result')

@section('content')
<section class="py-6 bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">
                        Student Result
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Select an exam to view the marksheet.
                    </p>
                </div>
                <a href="{{ route('student.result') }}"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-search"></i>
                    Lookup
                </a>
            </div>
        </div>

        {{-- Error --}}
        @if($error)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $error }}
            </div>
        @elseif($student)
            {{-- Student profile card --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-5">
                <div class="p-5 flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-black shrink-0">
                        {{ strtoupper(substr($student['full_name'] ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-black text-slate-900 truncate">
                            {{ $student['full_name'] ?? 'N/A' }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5 flex flex-wrap gap-x-2 gap-y-0.5">
                            @if(!empty($student['student_code']))
                                <span>Code: <b class="text-slate-700">{{ $student['student_code'] }}</b></span>
                            @endif
                            @if(!empty($student['custom_id']))
                                <span>ID: <b class="text-slate-700">{{ $student['custom_id'] }}</b></span>
                            @endif
                            @if(!empty($student['gender']))
                                <span class="capitalize text-slate-400">{{ $student['gender'] }}</span>
                            @endif
                        </p>
                    </div>
                    @if(!empty($school['name']) || !empty($school['logo']))
                        <div class="text-right shrink-0 hidden sm:block">
                            @if(!empty($school['logo']))
                                <img src="{{ $school['logo'] }}" alt="{{ $school['name'] }}"
                                    class="h-10 w-10 object-contain mx-auto">
                            @endif
                            <p class="text-xs font-semibold text-slate-600 mt-1 max-w-32 truncate">
                                {{ $school['name'] }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Enrollments --}}
            <div class="space-y-4">
                @forelse($enrollments as $enrollment)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                        {{-- Enrollment header --}}
                        <div
                            class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center gap-x-3 gap-y-2">
                            <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold">
                                Class {{ $enrollment['class_name'] ?? '—' }}
                            </span>
                            <div class="text-xs text-slate-600 flex flex-wrap gap-x-2 gap-y-0.5">
                                <span>Year: <b class="text-slate-700">{{ $enrollment['academic_year'] ?? '—' }}</b></span>
                                @if(!empty($enrollment['section_name']))
                                    <span>· Section: <b class="text-slate-700">{{ $enrollment['section_name'] }}</b></span>
                                @endif
                                @if(!empty($enrollment['group_name']))
                                    <span>· Group: <b class="text-slate-700">{{ $enrollment['group_name'] }}</b></span>
                                @endif
                                @if(!empty($enrollment['shift']))
                                    <span>· Shift: <b class="text-slate-700">{{ $enrollment['shift'] }}</b></span>
                                @endif
                            </div>
                            <div class="ml-auto text-xs font-semibold text-slate-700">
                                Roll: {{ $enrollment['roll_no'] ?? '—' }}
                            </div>
                        </div>

                        {{-- Exams --}}
                        <div class="divide-y divide-slate-100">
                            @forelse($enrollment['exams'] ?? [] as $exam)
                                @php
                                    $isPublished = ($exam['status'] ?? '') === 'published';
                                    $marksheetUrl = $isPublished ? route('student.result.marksheet', [
                                        'student' => $student['id'],
                                        'enrollment_id' => $enrollment['id'],
                                        'exam_id' => $exam['exam_id'],
                                    ]) : '#';
                                @endphp
                                <a href="{{ $marksheetUrl }}"
                                    class="group flex items-center gap-4 px-5 py-4 transition {{ $isPublished ? 'hover:bg-slate-50' : 'opacity-60 cursor-not-allowed' }}">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-sm font-bold text-slate-900">
                                                {{ $exam['exam_name'] ?? 'Exam' }}
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
                                            <span
                                                class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $statusClasses }}">
                                                {{ $status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1 flex flex-wrap gap-x-3 gap-y-0.5">
                                            @if(!$isPublished)
                                                <span>Result has not been published yet.</span>
                                            @else
                                                @if(($exam['gpa'] ?? null) !== null)
                                                    <span>GPA <b class="text-slate-700">{{ $exam['gpa'] }}</b></span>
                                                @endif
                                                @if(!empty($exam['grade']))
                                                    <span>Grade <b class="text-slate-700">{{ $exam['grade'] }}</b></span>
                                                @endif
                                                @if(($exam['class_position'] ?? null) !== null || ($exam['section_position'] ?? null) !== null)
                                                    <span>
                                                        <i class="fas fa-trophy text-amber-400 mr-0.5"></i>
                                                        Class #{{ $exam['class_position'] ?? '—' }}
                                                        · Section #{{ $exam['section_position'] ?? '—' }}
                                                    </span>
                                                @endif
                                                @if(($exam['fail_count'] ?? 0) > 0)
                                                    <span class="text-red-600">Fail: {{ $exam['fail_count'] }}</span>
                                                @endif
                                                @if(($exam['absent_count'] ?? 0) > 0)
                                                    <span>Absent: {{ $exam['absent_count'] }}</span>
                                                @endif
                                            @endif
                                        </p>
                                    </div>

                                    @if(!$isPublished)
                                        <span
                                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-200 text-slate-500 text-xs font-bold uppercase tracking-wide">
                                            <i class="fas fa-lock"></i>
                                            Not Released
                                        </span>
                                    @else
                                        <span
                                            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold group-hover:bg-blue-700">
                                            Marksheet
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </span>
                                    @endif
                                </a>
                            @empty
                                <div class="px-5 py-8 text-center text-sm text-slate-400">
                                    No exams available for this enrollment.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-400">
                        <i class="fas fa-graduation-cap text-4xl mb-3"></i>
                        <p class="font-semibold text-slate-500">No enrollments found</p>
                        <p class="text-sm mt-1">This student has no enrollments yet.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</section>
@endsection
