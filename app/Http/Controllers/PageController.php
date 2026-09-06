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
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    use BuildsPublicPageData;

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
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(department, '')) NOT LIKE ?", ['%incharge%'])
                    ->whereRaw("LOWER(COALESCE(designation, '')) NOT LIKE ?", ['%incharge%']);
            })
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

    public function incharges(): View
    {
        $incharges = Staff::where('status', 'active')
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(department, '')) LIKE ?", ['%incharge%'])
                    ->orWhereRaw("LOWER(COALESCE(designation, '')) LIKE ?", ['%incharge%']);
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('academic.incharges', array_merge($this->getPublicPageData(), ['incharges' => $incharges]));
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

}
