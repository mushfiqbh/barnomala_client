@extends('layouts.app')

@section('title', 'Apply New')

@section('content')
@php
    $school = $formData['school'] ?? null;
    $schoolName = $school['name'] ?? $options['institute.branding.name'] ?? config('app.name', 'Barnomala');
    $schoolDomain = $school['domain_name'] ?? ($schoolContext['display'] ?? request()->getHost());
    $schoolShortCode = $school['short_code'] ?? null;
    $classOptions = collect($formData['classOptions'] ?? []);
    $selectedClassId = old('admission_class_id', $prefill['admission_class_id'] ?? null);
    $selectedClass = $classOptions->firstWhere('value', $selectedClassId);
    $showGroupField = (bool) ($selectedClass['has_groups'] ?? false);
    $selectedGroupOptions = collect($selectedClass['groups'] ?? []);
    if ($selectedGroupOptions->isEmpty()) {
        $selectedGroupOptions = collect($formData['groupOptions'] ?? []);
    }
    $currentAcademicYear = $admissionYears[0] ?? now()->year;
    $prefill = $prefill ?? [];
    $prefillValue = static fn (string $key) => old($key, $prefill[$key] ?? null);
    $hasPrefill = !empty($prefill);
    $selectedFourthSubjectIds = array_map('intval', old('fourth_subject_ids', $prefill['fourth_subject_ids'] ?? []));
@endphp

