<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    use BuildsPublicPageData;

    private const API_BASE_URL = 'https://cloud.barnomala.com/api/v1';

    /**
     * Marksheet subject keys that are NOT mark-distribution heads
     * (e.g. cq, mcq, practical). Everything else is rendered as a
     * dynamic mark column in the marksheet table.
     */
    private const MARKSHEET_STATIC_KEYS = [
        'subject_name',
        'subject_id',
        'order_no',
        'is_fourth',
        'is_assessment',
        'is_combined',
        'parent_subject_id',
        'connection_type',
        'grade',
        'gpa',
        'is_absent',
        'has_incourse',
        'total_mark',
        'processed_total_mark',
        'incourse_total_mark',
        'final_total_mark',
        'full_mark',
        'assessment_total',
        'total',
    ];

    /**
     * Show the result lookup page.
     */
    public function index(Request $request)
    {
        $filterOptions = $this->fetchFilterOptions();

        $school = null;
        $student = null;
        $enrollment = null;
        $exams = [];
        $headKeys = [];
        $error = null;
        $data = null;

        $hasCriteria = $request->filled('class_id')
            || $request->filled('section_id')
            || $request->filled('year')
            || $request->filled('roll')
            || $request->filled('student_id');

        if ($hasCriteria) {
            // Light client-side validation with friendly messages
            if (! $request->filled('class_id')) {
                $error = 'Please select a class.';
            } elseif (! $request->filled('year')) {
                $error = 'Please select a year.';
            } elseif (! $request->filled('roll') && ! $request->filled('student_id')) {
                $error = 'Please provide a roll number or a student ID.';
            } else {
                $data = $this->lookupResults($request, $error);
            }

            if ($data) {
                $school = $data['school'] ?? null;
                $student = $data['student'] ?? null;
                $enrollment = $data['enrollment'] ?? null;

                // Unpublished results are not released: the API only exposes
                // exam metadata for them, so the view renders them as a
                // disabled summary button with no figures or marksheet.
                $exams = $data['exams'] ?? [];

                $headKeys = $this->collectMarksheetHeadKeys($exams);

                if ($student) {
                    $student['image_path'] = $this->normalizeImageUrl($student['image_path'] ?? null);
                }

                foreach ($exams as &$exam) {
                    if (isset($exam['marksheet']['image'])) {
                        $exam['marksheet']['image'] = $this->normalizeImageUrl($exam['marksheet']['image']);
                    }
                }
                unset($exam);
            }
        }

        return view('student.result', array_merge($this->getPublicPageData(), [
            'school' => $school,
            'student' => $student,
            'enrollment' => $enrollment,
            'exams' => $exams,
            'headKeys' => $headKeys,
            'error' => $error,
            'filterOptions' => $filterOptions,
            'filters' => [
                'class_id' => $request->input('class_id'),
                'section_id' => $request->input('section_id'),
                'year' => $request->input('year'),
                'roll' => $request->input('roll'),
                'student_id' => $request->input('student_id'),
            ],
        ]));
    }

    /**
     * Fetch class / section / year filter options from the API.
     */
    private function fetchFilterOptions(): array
    {
        try {
            $response = Http::timeout(10)
                ->get(self::API_BASE_URL . '/students/lookup', [
                    'school_id' => $this->getSchoolIdentifier(),
                ]);

            if ($response->successful()) {
                return $response->json('data.filterOptions', []);
            }
        } catch (\Exception $e) {
            Log::error('Result Filter Options Error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Lookup results from the API by school + class filters.
     */
    private function lookupResults(Request $request, &$error): ?array
    {
        try {
            $params = ['school_id' => $this->getSchoolIdentifier()];

            if ($request->filled('class_id')) {
                $params['class_id'] = $request->input('class_id');
            }

            if ($request->filled('section_id')) {
                $params['section_id'] = $request->input('section_id');
            }

            if ($request->filled('year')) {
                $params['year'] = $request->input('year');
            }

            if ($request->filled('roll')) {
                $params['roll'] = $request->input('roll');
            }

            if ($request->filled('student_id')) {
                $params['student_id'] = $request->input('student_id');
            }

            $response = Http::timeout(15)
                ->get(self::API_BASE_URL . '/results/lookup', $params);

            if ($response->successful()) {
                return $response->json('data');
            }

            $error = $response->json('message')
                ?? ($response->json('errors') ? 'Please check the provided information.' : 'No results found.');

            return null;
        } catch (\Exception $e) {
            Log::error('Result Lookup Error: ' . $e->getMessage());
            $error = 'Unable to retrieve results. Please try again later.';

            return null;
        }
    }

    /**
     * Collect the union of dynamic mark-distribution heads across all
     * marksheet subjects so the table header can render one column per head.
     */
    private function collectMarksheetHeadKeys(array $exams): array
    {
        $heads = [];

        foreach ($exams as $exam) {
            foreach (($exam['marksheet']['subjects'] ?? []) as $subject) {
                foreach ($subject as $key => $value) {
                    if (is_numeric($key) || in_array($key, self::MARKSHEET_STATIC_KEYS, true)) {
                        continue;
                    }

                    if (! in_array($key, $heads, true)) {
                        $heads[] = $key;
                    }
                }
            }
        }

        return $heads;
    }

    /**
     * Normalize an image path to an absolute URL. API responses may include
     * either a full URL or a storage-relative path.
     */
    private function normalizeImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return 'https://cloud.barnomala.com/storage/' . ltrim($path, '/');
    }

    /**
     * Get the school identifier (school_id from options or domain fallback).
     */
    private function getSchoolIdentifier()
    {
        $schoolId = Option::where('option_key', 'institute.tenant.id')
            ->value('option_value');

        if ($schoolId) {
            return (int) $schoolId;
        }

        return config('app.domain_name');
    }
}
