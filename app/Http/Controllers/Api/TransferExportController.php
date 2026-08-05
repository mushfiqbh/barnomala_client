<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WordPressExportService;
use Illuminate\Http\JsonResponse;

class TransferExportController extends Controller
{
    public function __construct(private readonly WordPressExportService $exportService)
    {
    }

    public function students(): JsonResponse
    {
        return response()->json($this->exportService->exportStudents());
    }

    public function studentEnrollments(): JsonResponse
    {
        return response()->json($this->exportService->exportStudentEnrollments());
    }

    public function subjects(): JsonResponse
    {
        return response()->json($this->exportService->exportSubjects());
    }

    public function classes(): JsonResponse
    {
        return response()->json($this->exportService->exportClasses());
    }

    public function teachers(): JsonResponse
    {
        return response()->json($this->exportService->exportTeachers());
    }

    public function exams(): JsonResponse
    {
        return response()->json($this->exportService->exportExams());
    }

    public function examSchedules(): JsonResponse
    {
        return response()->json($this->exportService->exportExamSchedules());
    }

    public function examResults(): JsonResponse
    {
        return response()->json($this->exportService->exportExamResults());
    }

    public function sliderImages(): JsonResponse
    {
        return response()->json($this->exportService->exportSliderImages());
    }

    public function committees(): JsonResponse
    {
        return response()->json($this->exportService->exportCommittees());
    }

    public function governingBody(): JsonResponse
    {
        return response()->json($this->exportService->exportGoverningBody());
    }

    public function options(): JsonResponse
    {
        return response()->json($this->exportService->exportOptions());
    }
}
