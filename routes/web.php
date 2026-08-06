<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\SpeechController as AdminSpeechController;
use App\Http\Controllers\Admin\OptionController as AdminOptionController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\DataTransferController as AdminDataTransferController;
use App\Http\Controllers\RedirectToBranchController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/up', function () {
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('optimize:clear');
    
    return redirect('/')->with('status', 'Application is up to date!');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery.index');
Route::get('/gallery/{gallery}', [PageController::class, 'galleryDetail'])->name('gallery.show');

Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::resource('notices', AdminNoticeController::class);
    Route::resource('news', AdminNewsController::class);
    Route::resource('gallery', AdminGalleryController::class);
    Route::resource('speeches', AdminSpeechController::class);
    Route::post('speeches/quick', [AdminSpeechController::class, 'storeQuick'])->name('speeches.quick');
    Route::post('speeches/row-config', [AdminSpeechController::class, 'updateRowConfig'])->name('speeches.row-config');

    Route::get('branding', [AdminOptionController::class, 'branding'])->name('branding.index');
    Route::post('branding', [AdminOptionController::class, 'updateBranding'])->name('branding.update');

    Route::get('sliders', [AdminOptionController::class, 'slider'])->name('sliders.index');
    Route::post('sliders', [AdminOptionController::class, 'updateSlider'])->name('sliders.update');

    Route::get('settings', [AdminOptionController::class, 'settings'])->name('settings.index');
    Route::post('settings', [AdminOptionController::class, 'updateSettings'])->name('settings.update');

    Route::get('layout', [AdminOptionController::class, 'layout'])->name('layout.index');
    Route::post('layout', [AdminOptionController::class, 'updateLayout'])->name('layout.update');

    Route::get('demographics', [AdminOptionController::class, 'demographics'])->name('demographics.index');
    Route::post('demographics', [AdminOptionController::class, 'updateDemographics'])->name('demographics.update');

    Route::get('transfer', [AdminDataTransferController::class, 'index'])->name('transfer.index');
    Route::post('transfer/lock', [AdminDataTransferController::class, 'toggleLock'])->name('transfer.lock');
    Route::post('transfer/speeches', [AdminDataTransferController::class, 'transferSpeeches'])->name('transfer.speeches');
    Route::post('transfer/sliders', [AdminDataTransferController::class, 'transferSliders'])->name('transfer.sliders');
    Route::post('transfer/galleries', [AdminDataTransferController::class, 'transferGalleries'])->name('transfer.galleries');
    Route::post('transfer/notices', [AdminDataTransferController::class, 'transferNotices'])->name('transfer.notices');
    Route::post('transfer/news', [AdminDataTransferController::class, 'transferNews'])->name('transfer.news');
});

Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/speeches', [PageController::class, 'speeches'])->name('speeches.index');
Route::get('/history', [PageController::class, 'history'])->name('history.index');
Route::get('/achievements', [PageController::class, 'achievements'])->name('achievements.index');
Route::get('/academic', [PageController::class, 'academic'])->name('academic.index');
Route::get('/academic-calendar', [PageController::class, 'academicCalendar'])->name('academic.calendar');
Route::get('/academic-rules', [PageController::class, 'academicRules'])->name('academic.rules');
Route::get('/class-schedule', [PageController::class, 'classSchedule'])->name('academic.schedule');
Route::get('/exam-schedule', [PageController::class, 'examSchedule'])->name('academic.exam-schedule');

Route::get('/tuition-fees', [PageController::class, 'tuitionFees'])->name('student.tuition-fees');
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/student-uniform', [PageController::class, 'uniform'])->name('student.uniform');
Route::get('/daily-activities', [PageController::class, 'activities'])->name('student.activities');
Route::get('/mobile-banking', [PageController::class, 'mobileBanking'])->name('student.mobile-banking');

Route::get('/result', [PageController::class, 'results'])->name('student.result');
Route::get('/teachers', [PageController::class, 'teachers'])->name('teachers.index');
Route::get('/lecturers', [PageController::class, 'lecturers'])->name('lecturers.index');
Route::get('/former-teachers', [PageController::class, 'formerTeachers'])->name('teachers.former');
Route::get('/teachers/{teacher}', [PageController::class, 'teacherDetail'])->name('teachers.show');

Route::get('/staff', [PageController::class, 'staff'])->name('staff.index');
Route::get('/former-staff', [PageController::class, 'formerStaff'])->name('staff.former');
Route::get('/staff/{staff}', [PageController::class, 'staffDetail'])->name('staff.show');

Route::get('/committees', [PageController::class, 'committees'])->name('committees.index');
Route::get('/committees/{committee}', [PageController::class, 'committeeDetail'])->name('committees.show');

Route::get('/contact-us', [PageController::class, 'contact'])->name('contact.index');
Route::get('/apply', [PageController::class, 'apply'])->name('apply.index');
Route::post('/apply', [PageController::class, 'applySubmit'])->name('apply.submit');
Route::post('/contact-us', [PageController::class, 'contactSubmit'])->name('contact.submit');

Route::prefix('notices')->name('notices.')->group(function () {
    Route::get('/', [NoticeController::class, 'index'])->name('index');
    Route::get('/{notice}', [NoticeController::class, 'show'])->name('show');
});

Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/{news}', [NewsController::class, 'show'])->name('show');
});

// Catch-all route for branch subdomain redirects — must be last
Route::get('/{subdomain}', RedirectToBranchController::class);
