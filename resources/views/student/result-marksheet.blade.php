@extends('layouts.app')

@section('title', 'Marksheet')

@section('content')
<section class="py-6 bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">
                    Marksheet
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $student['full_name'] ?? 'Student' }}
                    @if(!empty($enrollment['class_name']))
                        · Class <b class="text-slate-600">{{ $enrollment['class_name'] }}</b>
                    @endif
                    @if(!empty($enrollment['section_name']))
                        · Section <b class="text-slate-600">{{ $enrollment['section_name'] }}</b>
                    @endif
                    @if(!empty($enrollment['academic_year']))
                        · <b class="text-slate-600">{{ $enrollment['academic_year'] }}</b>
                    @endif
                </p>
            </div>
            @if(!empty($student['id']))
                <a href="{{ route('student.result.profile', $student['id']) }}"
                    class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <i class="fas fa-arrow-left"></i>
                    All Exams
                </a>
            @endif
        </div>

        {{-- Error --}}
        @if($error)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ $error }}
            </div>
        @else
            {{-- Exam switcher + print --}}
            @if(!empty($exams))
                <div
                    class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm mb-4 flex flex-wrap items-center gap-3 print:hidden">
                    <label for="exam_selector" class="text-xs font-semibold text-slate-600">Exam</label>
                    <select id="exam_selector"
                        class="h-10 min-w-44 flex-1 rounded-lg border border-slate-300 px-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @foreach($exams as $exam)
                            <option
                                value="{{ route('student.result.marksheet', ['student' => $student['id'], 'enrollment_id' => $enrollment['id'], 'exam_id' => $exam['id']]) }}"
                                {{ (int) ($exam['id'] ?? 0) === (int) $selected_exam_id ? 'selected' : '' }}>
                                {{ $exam['name'] }}
                            </option>
                        @endforeach
                    </select>

                    @if($result_gate === 'published' && $print_url)
                        <a href="{{ $print_url }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700">
                            <i class="fas fa-print"></i>
                            Print Marksheet
                        </a>
                    @endif
                </div>
            @endif

            {{-- Publish gate --}}
            @if($result_gate !== 'published')
                @if($result_gate === 'withheld')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-eye-slash mr-1"></i>
                        Result withheld.
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-500">
                        <i class="fas fa-lock mr-1"></i>
                        Result has not been published yet.
                    </div>
                @endif
            @else
                {{-- Marksheet --}}
                @include('student.partials.marksheet', [
                    'marksheet' => $marksheet,
                    'headKeys' => $headKeys,
                ])
            @endif
        @endif
    </div>
</section>

@if(empty($error) && !empty($exams))
    <script>
        document.getElementById('exam_selector')?.addEventListener('change', function () {
            if (this.value) {
                window.location.href = this.value;
            }
        });
    </script>
@endif
@endsection