<section class="py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="mt-3 text-3xl font-black text-slate-950 sm:text-4xl">
                    {{ $hasPrefill ? 'Update Application' : 'New Application' }}
                </h1>
            </div>
            <a href="{{ route('apply.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                <i class="fas fa-arrow-left"></i> Back to Apply
            </a>
        </div>

        {{-- Prefill notice --}}
        @if($hasPrefill)
            <div class="mt-6 flex items-center gap-3 rounded-3xl border border-indigo-200 bg-indigo-50 px-5 py-4 text-sm font-medium text-indigo-900">
                <i class="fas fa-circle-info text-lg"></i>
                <span>
                    <strong>Editing application:</strong> {{ $prefill['application_no'] ?? '' }} — submitting will update the record.
                </span>
            </div>
        @endif

        {{-- Form card --}}
        <div class="mt-8 rounded-4xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
            <form action="{{ route('apply.submit') }}" method="POST" enctype="multipart/form-data" id="applicationForm">
                @csrf

                @if($hasPrefill && !empty($prefill['application_no']))
                    <input type="hidden" name="application_no" value="{{ $prefill['application_no'] }}">
                @endif

                @if(!empty($selectedApplicantId))
                    <input type="hidden" name="applicant_id" value="{{ $selectedApplicantId }}">
                @endif

                @foreach($schoolContext['payload'] as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                {{-- Tabs Navigation --}}
                <div class="apply-tabs flex flex-wrap gap-2" role="tablist" aria-label="Application steps">
                    <button type="button" class="apply-tab active" data-tab-target="tab-basic" role="tab" aria-selected="true">
                        <span class="apply-tab-num">1</span>
                        <span class="apply-tab-label">Student &amp; Admission</span>
                        <i class="apply-tab-status fas"></i>
                    </button>
                    <button type="button" class="apply-tab" data-tab-target="tab-guardian" role="tab" aria-selected="false">
                        <span class="apply-tab-num">2</span>
                        <span class="apply-tab-label">Guardian Details</span>
                        <i class="apply-tab-status fas"></i>
                    </button>
                    <button type="button" class="apply-tab" data-tab-target="tab-additional" role="tab" aria-selected="false">
                        <span class="apply-tab-num">3</span>
                        <span class="apply-tab-label">Additional Info</span>
                        <i class="apply-tab-status fas"></i>
                    </button>
                </div>

                {{-- Tab 1: Required & Important Fields --}}
                <div class="apply-tab-panel active" id="tab-basic" data-tab-panel>

                    {{-- Academic Information --}}
                    <h3 class="section-subtitle">Academic Information</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="admission_year">Admission Year *</label>
                            <select id="admission_year" name="admission_year" required class="form-control">
                                <option value="">Select year</option>
                                @foreach($admissionYears as $year)
                                    <option value="{{ $year }}" @selected((string) $prefillValue('admission_year') === (string) $year || (old('admission_year') === null && ! $hasPrefill && $currentAcademicYear == $year))>{{ $year }}</option>
                                @endforeach
                            </select>
                            @error('admission_year')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="admission_class_id">Admission Class *</label>
                            <select id="admission_class_id" name="admission_class_id" required class="form-control">
                                <option value="">Select class</option>
                                @forelse($classOptions as $option)
                                    <option
                                        value="{{ $option['value'] }}"
                                        data-has-groups="{{ !empty($option['has_groups']) ? '1' : '0' }}"
                                        data-groups='@json($option['groups'] ?? [])'
                                        data-has-fourth-subject="{{ !empty($option['has_fourth_subject']) ? '1' : '0' }}"
                                        data-fourth-subjects='@json($option['fourth_subjects'] ?? [])'
                                        @selected((string) $prefillValue('admission_class_id') === (string) $option['value'])
                                    >{{ $option['label'] }}</option>
                                @empty
                                    <option value="" disabled>No class data loaded</option>
                                @endforelse
                            </select>
                            @error('admission_class_id')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div id="group-field" class="{{ $showGroupField ? '' : 'apply-hidden' }}">
                            <label class="form-label" for="applying_group_id">Applying Group</label>
                            <select id="applying_group_id" name="applying_group_id" class="form-control highlight" {{ $showGroupField ? '' : 'disabled' }}>
                                <option value="">Select group</option>
                                @forelse($selectedGroupOptions as $option)
                                    <option value="{{ $option['value'] }}" @selected((string) $prefillValue('applying_group_id') === (string) $option['value'])>{{ $option['label'] }}</option>
                                @empty
                                    <option value="" disabled>No group data loaded</option>
                                @endforelse
                            </select>
                            @error('applying_group_id')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Fourth Subject --}}
                    @php
                        $selectedClassHasFourth = (bool) ($selectedClass['has_fourth_subject'] ?? false);
                        $initialFourthSubjects = $selectedClass['fourth_subjects'] ?? [];
                        $initialSelectedGroup = old('applying_group_id', $prefill['applying_group_id'] ?? null);
                    @endphp
                    <div id="fourth-subject-field" class="apply-hidden" data-has-fourth-subject="{{ $selectedClassHasFourth ? '1' : '0' }}" data-selected-group="{{ $initialSelectedGroup ?? '' }}">
                        <h3 class="section-subtitle">Fourth Subject</h3>
                        <div id="fourth-subject-list" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($initialFourthSubjects as $subject)
                                @php
                                    $subjectGroupIds = array_map('intval', $subject['groups'] ?? []);
                                    $matchesGroup = empty($subjectGroupIds)
                                        || ($initialSelectedGroup !== null && in_array((int) $initialSelectedGroup, $subjectGroupIds, true));
                                @endphp
                                <label
                                    class="fourth-subject-option checkbox-row {{ $matchesGroup ? '' : 'apply-hidden' }}"
                                    data-subject-id="{{ $subject['id'] }}"
                                    data-subject-groups='@json($subjectGroupIds)'
                                >
                                    <input
                                        type="checkbox"
                                        name="fourth_subject_ids[]"
                                        value="{{ $subject['id'] }}"
                                        @checked(in_array((int) $subject['id'], $selectedFourthSubjectIds, true))
                                    >
                                    <span>
                                        <span class="block font-semibold text-slate-800">{{ $subject['name'] }}</span>
                                        @if(!empty($subject['subject_code']))
                                            <span class="block text-xs uppercase tracking-wider text-slate-400">Code: {{ $subject['subject_code'] }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p id="fourth-subject-empty" class="apply-hidden mt-2 text-sm text-slate-500">No fourth subjects available for the current class/group.</p>
                        @error('fourth_subject_ids')<p class="error-text">{{ $message }}</p>@enderror
                    </div>

                    {{-- Student Information --}}
                    <h3 class="section-subtitle">Student Information</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="full_name">Full Name *</label>
                            <input id="full_name" name="full_name" type="text" value="{{ $prefillValue('full_name') }}" required class="form-control">
                            @error('full_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="full_name_bn">Full Name Bangla</label>
                            <input id="full_name_bn" name="full_name_bn" type="text" value="{{ $prefillValue('full_name_bn') }}" class="form-control">
                            @error('full_name_bn')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="phone">Phone *</label>
                            <input id="phone" name="phone" type="text" value="{{ $prefillValue('phone') }}" required class="form-control">
                            @error('phone')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="dob">Date of Birth *</label>
                            <input id="dob" name="dob" type="date" value="{{ $prefillValue('dob') }}" required class="form-control">
                            @error('dob')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <option value="">Select</option>
                                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $prefillValue('gender') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('gender')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="image">Applicant Photo</label>
                            <input id="image" name="image" type="file" accept="image/*" class="form-control">
                            @error('image')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="religion_id">Religion *</label>
                            <select id="religion_id" name="religion_id" required class="form-control">
                                <option value="">Select religion</option>
                                @forelse($formData['religionOptions'] ?? [] as $option)
                                    <option value="{{ $option['value'] }}" @selected((string) $prefillValue('religion_id') === (string) $option['value'])>{{ $option['label'] }}</option>
                                @empty
                                    <option value="" disabled>No religion data loaded</option>
                                @endforelse
                            </select>
                            @error('religion_id')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Address --}}
                    <h3 class="section-subtitle">Address</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="present_address">Present Address</label>
                            <textarea id="present_address" name="present_address" rows="3" required class="form-control">{{ $prefillValue('present_address') }}</textarea>
                            @error('present_address')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="permanent_address">Permanent Address</label>
                            <textarea id="permanent_address" name="permanent_address" rows="3" class="form-control">{{ $prefillValue('permanent_address') }}</textarea>
                            @error('permanent_address')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Parents (Required) --}}
                    <h3 class="section-subtitle">Parents (Required)</h3>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="form-label" for="father_name">Father's Name *</label>
                            <input id="father_name" name="father_name" type="text" value="{{ $prefillValue('father_name') }}" required class="form-control">
                            @error('father_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="mother_name">Mother's Name *</label>
                            <input id="mother_name" name="mother_name" type="text" value="{{ $prefillValue('mother_name') }}" required class="form-control">
                            @error('mother_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>{{-- /tab-basic --}}

                {{-- Tab 2: Guardian Details --}}
                <div class="apply-tab-panel" id="tab-guardian" data-tab-panel>

                    {{-- Parent / Guardian Details --}}
                    <h3 class="section-subtitle">Parent / Guardian Details</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="father_profession">Father Profession</label>
                            <input id="father_profession" name="father_profession" type="text" value="{{ $prefillValue('father_profession') }}" class="form-control">
                            @error('father_profession')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="mother_profession">Mother Profession</label>
                            <input id="mother_profession" name="mother_profession" type="text" value="{{ $prefillValue('mother_profession') }}" class="form-control">
                            @error('mother_profession')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="parent_annual_income">Parent Annual Income</label>
                            <input id="parent_annual_income" name="parent_annual_income" type="number" step="0.01" value="{{ $prefillValue('parent_annual_income') }}" class="form-control">
                            @error('parent_annual_income')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="father_nid">Father NID</label>
                            <input id="father_nid" name="father_nid" type="text" value="{{ $prefillValue('father_nid') }}" class="form-control">
                            @error('father_nid')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="mother_nid">Mother NID</label>
                            <input id="mother_nid" name="mother_nid" type="text" value="{{ $prefillValue('mother_nid') }}" class="form-control">
                            @error('mother_nid')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="guardian_name">Guardian Name</label>
                            <input id="guardian_name" name="guardian_name" type="text" value="{{ $prefillValue('guardian_name') }}" class="form-control">
                            @error('guardian_name')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="guardian_phone">Guardian Phone</label>
                            <input id="guardian_phone" name="guardian_phone" type="text" value="{{ $prefillValue('guardian_phone') }}" class="form-control">
                            @error('guardian_phone')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="guardian_nid">Guardian NID</label>
                            <input id="guardian_nid" name="guardian_nid" type="text" value="{{ $prefillValue('guardian_nid') }}" class="form-control">
                            @error('guardian_nid')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="emergency_phone">Emergency Phone</label>
                            <input id="emergency_phone" name="emergency_phone" type="text" value="{{ $prefillValue('emergency_phone') }}" class="form-control">
                            @error('emergency_phone')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Flags --}}
                    <h3 class="section-subtitle">Flags</h3>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_father_late" value="1" @checked($prefillValue('is_father_late'))>
                            <span>Father is late</span>
                        </label>
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_mother_late" value="1" @checked($prefillValue('is_mother_late'))>
                            <span>Mother is late</span>
                        </label>
                        <label class="checkbox-row">
                            <input type="checkbox" name="is_intellectual_disability" value="1" @checked($prefillValue('is_intellectual_disability'))>
                            <span>Intellectual disability</span>
                        </label>
                    </div>
                </div>{{-- /tab-guardian --}}

                {{-- Tab 3: Additional Info --}}
                <div class="apply-tab-panel" id="tab-additional" data-tab-panel>

                    {{-- Additional Info --}}
                    <h3 class="section-subtitle">Additional Info</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ $prefillValue('email') }}" class="form-control">
                            @error('email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="blood_group">Blood Group</label>
                            <input id="blood_group" name="blood_group" type="text" value="{{ $prefillValue('blood_group') }}" class="form-control">
                            @error('blood_group')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="nationality">Nationality</label>
                            <input id="nationality" name="nationality" type="text" value="{{ $prefillValue('nationality') ?? 'Bangladeshi' }}" class="form-control">
                            @error('nationality')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="birth_reg_no">Birth Reg. No.</label>
                            <input id="birth_reg_no" name="birth_reg_no" type="text" value="{{ $prefillValue('birth_reg_no') }}" class="form-control">
                            @error('birth_reg_no')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Admission Details --}}
                    <h3 class="section-subtitle">Admission Details</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="shift">Shift</label>
                            <input id="shift" name="shift" type="text" value="{{ $prefillValue('shift') }}" class="form-control">
                            @error('shift')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="tc_number">TC Number</label>
                            <input id="tc_number" name="tc_number" type="text" value="{{ $prefillValue('tc_number') }}" class="form-control">
                            @error('tc_number')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Previous Academic Info --}}
                    <h3 class="section-subtitle">Previous Academic Info</h3>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <label class="form-label" for="previous_school">Previous School</label>
                            <input id="previous_school" name="previous_school" type="text" value="{{ $prefillValue('previous_school') }}" class="form-control">
                            @error('previous_school')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="facilities_availed">Facilities Availed</label>
                            <input id="facilities_availed" name="facilities_availed" type="text" value="{{ $prefillValue('facilities_availed') }}" class="form-control">
                            @error('facilities_availed')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="ssc_roll">SSC Roll</label>
                            <input id="ssc_roll" name="ssc_roll" type="text" value="{{ $prefillValue('ssc_roll') }}" class="form-control">
                            @error('ssc_roll')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="ssc_reg_no">SSC Registration No.</label>
                            <input id="ssc_reg_no" name="ssc_reg_no" type="text" value="{{ $prefillValue('ssc_reg_no') }}" class="form-control">
                            @error('ssc_reg_no')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="ssc_gpa">SSC GPA</label>
                            <input id="ssc_gpa" name="ssc_gpa" type="number" step="0.01" value="{{ $prefillValue('ssc_gpa') }}" class="form-control">
                            @error('ssc_gpa')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>{{-- /tab-additional --}}

                {{-- Footer navigation --}}
                <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-8">
                    <button type="button" class="btn btn-outline" id="tab-prev" disabled>
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="flex flex-col items-center gap-1 text-center">
                        <span class="tab-progress" id="tab-progress">Step 1 of 3</span>
                        <span class="text-xs text-slate-400">Fields marked <span class="font-bold text-rose-600">*</span> are required</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" class="btn btn-primary" id="tab-next">Next <i class="fas fa-chevron-right"></i></button>
                        <button type="submit" class="btn {{ $hasPrefill ? 'btn-warning' : 'btn-success' }}" id="tab-submit">
                            <i class="fas {{ $hasPrefill ? 'fa-arrows-rotate' : 'fa-paper-plane' }}"></i>
                            {{ $hasPrefill ? 'Update Application' : 'Submit Application' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@push('styles')
    <style>
        .section-subtitle {
            margin-top: 28px;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #64748b;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #475569;
            margin-bottom: 8px;
        }

        .form-control {
            --field-accent: #94a3b8;
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--field-accent);
            background-color: #f8fafc;
            padding: 12px 16px;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }

        .form-control:required,
        .form-control.highlight {
            --field-accent: #4f46e5;
        }

        .form-control:focus {
            border-color: #0f172a;
            border-left-color: var(--field-accent);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
        }

        textarea.form-control {
            height: auto;
            resize: vertical;
        }

        .error-text {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #e11d48;
        }

        .checkbox-row {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
        }

        .checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #4f46e5;
        }

        /* ===== Apply Tabs ===== */
        .apply-tab {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9999px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            color: #64748b;
            transition: all 0.2s;
            font-family: inherit;
        }

        .apply-tab:hover {
            color: #4f46e5;
            border-color: #c7d2fe;
        }

        .apply-tab.active {
            color: #ffffff;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-color: transparent;
            box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.5);
        }

        .apply-tab-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .apply-tab.active .apply-tab-num {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        .apply-tab.is-complete .apply-tab-num {
            background: #10b981;
            color: #fff;
        }

        .apply-tab.has-error .apply-tab-num {
            background: #dc2626;
            color: #fff;
        }

        .apply-tab-label {
            line-height: 1.2;
        }

        .apply-tab-status {
            display: none;
            font-size: 13px;
            margin-left: 2px;
        }

        .apply-tab.is-complete .apply-tab-status,
        .apply-tab.has-error .apply-tab-status {
            display: inline-block;
        }

        .apply-tab.is-complete .apply-tab-status {
            color: #10b981;
        }

        .apply-tab.has-error .apply-tab-status {
            color: #c63636;
        }

        .apply-tab-badge {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 700;
        }

        .apply-tab.active .apply-tab-badge.required {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .apply-tab-badge.required {
            background: #fef3c7;
            color: #92400e;
        }

        .apply-tab-badge.optional {
            background: #e2e8f0;
            color: #475569;
        }

        @media screen and (max-width: 600px) {
            .apply-tab {
                padding: 8px 12px;
                font-size: 12px;
            }

            .apply-tab-badge {
                display: none;
            }
        }

        /* Panels */
        .apply-tab-panel {
            display: none;
        }

        .apply-tab-panel.active {
            display: block;
            animation: tabFadeIn 0.25s ease;
        }

        @keyframes tabFadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: none;
            }
        }

        /* Footer navigation */
        .tab-progress {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 1rem;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
            font-family: inherit;
        }

        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #0f172a;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #1e293b;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        .btn-outline:hover:not(:disabled) {
            background: #f1f5f9;
        }

        .apply-hidden {
            display: none;
        }

        .apply-field-invalid {
            border-color: #e11d48 !important;
            border-left-color: #e11d48 !important;
            background-color: #fff1f2 !important;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
        }

        .apply-field-invalid:focus {
            border-color: #e11d48 !important;
            border-left-color: #e11d48 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.25);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const classSelect = document.getElementById('admission_class_id');
        const groupField = document.getElementById('group-field');
        const groupSelect = document.getElementById('applying_group_id');
        const fourthSubjectField = document.getElementById('fourth-subject-field');
        const fourthSubjectList = document.getElementById('fourth-subject-list');
        const fourthSubjectEmpty = document.getElementById('fourth-subject-empty');
        const fourthSubjectHelp = document.getElementById('fourth-subject-group-note');

        if (!classSelect || !groupField || !groupSelect) {
            return;
        }

        const defaultGroupOptions = @json($formData['groupOptions'] ?? []);

        function renderOptions(options) {
            const fragment = document.createDocumentFragment();

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select group';
            fragment.appendChild(placeholder);

            if (!Array.isArray(options) || options.length === 0) {
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.disabled = true;
                emptyOption.textContent = 'No group data loaded';
                fragment.appendChild(emptyOption);
            } else {
                options.forEach((option) => {
                    if (!option || option.value === undefined || option.value === null || option.label === undefined) {
                        return;
                    }

                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.label;
                    if (String(option.value) === groupSelect.value) {
                        opt.selected = true;
                    }
                    fragment.appendChild(opt);
                });
            }

            groupSelect.innerHTML = '';
            groupSelect.appendChild(fragment);
        }

        function parseJSON(raw, fallback) {
            if (!raw) {
                return fallback;
            }
            try {
                const parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : fallback;
            } catch (error) {
                return fallback;
            }
        }

        function rebuildFourthSubjects(subjects) {
            if (!fourthSubjectList) {
                return;
            }

            const selectedIds = Array.from(fourthSubjectList.querySelectorAll('input[name="fourth_subject_ids[]"]:checked'))
                .map((input) => String(input.value));

            const fragment = document.createDocumentFragment();
            subjects.forEach((subject) => {
                if (!subject || subject.id === undefined || subject.id === null || !subject.name) {
                    return;
                }

                const wrapper = document.createElement('label');
                wrapper.className = 'fourth-subject-option checkbox-row';
                wrapper.dataset.subjectId = String(subject.id);
                wrapper.dataset.subjectGroups = JSON.stringify(Array.isArray(subject.groups) ? subject.groups : []);

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'fourth_subject_ids[]';
                input.value = String(subject.id);
                if (selectedIds.includes(String(subject.id))) {
                    input.checked = true;
                }

                const text = document.createElement('span');
                const nameLine = document.createElement('span');
                nameLine.className = 'block font-semibold text-slate-800';
                nameLine.textContent = subject.name;
                text.appendChild(nameLine);

                if (subject.subject_code) {
                    const codeLine = document.createElement('span');
                    codeLine.className = 'block text-xs uppercase tracking-wider text-slate-400';
                    codeLine.textContent = 'Code: ' + subject.subject_code;
                    text.appendChild(codeLine);
                }

                wrapper.appendChild(input);
                wrapper.appendChild(text);
                fragment.appendChild(wrapper);
            });

            fourthSubjectList.innerHTML = '';
            fourthSubjectList.appendChild(fragment);
        }

        function applyFourthSubjectVisibility() {
            if (!fourthSubjectField) {
                return;
            }

            const selectedOption = classSelect.options[classSelect.selectedIndex];
            const hasFourth = selectedOption?.dataset?.hasFourthSubject === '1';
            const subjects = parseJSON(selectedOption?.dataset?.fourthSubjects, []);
            const groupId = groupSelect.value !== '' ? Number(groupSelect.value) : null;

            if (!hasFourth || subjects.length === 0) {
                fourthSubjectField.classList.add('apply-hidden');
                fourthSubjectField.querySelectorAll('input[name="fourth_subject_ids[]"]').forEach((input) => {
                    input.disabled = true;
                });
                return;
            }

            rebuildFourthSubjects(subjects);

            let visibleCount = 0;
            fourthSubjectList.querySelectorAll('.fourth-subject-option').forEach((option) => {
                const rawGroups = option.dataset.subjectGroups || '[]';
                let groupIds = [];
                try {
                    const parsed = JSON.parse(rawGroups);
                    if (Array.isArray(parsed)) {
                        groupIds = parsed.map((id) => Number(id));
                    }
                } catch (error) {
                    groupIds = [];
                }

                const matches = groupIds.length === 0 || (groupId !== null && groupIds.includes(groupId));
                option.classList.toggle('apply-hidden', !matches);
                if (matches) {
                    visibleCount++;
                }
            });

            const groupConstrains = subjects.some((subject) => Array.isArray(subject.groups) && subject.groups.length > 0);
            if (fourthSubjectHelp) {
                fourthSubjectHelp.classList.toggle('apply-hidden', !groupConstrains);
            }
            if (fourthSubjectEmpty) {
                fourthSubjectEmpty.classList.toggle('apply-hidden', visibleCount !== 0);
            }

            fourthSubjectField.classList.remove('apply-hidden');
            fourthSubjectField.querySelectorAll('input[name="fourth_subject_ids[]"]').forEach((input) => {
                input.disabled = false;
            });
        }

        function toggleGroupField() {
            const selectedOption = classSelect.options[classSelect.selectedIndex];
            const hasGroups = selectedOption?.dataset?.hasGroups === '1';
            const rawGroups = selectedOption?.dataset?.groups || '[]';

            let classGroups = defaultGroupOptions;
            try {
                const parsedGroups = JSON.parse(rawGroups);
                if (Array.isArray(parsedGroups) && parsedGroups.length > 0) {
                    classGroups = parsedGroups;
                }
            } catch (error) {
                classGroups = defaultGroupOptions;
            }

            if (hasGroups) {
                groupField.classList.remove('apply-hidden');
                groupSelect.disabled = false;
                renderOptions(classGroups);
            } else {
                groupSelect.value = '';
                groupSelect.disabled = true;
                renderOptions([]);
                groupField.classList.add('apply-hidden');
            }

            applyFourthSubjectVisibility();
        }

        classSelect.addEventListener('change', toggleGroupField);
        if (groupSelect) {
            groupSelect.addEventListener('change', applyFourthSubjectVisibility);
        }
        toggleGroupField();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = Array.from(document.querySelectorAll('.apply-tab'));
        const panels = Array.from(document.querySelectorAll('[data-tab-panel]'));
        const form = document.getElementById('applicationForm');
        const prevBtn = document.getElementById('tab-prev');
        const nextBtn = document.getElementById('tab-next');
        const submitBtn = document.getElementById('tab-submit');
        const progress = document.getElementById('tab-progress');

        if (tabs.length === 0 || panels.length === 0) {
            return;
        }

        const total = panels.length;
        let touched = false;

        function setTabStatus(tab, status) {
            const icon = tab.querySelector('.apply-tab-status');
            tab.classList.remove('is-complete', 'has-error');
            icon.className = 'apply-tab-status fas';
            if (status === 'complete') {
                tab.classList.add('is-complete');
                icon.classList.add('fa-check-circle');
            } else if (status === 'error') {
                tab.classList.add('has-error');
                icon.classList.add('fa-exclamation-circle');
            }
        }

        function activateTab(targetId, { focusFirst = false } = {}) {
            const target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            tabs.forEach((tab) => {
                const isActive = tab.dataset.tabTarget === targetId;
                tab.classList.toggle('active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            panels.forEach((panel) => panel.classList.toggle('active', panel.id === targetId));

            const index = panels.findIndex((panel) => panel.id === targetId);
            if (progress) {
                progress.textContent = `Step ${index + 1} of ${total}`;
            }
            if (prevBtn) {
                prevBtn.disabled = index === 0;
            }
            if (nextBtn) {
                nextBtn.style.display = index === total - 1 ? 'none' : '';
            }
            if (submitBtn) {
                submitBtn.style.display = index === total - 1 ? '' : 'none';
            }

            if (focusFirst) {
                const firstInvalid = target.querySelector(':invalid');
                const firstControl = target.querySelector('input, select, textarea');
                setTimeout(() => {
                    (firstInvalid || firstControl || target).focus();
                }, 60);
            }
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateTab(tab.dataset.tabTarget));
        });

        function validatePanel(panel) {
            if (!panel) {
                return true;
            }

            const requiredFields = Array.from(panel.querySelectorAll('[required]'));
            let firstInvalid = null;

            requiredFields.forEach((field) => {
                if (field.disabled || field.type === 'hidden') {
                    return;
                }

                const isCheckbox = field.type === 'checkbox' || field.type === 'radio';
                const value = isCheckbox
                    ? panel.querySelector(`input[name="${field.name}"]:checked`)
                    : field.value;

                field.classList.remove('apply-field-invalid');

                if (!value || (typeof value === 'string' && value.trim() === '')) {
                    if (!firstInvalid) {
                        firstInvalid = field;
                    }
                    field.classList.add('apply-field-invalid');
                }
            });

            if (firstInvalid) {
                firstInvalid.reportValidity();
                if (typeof firstInvalid.scrollIntoView === 'function') {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                firstInvalid.focus({ preventScroll: true });
                touched = true;
                updateTabStates();
                return false;
            }

            return true;
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                const current = panels.find((panel) => panel.classList.contains('active')) || panels[0];
                const idx = panels.indexOf(current);
                if (idx < total - 1) {
                    if (!validatePanel(current)) {
                        e.preventDefault();
                        return;
                    }
                    activateTab(panels[idx + 1].id, { focusFirst: true });
                }
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                const current = panels.find((panel) => panel.classList.contains('active')) || panels[0];
                const idx = panels.indexOf(current);
                if (idx > 0) {
                    activateTab(panels[idx - 1].id);
                }
            });
        }

        function updateTabStates() {
            panels.forEach((panel) => {
                const tab = tabs.find((t) => t.dataset.tabTarget === panel.id);
                if (!tab) {
                    return;
                }

                const hasServerError = panel.querySelector('.error-text') !== null;
                const invalidCount = panel.querySelectorAll(':invalid').length;
                const requiredCount = panel.querySelectorAll('[required]').length;
                const filledRequired = panel.querySelectorAll('[required]:valid').length;

                if ((touched || hasServerError) && invalidCount > 0) {
                    setTabStatus(tab, 'error');
                } else if (requiredCount > 0 && filledRequired === requiredCount) {
                    setTabStatus(tab, 'complete');
                } else {
                    setTabStatus(tab, '');
                }
            });
        }

        if (form) {
            form.addEventListener('input', () => {
                touched = true;
                updateTabStates();
            });
            form.addEventListener('change', () => {
                touched = true;
                updateTabStates();
            });

            // If a hidden tab contains invalid required fields, bring the user there.
            form.addEventListener('submit', (e) => {
                const blockedPanel = panels.find(
                    (panel) => !panel.classList.contains('active') && panel.querySelector(':invalid')
                );
                if (blockedPanel) {
                    e.preventDefault();
                    activateTab(blockedPanel.id, { focusFirst: true });
                }
            });
        }

        // On load, jump to the tab containing server-side validation errors (if any).
        const errorPanel = panels.find((panel) => panel.querySelector('.error-text'));
        activateTab(errorPanel ? errorPanel.id : panels[0].id);
        updateTabStates();
    });
</script>
@endsection
