@extends('layouts.app')

@section('title', 'Our Students')

@section('content')
<section class="py-6 print:py-0 bg-slate-50 print:bg-white min-h-screen">
    <div class="mx-auto max-w-[90%] px-4 sm:px-6 lg:px-8 print:px-0">

        <!-- Header -->
        <div class="mb-5 print:mb-3">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">
                        Student Lookup
                    </h1>
                </div>

                <button onclick="window.print()"
                    class="hidden print:hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Search Panel -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm print:hidden">
            <form action="{{ route('students.index') }}" method="GET" class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

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

                    <div class="mt-auto">
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
                {{ $error }}
            </div>
        @endif

        <!-- Result -->
        @if($student)

            <div id="result-area" class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-4">

                <!-- Main Card -->
                <div class="xl:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

                    <!-- Top -->
                    <div class="p-4 border-b border-slate-200">
                        <div class="flex flex-col sm:flex-row gap-4">

                            <div class="shrink-0">
                                @if($student['image_path'])
                                    <img
                                        src="{{ 'https://cloud.barnomala.com/storage/' . $student['image_path'] }}"
                                        alt="{{ $student['full_name'] }}"
                                        class="w-28 h-28 rounded-xl object-cover border border-slate-200">
                                @else
                                    <div class="w-28 h-28 rounded-xl border border-slate-200 bg-slate-100 flex items-center justify-center">
                                        <i class="fas fa-user text-3xl text-slate-400"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1">
                                <h2 class="text-xl font-black text-slate-900">
                                    {{ $student['full_name'] }}
                                </h2>

                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">

                                    <div><span class="text-slate-500">Code:</span>
                                        <span class="font-semibold text-slate-800">
                                            {{ $student['student_code'] ?? 'N/A' }}
                                        </span>
                                    </div>

                                    <div><span class="text-slate-500">Student ID:</span>
                                        <span class="font-semibold text-slate-800">
                                            {{ $student['student_id'] ?? 'N/A' }}
                                        </span>
                                    </div>

                                    <div><span class="text-slate-500">Gender:</span>
                                        <span class="font-semibold text-slate-800 capitalize">
                                            {{ $student['gender'] ?? 'N/A' }}
                                        </span>
                                    </div>

                                    <div>
                                        <span class="text-slate-500">Status:</span>
                                        <span class="ml-1 inline-flex px-2 py-0.5 rounded-full text-xs font-bold
                                            {{ $student['status'] == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $student['status'] == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Profile -->
                    @if($student['profile'] ?? null)
                        <div class="p-4">
                            <h3 class="text-sm font-bold text-slate-800 mb-3">
                                Additional Information
                            </h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">

                                @if($student['profile']['father_name'] ?? null)
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-slate-500 text-xs">Father's Name</p>
                                        <p class="font-semibold text-slate-800">
                                            {{ $student['profile']['father_name'] }}
                                        </p>
                                    </div>
                                @endif

                                @if($student['profile']['mother_name'] ?? null)
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-slate-500 text-xs">Mother's Name</p>
                                        <p class="font-semibold text-slate-800">
                                            {{ $student['profile']['mother_name'] }}
                                        </p>
                                    </div>
                                @endif

                                @if($student['profile']['religion'] ?? null)
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-slate-500 text-xs">Religion</p>
                                        <p class="font-semibold text-slate-800">
                                            {{ $student['profile']['religion'] }}
                                        </p>
                                    </div>
                                @endif

                                @if($student['profile']['full_name_bn'] ?? null)
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-slate-500 text-xs">Bangla Name</p>
                                        <p class="font-semibold text-slate-800">
                                            {{ $student['profile']['full_name_bn'] }}
                                        </p>
                                    </div>
                                @endif

                            </div>
                        </div>
                    @endif
                </div>

                <!-- Enrollment -->
                @if($enrollment)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-bold text-slate-800 mb-3">
                            Enrollment Details
                        </h3>

                        <div class="space-y-2 text-sm">

                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">Year</span>
                                <span class="font-semibold">{{ $enrollment['academic_year'] }}</span>
                            </div>

                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">Class</span>
                                <span class="font-semibold">{{ $enrollment['class']['name'] ?? 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">Section</span>
                                <span class="font-semibold">{{ $enrollment['section']['name'] ?? 'N/A' }}</span>
                            </div>

                            <div class="flex justify-between border-b pb-2">
                                <span class="text-slate-500">Roll</span>
                                <span class="font-semibold">{{ $enrollment['roll_no'] }}</span>
                            </div>

                            @if($enrollment['group'] ?? null)
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-slate-500">Group</span>
                                    <span class="font-semibold">{{ $enrollment['group']['name'] ?? $enrollment['group'] }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between pt-1">
                                <span class="text-slate-500">Status</span>
                                <span class="font-semibold {{ $enrollment['status'] == 1 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $enrollment['status'] == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                        </div>
                    </div>
                @endif

                <!-- School -->
                @if($school)
                    <div class="xl:col-span-3 bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                        <h3 class="text-sm font-bold text-slate-800 mb-3">
                            School Information
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">

                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-slate-500 text-xs">School Name</p>
                                <p class="font-semibold">{{ $school['name'] }}</p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-slate-500 text-xs">Domain</p>
                                <p class="font-semibold">{{ $school['domain_name'] }}</p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-slate-500 text-xs">Short Code</p>
                                <p class="font-semibold">{{ $school['short_code'] ?? 'N/A' }}</p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-3">
                                <p class="text-slate-500 text-xs">School ID</p>
                                <p class="font-semibold">{{ $school['id'] }}</p>
                            </div>

                        </div>
                    </div>
                @endif

            </div>

        @else

            <!-- Empty -->
            <div class="mt-5 bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center print:hidden">
                <div class="w-14 h-14 mx-auto rounded-xl bg-slate-100 flex items-center justify-center mb-4">
                    <i class="fas fa-magnifying-glass text-slate-400"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-800">
                    Search for Student Information
                </h2>
                <p class="text-sm text-slate-500 mt-2">
                    Use filters above to find a student quickly.
                </p>
            </div>

        @endif

    </div>
</section>
@endsection