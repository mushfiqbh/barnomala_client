<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Option;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ApplyController extends Controller
{
    use BuildsPublicPageData;

    private const APPLICANT_API_BASE_URL = 'https://cloud.barnomala.com/api/v1';

    public function apply(): View
    {
        return view('apply.index', $this->getPublicPageData());
    }

    public function applyNew(Request $request): View
    {
        $schoolContext = $this->getSchoolContext();
        $formData = $this->loadApplicantFormData($schoolContext);

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
            $lookupError = null;
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

        return view('apply.new', array_merge($this->getPublicPageData(), [
            'schoolContext' => $schoolContext,
            'formData' => $formData,
            'prefill' => $prefill,
            'selectedApplicationNo' => $selectedApplicationNo,
            'selectedApplicantId' => $selectedApplicantId,
            'admissionYears' => range(now()->year, now()->year + 3),
        ]));
    }

    public function applyApplications(Request $request): View
    {
        $schoolContext = $this->getSchoolContext();

        $lookupResult = null;
        $lookupError = null;

        if ($request->filled(['phone', 'dob'])) {
            $lookupResult = $this->lookupApplicants(
                $schoolContext,
                (string) $request->input('phone'),
                (string) $request->input('dob'),
                $lookupError
            );
        }

        return view('apply.applications', array_merge($this->getPublicPageData(), [
            'schoolContext' => $schoolContext,
            'lookupResult' => $lookupResult,
            'lookupError' => $lookupError,
            'lookupFilters' => [
                'phone' => $request->input('phone'),
                'dob' => $request->input('dob'),
            ],
        ]));
    }

    public function applyPayment(): View
    {
        return view('apply.payment', $this->getPublicPageData());
    }

    public function applySupport(): View
    {
        return view('apply.support', $this->getPublicPageData());
    }

    public function printSlip(Request $request): Response
    {
        $schoolContext = $this->getSchoolContext();

        $application = null;

        if ($request->filled(['phone', 'dob', 'application_no'])) {
            $lookupError = null;
            $lookupResult = $this->lookupApplicants(
                $schoolContext,
                (string) $request->input('phone'),
                (string) $request->input('dob'),
                $lookupError
            );

            $application = collect($lookupResult['applicants'] ?? [])->first(function ($item) use ($request) {
                return isset($item['application_no'])
                    && (string) $item['application_no'] === (string) $request->input('application_no');
            });
        }

        $html = view('apply.print-slip', [
            'application' => $application,
            'schoolName' => Option::get('institute.branding.name', config('app.name', 'School Name')),
            'schoolAddress' => (string) Option::get('institute.contact.address', ''),
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'freeserif',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12,
        ]);

        $mpdf->WriteHTML($html);

        $applicationNo = $application['application_no'] ?? null;
        $safeNo = is_string($applicationNo) ? preg_replace('/[^A-Za-z0-9_-]/', '', $applicationNo) : '';
        $filename = 'admission-slip' . ($safeNo !== '' ? '-' . $safeNo : '') . '.pdf';

        $pdf = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

        $fourthSubjectIds = [];
        if (isset($applicant['fourth_subject_ids']) && is_array($applicant['fourth_subject_ids'])) {
            foreach ($applicant['fourth_subject_ids'] as $id) {
                if (is_numeric($id)) {
                    $fourthSubjectIds[] = (int) $id;
                }
            }
        }

        $prefill = [
            'application_no' => $applicant['application_no'] ?? null,
            'admission_class_id' => $classValue,
            'applying_group_id' => $applicantGroup,
            'religion_id' => $applicantReligion,
            'admission_year' => $applicant['admission_year'] ?? null,
            'fourth_subject_ids' => $fourthSubjectIds,
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
            'fourth_subject_ids' => ['nullable', 'array'],
            'fourth_subject_ids.*' => ['integer'],

            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'in:male,female,other'],
            'image' => ['nullable', 'image', 'max:2048'],

            'religion_id' => ['required', 'integer'],
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

        // Auto-set the admission date to today for new applications.
        if (!$isUpdate) {
            $sanitized['admission_date'] = now()->toDateString();
        }

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
                    ->route('apply.applications', array_filter([
                        'phone' => $validated['phone'] ?? null,
                        'dob' => $validated['dob'] ?? null,
                    ]))
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

        if (array_key_exists('fourth_subject_ids', $payload)) {
            $raw = $payload['fourth_subject_ids'];
            $payload['fourth_subject_ids'] = is_array($raw)
                ? array_values(array_unique(array_filter(array_map(
                    static fn ($id) => is_numeric($id) ? (int) $id : null,
                    $raw
                ), static fn ($id) => $id !== null)))
                : [];
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
            $normalized['has_fourth_subject'] = (bool) ($item['has_fourth_subject'] ?? $item['hasFourthSubject'] ?? false);
            $normalized['fourth_subjects'] = $this->normalizeFourthSubjects(
                $item['fourth_subjects'] ?? []
            );

            $options[] = $normalized;
        }

        return array_values($options);
    }

    /**
     * Normalize the API's fourth-subject payload so the view can iterate
     * without re-mapping. Each entry becomes
     *   ['id' => int, 'name' => string, 'subject_code' => string|null, 'groups' => array<int>]
     * where `groups` is a list of allowed group ids for the subject (empty
     * when the subject is offered to every group).
     */
    private function normalizeFourthSubjects(array $items): array
    {
        $subjects = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = null;
            foreach (['name', 'subject_name', 'title', 'label'] as $key) {
                if (isset($item[$key]) && trim((string) $item[$key]) !== '') {
                    $name = trim((string) $item[$key]);
                    break;
                }
            }

            if ($name === null || $name === '') {
                continue;
            }

            $id = null;
            foreach (['id', 'subject_id', 'value'] as $key) {
                if (isset($item[$key]) && is_numeric($item[$key])) {
                    $id = (int) $item[$key];
                    break;
                }
            }

            if ($id === null) {
                continue;
            }

            $code = null;
            if (isset($item['subject_code']) && trim((string) $item['subject_code']) !== '') {
                $code = trim((string) $item['subject_code']);
            } elseif (isset($item['code']) && trim((string) $item['code']) !== '') {
                $code = trim((string) $item['code']);
            }

            $groupIds = [];
            $rawGroups = $item['groups'] ?? [];
            if (is_array($rawGroups)) {
                foreach ($rawGroups as $group) {
                    if (is_array($group)) {
                        $gid = $group['id'] ?? ($group['value'] ?? ($group['group_id'] ?? null));
                        if (is_numeric($gid)) {
                            $groupIds[] = (int) $gid;
                        }
                    } elseif (is_numeric($group)) {
                        $groupIds[] = (int) $group;
                    }
                }
            }

            $subjects[] = [
                'id' => $id,
                'name' => $name,
                'subject_code' => $code,
                'groups' => array_values(array_unique($groupIds)),
            ];
        }

        return $subjects;
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
