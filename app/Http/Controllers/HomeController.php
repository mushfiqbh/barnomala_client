<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsPublicPageData;
use App\Models\Post;
use App\Models\Speech;
use App\Models\Teacher;
use App\Models\CommitteeMember;
use App\Models\Gallery;

class HomeController extends Controller
{
    use BuildsPublicPageData;

    public function index()
    {
        $publicData = $this->getPublicPageData();
        $options = $publicData['options'];

        $notices = Post::notices()->with('artifacts')
            ->where('is_active', true)
            ->orderBy('is_urgent', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(10)
            ->get();

        $speeches = Speech::query()
            ->where('is_active', true)
            ->orderBy('row_index')
            ->orderBy('column_index')
            ->get();

        $featuredNews = Post::news()->where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        $galleryItems = Gallery::orderBy('date', 'desc')
            ->take(8)
            ->get();

        $sliderImages = $options['institute.branding.slider_json'] ?? [];
        if (is_string($sliderImages)) {
            $sliderImages = json_decode($sliderImages, true) ?: [];
        }

        $teachers = Teacher::where('status', true)
            ->orderBy('priority_index', 'asc')
            ->take(12)
            ->get();

        $generalCommitteeMembers = CommitteeMember::whereHas('committee', function ($query) {
                $query->where('type', 'general')->where('status', 'active');
            })
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        $stats = [
            ['label' => 'Classes', 'count' => $options['institute.stats.classes_count'] ?? $options['totalClasses'] ?? null],
            ['label' => 'Students', 'count' => $options['institute.stats.students_count'] ?? $options['totalStudents'] ?? null],
            ['label' => 'Teachers', 'count' => $options['institute.stats.teachers_count'] ?? $options['totalTeachers'] ?? null],
            ['label' => 'Staffs', 'count' => $options['institute.stats.staffs_count'] ?? $options['totalStaffs'] ?? null],
        ];


        $quickLinks = [
            ['label' => 'Student', 'icon' => 'user-graduate', 'color' => 'bg-green-600', 'url' => '/student'],
            ['label' => 'Teachers', 'icon' => 'user', 'color' => 'bg-orange-500', 'url' => '/teachers'],
            ['label' => 'Attendance', 'icon' => 'check', 'color' => 'bg-blue-600', 'url' => '/attendance'],
            ['label' => 'Result', 'icon' => 'bolt', 'color' => 'bg-red-600', 'url' => '/result'],
            ['label' => 'Routine', 'icon' => 'bell', 'color' => 'bg-green-600', 'url' => '/routine'],
            ['label' => 'Syllabus', 'icon' => 'book', 'color' => 'bg-orange-500', 'url' => '/syllabus'],
            ['label' => 'Academic Calendar', 'icon' => 'calendar', 'color' => 'bg-blue-600', 'url' => '/academic-calendar'],
            ['label' => 'Photo Gallery', 'icon' => 'camera', 'color' => 'bg-red-600', 'url' => '/gallery'],
            ['label' => 'Download', 'icon' => 'download', 'color' => 'bg-green-600', 'url' => '/download'],
            ['label' => 'News', 'icon' => 'bell', 'color' => 'bg-orange-500', 'url' => '/news'],
            ['label' => 'Notice', 'icon' => 'quote-left', 'color' => 'bg-blue-600', 'url' => '/notices'],
            ['label' => 'Career', 'icon' => 'briefcase', 'color' => 'bg-red-600', 'url' => '/careers'],
        ];

        return view('home.index', array_merge($publicData, [
            'notices' => $notices,
            'speeches' => $speeches,
            'featuredNews' => $featuredNews,
            'galleryItems' => $galleryItems,
            'sliderImages' => $sliderImages,
            'teachers' => $teachers,
            'generalCommitteeMembers' => $generalCommitteeMembers,
            'stats' => $stats,
            'quickLinks' => $quickLinks,
        ]));
    }
}
