@php
    $ms = $marksheet ?? [];
    $heads = $headKeys ?? [];
@endphp

<div class="rounded-xl border border-slate-200 overflow-hidden">

    {{-- Marksheet header strip --}}
    <div class="flex flex-wrap items-center gap-4 bg-slate-50/70 px-4 py-3 border-b border-slate-200">
        @if(!empty($ms['image']))
            <img src="{{ $ms['image'] }}" alt="{{ $ms['name'] ?? '' }}"
                class="w-12 h-12 rounded-lg object-cover border border-slate-200">
        @endif
        <div class="flex-1 min-w-[220px]">
            <p class="font-bold text-slate-900 text-sm">{{ $ms['name'] ?? 'N/A' }}</p>
            <p class="text-xs text-slate-500">
                Roll: <span class="font-semibold text-slate-700">{{ $ms['roll_no'] ?? '—' }}</span>
                @if(!empty($ms['class_name']))
                    · Class: <span class="font-semibold text-slate-700">{{ $ms['class_name'] }}</span>
                @endif
                @if(!empty($ms['section']))
                    · Section: <span class="font-semibold text-slate-700">{{ $ms['section'] }}</span>
                @endif
                @if(!empty($ms['group']))
                    · Group: <span class="font-semibold text-slate-700">{{ $ms['group'] }}</span>
                @endif
            </p>
        </div>
        <div class="text-xs text-slate-500">
            @if(!empty($ms['father_name']))
                <p>Father: <span class="font-semibold text-slate-700">{{ $ms['father_name'] }}</span></p>
            @endif
            @if(!empty($ms['mother_name']))
                <p>Mother: <span class="font-semibold text-slate-700">{{ $ms['mother_name'] }}</span></p>
            @endif
        </div>
    </div>

    {{-- Overall summary --}}
    @if(($ms['overall_total'] ?? null) !== null || ($ms['overall_gpa'] ?? null) !== null)
        <div class="flex flex-wrap gap-3 px-4 py-3 bg-slate-50/70 border-t border-slate-200">
            @if(($ms['overall_total'] ?? null) !== null)
                <div class="rounded-lg bg-white border border-slate-200 px-3 py-2 min-w-[90px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Total</p>
                    <p class="font-black text-slate-900 text-sm">{{ $ms['overall_total'] }}</p>
                </div>
            @endif
            @if(($ms['overall_gpa'] ?? null) !== null)
                <div class="rounded-lg bg-white border border-slate-200 px-3 py-2 min-w-[90px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">GPA</p>
                    <p class="font-black text-slate-900 text-sm">{{ number_format((float) $ms['overall_gpa'], 2) }}</p>
                </div>
            @endif
            @if(($ms['overall_gpa_without_fourth'] ?? null) !== null)
                <div class="rounded-lg bg-white border border-slate-200 px-3 py-2 min-w-[110px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">GPA (w/o 4th)</p>
                    <p class="font-black text-slate-900 text-sm">{{ number_format((float) $ms['overall_gpa_without_fourth'], 2) }}</p>
                </div>
            @endif
            @if(!empty($ms['overall_grade']))
                <div class="rounded-lg bg-white border border-slate-200 px-3 py-2 min-w-[90px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Grade</p>
                    <p class="font-black text-slate-900 text-sm">{{ $ms['overall_grade'] }}</p>
                </div>
            @endif
            @if(($ms['class_position'] ?? null) !== null || ($ms['section_position'] ?? null) !== null)
                <div class="rounded-lg bg-white border border-slate-200 px-3 py-2 min-w-[130px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Position</p>
                    <p class="font-black text-slate-900 text-sm">
                        Class #{{ $ms['class_position'] ?? '—' }} · Section #{{ $ms['section_position'] ?? '—' }}
                    </p>
                </div>
            @endif
            @if(!empty($ms['has_fail']))
                <div class="rounded-lg bg-red-50 border border-red-200 px-3 py-2 min-w-[90px]">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-red-400">Result</p>
                    <p class="font-black text-red-600 text-sm">Failed</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Subjects table --}}
    @if(empty($ms['subjects']))
        <div class="px-4 py-8 text-center text-sm text-slate-400">
            No subject-wise marks available for this exam.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-3 py-2.5 w-8">#</th>
                        <th class="px-3 py-2.5">Subject</th>
                        @foreach($heads as $head)
                            <th class="px-3 py-2.5 text-center whitespace-nowrap">
                                {{ ucwords(str_replace('_', ' ', $head)) }}
                            </th>
                        @endforeach
                        <th class="px-3 py-2.5 text-center">Total</th>
                        <th class="px-3 py-2.5 text-center">Grade</th>
                        <th class="px-3 py-2.5 text-center">GPA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($ms['subjects'] as $subject)
                        @php
                            $isFourth = !empty($subject['is_fourth']);
                            $isAbsent = !empty($subject['is_absent']);
                            $isCombined = !empty($subject['is_combined']);
                            $hasIncourse = !empty($subject['has_incourse']);
                            $subjectTotal = $subject['total'] ?? ($subject['total_mark'] ?? 0);
                            $subjectGpa = $subject['gpa'] ?? null;
                        @endphp
                        <tr class="{{ $isFourth ? 'bg-indigo-50/60' : 'hover:bg-slate-50' }}">
                            <td class="px-3 py-2.5 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-start gap-1.5">
                                    <span class="font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $subject['subject_name'] ?? 'N/A' }}
                                    </span>
                                    @if(!empty($subject['full_mark']))
                                        <span class="text-slate-400 font-normal">({{ $subject['full_mark'] }})</span>
                                    @endif
                                </div>
                                @if($isFourth || $isAbsent || $isCombined || $hasIncourse)
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if($isFourth)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">4th</span>
                                        @endif
                                        @if($isCombined)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Combined</span>
                                        @endif
                                        @if($isAbsent)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">Absent</span>
                                        @endif
                                        @if($hasIncourse)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                                In-course {{ $subject['incourse_total_mark'] ?? 0 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            @foreach($heads as $head)
                                <td class="px-3 py-2.5 text-center text-slate-600 whitespace-nowrap">
                                    {{ $subject[$head] ?? '—' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2.5 text-center font-bold text-slate-900 whitespace-nowrap">
                                {{ number_format((float) $subjectTotal, 0) }}
                            </td>
                            <td class="px-3 py-2.5 text-center font-semibold text-slate-700">
                                {{ $subject['grade'] ?? '—' }}
                            </td>
                            <td class="px-3 py-2.5 text-center font-semibold text-slate-700 whitespace-nowrap">
                                {{ $subjectGpa !== null ? number_format((float) $subjectGpa, 2) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
