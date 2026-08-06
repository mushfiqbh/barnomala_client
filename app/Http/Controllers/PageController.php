<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Speech;
use App\Models\Gallery;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\Committee;
use App\Models\Option;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    use BuildsPublicPageData;

    private const APPLICANT_API_BASE_URL = 'https://cloud.barnomala.com/api/v1';

    public function about(): View
    {
        return view('pages.about', $this->getPublicPageData());
    }

    public function speeches(Request $request): View
    {
        $query = Speech::where('is_active', true);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        $speeches = $query->orderBy('row_index', 'asc')
            ->orderBy('column_index', 'asc')
            ->get();

        return view('pages.speeches', array_merge($this->getPublicPageData(), compact('speeches')));
    }

    public function history(): View
    {
        return view('pages.history', $this->getPublicPageData());
    }

    public function gallery(Request $request): View
    {
        $query = Gallery::query();

        if ($request->has('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('date', 'desc')->paginate(12);

        $categories = Gallery::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('pages.gallery', array_merge($this->getPublicPageData(), compact('items', 'categories')));
    }

    public function galleryDetail(Gallery $gallery): View
    {
        return view('pages.gallery-detail', array_merge($this->getPublicPageData(), ['item' => $gallery]));
    }

    public function achievements(): View
    {
        return view('pages.achievements', $this->getPublicPageData());
    }

    public function contact(): View
    {
        return view('pages.contact', $this->getPublicPageData());
    }

    public function academic(): View
    {
        return view('pages.academic', $this->getPublicPageData());
    }

    public function academicCalendar(): View
    {
        return view('academic.academic-calendar', $this->getPublicPageData());
    }

    public function academicRules(): View
    {
        return view('academic.academic-rules', $this->getPublicPageData());
    }

    public function classSchedule(): View
    {
        return view('academic.class-schedule', $this->getPublicPageData());
    }

    public function examSchedule(): View
    {
        return view('academic.exam-schedule', $this->getPublicPageData());
    }

    public function tuitionFees(): View
    {
        return view('student.tuition-fees', $this->getPublicPageData());
    }

    public function students(): View
    {
        return view('student.students', $this->getPublicPageData());
    }

    public function uniform(): View
    {
        return view('student.uniform', $this->getPublicPageData());
    }

    public function activities(): View
    {
        return view('student.activities', $this->getPublicPageData());
    }

    public function mobileBanking(): View
    {
        return view('student.mobile-banking', $this->getPublicPageData());
    }

    public function results(): View
    {
        return view('student.result', $this->getPublicPageData());
    }

    public function teachers(): View
    {
        $teachers = Teacher::where('status', true)
            ->whereRaw("LOWER(COALESCE(designation, '')) NOT LIKE ?", ['%lecturer%'])
            ->orderBy('priority_index', 'asc')
            ->get();
        return view('academic.teachers', array_merge($this->getPublicPageData(), compact('teachers')));
    }

    public function lecturers(): View
    {
        $teachers = Teacher::where('status', true)
            ->whereRaw('LOWER(designation) LIKE ?', ['%lecturer%'])
            ->orderBy('priority_index', 'asc')
            ->get();

        return view('academic.lecturers', array_merge($this->getPublicPageData(), compact('teachers')));
    }

    public function formerTeachers(): View
    {
        $teachers = Teacher::where('status', false)
            ->orderBy('priority_index', 'asc')
            ->get();
        return view('academic.former-teachers', array_merge($this->getPublicPageData(), compact('teachers')));
    }

    public function teacherDetail(Teacher $teacher): View
    {
        $teacher->load(['qualifications', 'trainings']);
        return view('academic.teacher-detail', array_merge($this->getPublicPageData(), compact('teacher')));
    }

    public function staff(): View
    {
        $staffMembers = Staff::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();
        return view('academic.staff', array_merge($this->getPublicPageData(), ['staffMembers' => $staffMembers]));
    }

    public function formerStaff(): View
    {
        $staffMembers = Staff::whereIn('status', ['inactive', 'resigned'])
            ->orderBy('name', 'asc')
            ->get();
        return view('academic.former-staff', array_merge($this->getPublicPageData(), ['staffMembers' => $staffMembers]));
    }

    public function staffDetail(Staff $staff): View
    {
        return view('academic.staff-detail', array_merge($this->getPublicPageData(), ['staff' => $staff]));
    }

    public function committees(): View
    {
        $committees = Committee::where('status', 'active')
            ->orderBy('order_index')
            ->get();
        return view('pages.committees', array_merge($this->getPublicPageData(), compact('committees')));
    }

    public function committeeDetail(Committee $committee): View
    {
        $committee->load(['members' => function ($query) {
            $query->where('is_active', true);
        }]);
        return view('pages.committee-detail', array_merge($this->getPublicPageData(), compact('committee')));
    }

    public function apply(Request $request): View
    {
        $schoolContext = $this->getSchoolContext();
        $formData = $this->loadApplicantFormData($schoolContext);

        $lookupResult = null;
        $lookupError = null;
        $prefill = [];
        $selectedApplicantId = null;

        $selectedApplicationNo = (string) $request->input('application_no', '');

        // Seed selectedApplicantId from the query string so the hidden form
        // field is emitted on the initial render (before/independent of lookup).
        $rawApplicantId = $request->input('applicant_id');
        if (is_string($rawApplicantId) && trim($rawApplicantId) !== '' && ctype_digit(trim($rawApplicantId))) {
            $selectedApplicantId = (int) trim($rawApplicantId);
        }

        if ($request->filled(['phone', 'dob'])) {
            $lookupResult = $this->lookupApplicants(
                $schoolContext,
                (string) $request->input('phone'),
                (string) $request->input('dob'),
                $lookupError
            );

            if ($selectedApplicationNo !== '' && is_array($lookupResult)) {
                $applicants = $lookupResult['applicants'] ?? [];
                $match = collect($applicants)->first(function ($item) use ($selectedApplicationNo) {
                    return isset($item['application_no'])
                        && (string) $item['application_no'] === $selectedApplicationNo;
                });

                if ($match) {
                    $prefill = $this->buildApplicantPrefill($match, $formData);
                    // Prefer the authoritative id from the lookup response,
                    // but fall back to the query string value if missing.
                    $lookupId = isset($match['id']) ? (int) $match['id'] : null;
                    $selectedApplicantId = $lookupId ?? $selectedApplicantId;
                }
            }
        }

        return view('pages.apply', array_merge($this->getPublicPageData(), [
            'schoolContext' => $schoolContext,
            'formData' => $formData,
            'lookupResult' => $lookupResult,
            'lookupError' => $lookupError,
            'prefill' => $prefill,
            'selectedApplicationNo' => $selectedApplicationNo,
            'selectedApplicantId' => $selectedApplicantId,
            'lookupFilters' => [
                'phone' => $request->input('phone'),
                'dob' => $request->input('dob'),
            ],
            'admissionYears' => range(now()->year, now()->year + 3),
        ]));
    }

    private function buildApplicantPrefill(array $applicant, array $formData): array
    {
        $classOptions = collect($formData['classOptions'] ?? []);
        $groupOptions = collect($formData['groupOptions'] ?? []);

        $applicantClass = $applicant['admission_class_id'] ?? ($applicant['class_id'] ?? null);
        $applicantGroup = $applicant['applying_group_id'] ?? ($applicant['group_id'] ?? null);
        $applicantReligion = $applicant['religion_id'] ?? null;

        $classValue = $applicantClass;
        $resolvedClass = $applicantClass !== null
            ? $classOptions->firstWhere('value', (string) $applicantClass)
            : null;

        if (!$resolvedClass && $applicantClass !== null) {
            $resolvedClass = $classOptions->first(function ($option) use ($applicantClass) {
                return isset($option['label'])
                    && strcasecmp((string) $option['label'], (string) $applicantClass) === 0;
            });
        }

        if ($resolvedClass) {
            $classValue = $resolvedClass['value'];
            $classGroups = $resolvedClass['groups'] ?? [];
            if (!empty($classGroups) && $applicantGroup !== null) {
                $matchGroup = collect($classGroups)->firstWhere('value', (string) $applicantGroup);
                if ($matchGroup) {
                    $applicantGroup = $matchGroup['value'];
                }
            }
        } else {
            $applicantGroup = null;
        }

        if ($applicantGroup !== null && $groupOptions->isNotEmpty()) {
            $hasInGroups = $groupOptions->contains(function ($option) use ($applicantGroup) {
                return (string) ($option['value'] ?? '') === (string) $applicantGroup;
            });
            if (!$hasInGroups) {
                $applicantGroup = null;
            }
        }

        $checkboxKeys = [
            'is_father_late',
            'is_mother_late',
            'is_intellectual_disability',
        ];

        $prefill = [
            'application_no' => $applicant['application_no'] ?? null,
            'admission_class_id' => $classValue,
            'applying_group_id' => $applicantGroup,
            'religion_id' => $applicantReligion,
            'admission_year' => $applicant['admission_year'] ?? null,
            'full_name' => $applicant['full_name'] ?? null,
            'full_name_bn' => $applicant['full_name_bn'] ?? null,
            'phone' => $applicant['phone'] ?? null,
            'dob' => $applicant['dob'] ?? null,
            'gender' => $applicant['gender'] ?? null,
            'blood_group' => $applicant['blood_group'] ?? null,
            'nationality' => $applicant['nationality'] ?? null,
            'birth_reg_no' => $applicant['birth_reg_no'] ?? null,
            'email' => $applicant['email'] ?? null,
            'present_address' => $applicant['present_address'] ?? null,
            'permanent_address' => $applicant['permanent_address'] ?? null,
            'father_name' => $applicant['father_name'] ?? null,
            'mother_name' => $applicant['mother_name'] ?? null,
            'father_profession' => $applicant['father_profession'] ?? null,
            'mother_profession' => $applicant['mother_profession'] ?? null,
            'parent_annual_income' => $applicant['parent_annual_income'] ?? null,
            'father_nid' => $applicant['father_nid'] ?? null,
            'mother_nid' => $applicant['mother_nid'] ?? null,
            'guardian_name' => $applicant['guardian_name'] ?? null,
            'guardian_phone' => $applicant['guardian_phone'] ?? null,
            'guardian_nid' => $applicant['guardian_nid'] ?? null,
            'emergency_phone' => $applicant['emergency_phone'] ?? null,
            'admission_date' => $applicant['admission_date'] ?? null,
            'shift' => $applicant['shift'] ?? null,
            'tc_number' => $applicant['tc_number'] ?? null,
            'previous_school' => $applicant['previous_school'] ?? null,
            'facilities_availed' => $applicant['facilities_availed'] ?? null,
            'ssc_roll' => $applicant['ssc_roll'] ?? null,
            'ssc_reg_no' => $applicant['ssc_reg_no'] ?? null,
            'previous_gpa' => $applicant['previous_gpa'] ?? null,
        ];

        foreach ($checkboxKeys as $key) {
            $prefill[$key] = !empty($applicant[$key]);
        }

        return $prefill;
    }

    public function applySubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer', 'required_without:domain'],
            'domain' => ['nullable', 'string', 'max:255', 'required_without:school_id'],
            'applying_group_id' => ['nullable', 'integer'],
            'admission_class_id' => ['required', 'integer'],
            'admission_year' => ['required', 'integer', 'digits:4'],
            'phone' => ['required', 'string', 'max:20'],
            'dob' => ['required', 'date'],

            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:male,female,other'],
            'image' => ['nullable', 'image', 'max:2048'],

            'religion_id' => ['nullable', 'integer'],
            'full_name_bn' => ['nullable', 'string', 'max:200'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'birth_reg_no' => ['nullable', 'string', 'max:50'],
            'is_intellectual_disability' => ['nullable', 'boolean'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'present_address' => ['nullable', 'string'],
            'permanent_address' => ['nullable', 'string'],

            'father_name' => ['required', 'string', 'max:150'],
            'is_father_late' => ['nullable', 'boolean'],
            'father_profession' => ['nullable', 'string', 'max:100'],
            'father_nid' => ['nullable', 'string', 'max:25'],
            'mother_name' => ['required', 'string', 'max:150'],
            'is_mother_late' => ['nullable', 'boolean'],
            'mother_profession' => ['nullable', 'string', 'max:100'],
            'mother_nid' => ['nullable', 'string', 'max:25'],
            'parent_annual_income' => ['nullable', 'numeric'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_nid' => ['nullable', 'string', 'max:25'],

            'admission_date' => ['nullable', 'date'],
            'shift' => ['nullable', 'string', 'max:20'],
            'tc_number' => ['nullable', 'string', 'max:50'],
            'previous_school' => ['nullable', 'string'],
            'facilities_availed' => ['nullable', 'string'],
            'ssc_roll' => ['nullable', 'string', 'max:20'],
            'ssc_reg_no' => ['nullable', 'string', 'max:20'],
            'previous_gpa' => ['nullable', 'numeric'],
        ]);

        $schoolContext = $this->getSchoolContext();
        $sanitized = $this->sanitizeApplicantPayload($validated);
        $applicationNo = $request->input('application_no');
        $applicantId = $request->input('applicant_id');
        $isUpdate = is_string($applicantId) && trim($applicantId) !== '' && ctype_digit(trim($applicantId));

       if ($isUpdate) {
            $sanitized['applicant_id'] = (int) trim($applicantId);
            // Add method spoofing for Laravel BE to recognize it as a PATCH request
            $payload['_method'] = 'PATCH'; 
        }

        if (is_string($applicationNo) && trim($applicationNo) !== '') {
            $sanitized['application_no'] = $applicationNo;
        }

        $payload = array_merge($schoolContext['payload'], $sanitized);

        try {
            $requestBuilder = Http::timeout(30)->acceptJson();

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $requestBuilder = $requestBuilder->attach(
                    'image',
                    file_get_contents($image->getRealPath()),
                    $image->getClientOriginalName()
                );
            }

            if ($isUpdate) {
                $endpoint = self::APPLICANT_API_BASE_URL . '/applicants/' . (int) trim($applicantId);
                
                // CHANGED: Use post() instead of patch(), keeping asMultipart()
                $response = $requestBuilder->asMultipart()->post($endpoint, $payload); 
            } else {
                $response = $requestBuilder->post(self::APPLICANT_API_BASE_URL . '/applicants', $payload);
            }

            if ($response->successful()) {
                if ($isUpdate) {
                    $message = (string) ($response->json('message') ?? 'Application updated successfully.');
                } else {
                    $message = (string) ($response->json('message') ?? 'Application submitted successfully.');
                }

                $resolvedApplicationNo = $response->json('application_no') ?? ((is_string($applicationNo) && trim($applicationNo) !== '') ? $applicationNo : null);

                if ($resolvedApplicationNo) {
                    $message .= ' Application No: ' . $resolvedApplicationNo . '.';
                }

                return redirect()
                    ->route('apply.index')
                    ->with('success', $message);
            }

            return back()
                ->withInput($request->except(['image']))
                ->withErrors($this->extractApiValidationErrors($response))
                ->with('error', $this->extractApiMessage($response));
        } catch (\Throwable $exception) {
            Log::error('Applicant submission failed: ' . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            return back()
                ->withInput($request->except(['image']))
                ->with('error', 'Unable to submit the application right now. Please try again later.');
        }
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $defaultFromAddress = (string) config('mail.from.address', 'teambornomala@gmail.com');
            $defaultFromName = (string) config('mail.from.name', config('app.name', 'Barnomala School System'));

            $instituteEmail = trim((string) Option::get('institute.contact.email', $defaultFromAddress));
            $recipient = $instituteEmail !== '' ? $instituteEmail : $defaultFromAddress;

            Mail::to($recipient)->send(
                (new ContactMail($validated))
                    ->from($defaultFromAddress, $defaultFromName) // ✅ correct sender
                    ->replyTo($validated['email'], $validated['name']) // ✅ user reply
            );

            return back()->with('success', 'আপনার বার্তা সফলভাবে পাঠানো হয়েছে। দ্রুতই আমরা যোগাযোগ করব।');
        } catch (\Throwable $exception) {
            Log::error('Contact form submission failed: ' . $exception->getMessage(), [
                'exception' => $exception,
                'input' => $validated,
            ]);
            return back()
                ->withInput()
                ->with('error', 'দুঃখিত! বার্তা পাঠাতে সমস্যা হয়েছে। অনুগ্রহ করে কিছু সময় পর আবার চেষ্টা করুন।');
        }
    }

    private function getSchoolContext(): array
    {
        $schoolIdentifier = Option::where('option_key', 'institute.tenant.id')->value('option_value');
        $schoolIdentifier = (int) $schoolIdentifier;

        if ($schoolIdentifier !== null && $schoolIdentifier !== '') {
            if (is_numeric($schoolIdentifier)) {
                return [
                    'payload' => ['school_id' => (int) $schoolIdentifier],
                    'display' => (string) $schoolIdentifier,
                ];
            }

            return [
                'payload' => ['domain' => (string) $schoolIdentifier],
                'display' => (string) $schoolIdentifier,
            ];
        }

        $host = trim((string) request()->getHost());

        if ($host !== '') {
            return [
                'payload' => ['domain' => $host],
                'display' => $host,
            ];
        }

        return [
            'payload' => ['domain' => 'demo'],
            'display' => 'demo',
        ];
    }

    private function loadApplicantFormData(array $schoolContext): array
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get(self::APPLICANT_API_BASE_URL . '/applicants/form', $schoolContext['payload']);

            if ($response->successful()) {
                $data = $response->json('data', []);

                return [
                    'school' => $this->normalizeSchool($data['school'] ?? null),
                    'classOptions' => $this->normalizeClassOptions($data['classes'] ?? []),
                    'groupOptions' => $this->normalizeApplicationOptions($data['groups'] ?? [], ['groups', 'group_options'], ['group_id', 'id', 'value'], ['group_name', 'name', 'title', 'label', 'group']),
                    'religionOptions' => $this->normalizeApplicationOptions($data['religions'] ?? [], ['religions'], ['religion_id', 'id', 'value'], ['religion_name', 'name', 'title', 'label']),
                ];
            }
        } catch (\Throwable $exception) {
            Log::warning('Applicant form data load failed: ' . $exception->getMessage());
        }

        return [
            'school' => null,
            'groupOptions' => [],
            'classOptions' => [],
            'religionOptions' => [],
        ];
    }

    private function lookupApplicants(array $schoolContext, string $phone, string $dob, ?string &$error = null): ?array
    {
        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get(self::APPLICANT_API_BASE_URL . '/applicants/lookup', array_merge($schoolContext['payload'], [
                    'phone' => $phone,
                    'dob' => $dob,
                ]));

            if ($response->successful()) {
                return $response->json('data', []);
            }

            $error = $this->extractApiMessage($response) ?: 'No application records found for the provided phone number and date of birth.';
        } catch (\Throwable $exception) {
            Log::warning('Applicant lookup failed: ' . $exception->getMessage());
            $error = 'Unable to look up existing applications right now.';
        }

        return null;
    }

    private function sanitizeApplicantPayload(array $validated): array
    {
        $payload = $validated;
        unset($payload['image']);

        if (array_key_exists('is_intellectual_disability', $payload)) {
            $payload['is_intellectual_disability'] = (bool) $payload['is_intellectual_disability'];
        }

        if (array_key_exists('is_father_late', $payload)) {
            $payload['is_father_late'] = (bool) $payload['is_father_late'];
        }

        if (array_key_exists('is_mother_late', $payload)) {
            $payload['is_mother_late'] = (bool) $payload['is_mother_late'];
        }

        return array_filter($payload, static fn ($value) => $value !== null && $value !== '');
    }

    private function extractApiMessage($response): string
    {
        $message = $response->json('message');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        $errors = $response->json('errors');

        if (is_array($errors) && !empty($errors)) {
            $first = reset($errors);

            if (is_array($first)) {
                $first = reset($first);
            }

            if (is_string($first) && $first !== '') {
                return $first;
            }
        }

        return 'Something went wrong while processing the application.';
    }

    private function extractApiValidationErrors($response): array
    {
        $errors = $response->json('errors');

        if (is_array($errors) && !empty($errors)) {
            return $errors;
        }

        return [
            'application' => [$this->extractApiMessage($response)],
        ];
    }

    private function normalizeSchool($school): ?array
    {
        if (!is_array($school)) {
            return null;
        }

        return [
            'id' => $school['id'] ?? null,
            'name' => $school['name'] ?? $school['name_en'] ?? 'Selected School',
            'name_bn' => $school['name_bn'] ?? null,
            'domain_name' => $school['domain_name'] ?? null,
            'short_code' => $school['short_code'] ?? null,
        ];
    }

    private function normalizeApplicationOptions(array $items, array $nestedKeys, array $valueKeys, array $labelKeys): array
    {
        $options = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $nestedItems = null;

            foreach ($nestedKeys as $nestedKey) {
                if (!empty($item[$nestedKey]) && is_array($item[$nestedKey])) {
                    $nestedItems = $item[$nestedKey];
                    break;
                }
            }

            if (is_array($nestedItems)) {
                foreach ($nestedItems as $nestedItem) {
                    if (is_array($nestedItem) && ($normalized = $this->normalizeSelectableOption($nestedItem, $valueKeys, $labelKeys))) {
                        $options[] = $normalized;
                    }
                }

                continue;
            }

            if ($normalized = $this->normalizeSelectableOption($item, $valueKeys, $labelKeys)) {
                $options[] = $normalized;
            }
        }

        return array_values($options);
    }

    private function normalizeClassOptions(array $items): array
    {
        $options = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = $this->normalizeSelectableOption(
                $item,
                ['admission_class_id', 'id', 'value'],
                ['admission_class_name', 'class_name', 'name', 'title', 'label']
            );

            if (!$normalized) {
                continue;
            }

            $normalized['has_groups'] = (bool) ($item['has_groups'] ?? $item['hasGroup'] ?? false);
            $normalized['groups'] = $this->normalizeApplicationOptions(
                $item['groups'] ?? [],
                ['groups', 'group_options'],
                ['group_id', 'id', 'value'],
                ['group_name', 'name', 'title', 'label', 'group']
            );

            $options[] = $normalized;
        }

        return array_values($options);
    }

    private function normalizeSelectableOption(array $item, array $valueKeys, array $labelKeys): ?array
    {
        $label = null;

        foreach ($labelKeys as $key) {
            if (isset($item[$key]) && trim((string) $item[$key]) !== '') {
                $label = trim((string) $item[$key]);
                break;
            }
        }

        if ($label === null || $label === '') {
            return null;
        }

        $value = null;

        foreach ($valueKeys as $key) {
            if (isset($item[$key]) && $item[$key] !== '') {
                $value = $item[$key];
                break;
            }
        }

        if ($value === null || $value === '') {
            $value = $label;
        }

        return [
            'value' => $value,
            'label' => $label,
        ];
    }
}
