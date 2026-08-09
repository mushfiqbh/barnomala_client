<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Option;

class StudentController extends Controller
{
    use BuildsPublicPageData;

    private const API_BASE_URL = 'https://cloud.barnomala.com/api/v1';

    /**
     * Show students page with search and results
     */
    public function index(Request $request)
    {
        // Fetch filter options for dropdowns
        $filterOptions = $this->fetchFilterOptions();
        
        $student = null;
        $school = null;
        $enrollment = null;
        $error = null;

        // If search parameters are provided, fetch student data
        if ($request->filled(['class_id', 'section_id', 'year', 'roll']) || $request->filled('student_id')) {
            $student = $this->lookupStudent($request, $error);

            if ($student) {
                $school = $student['school'] ?? null;
                $enrollment = $student['enrollments'][0] ?? null;
                $student = $enrollment['student'] ?? null;
            }
        }

        return view('student.index', array_merge($this->getPublicPageData(), [
            'student' => $student,
            'school' => $school,
            'enrollment' => $enrollment,
            'error' => $error,
            'filterOptions' => $filterOptions,
            'filters' => [
                'class_id' => $request->input('class_id'),
                'section_id' => $request->input('section_id'),
                'year' => $request->input('year'),
                'roll' => $request->input('roll'),
                'student_id' => $request->input('student_id'),
            ]
        ]));
    }

    /**
     * Fetch filter options from API (initial load with school_id only)
     */
    private function fetchFilterOptions()
    {
        try {
            $schoolId = $this->getSchoolIdentifier();
            
            $response = Http::timeout(10)
                ->get(self::API_BASE_URL . '/students/lookup', [
                    'school_id' => $schoolId
                ]);

            if ($response->successful()) {
                return $response->json('data.filterOptions', []);
            }
        } catch (\Exception $e) {
            Log::error('Filter Options Error: ' . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Lookup student from API
     */
    private function lookupStudent(Request $request, &$error)
    {
        try {
            // Get school identifier (school_id or domain)
            $schoolId = $this->getSchoolIdentifier();
            
            // Build query parameters
            $params = [
                'school_id' => $schoolId
            ];
            
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

            // Make API request
            $response = Http::timeout(10)
                ->get(self::API_BASE_URL . '/students/lookup', $params);

            if ($response->successful()) {
                return $response->json('data');
            } else {
                $error = $response->json('message') ?? 'Student not found';
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Student Lookup Error: ' . $e->getMessage());
            $error = 'Unable to retrieve student information. Please try again later.';
            return null;
        }
    }

    /**
     * Get school identifier (school_id or domain)
     */
    private function getSchoolIdentifier()
    {
        // Try to get school_id from options
        $schoolId = Option::where('option_key', 'institute.tenant.id')
            ->value('option_value');

        if ($schoolId) {
            return (int) $schoolId;
        }

        // Fallback to domain from config if available
        return config('app.domain_name');
    }
}
