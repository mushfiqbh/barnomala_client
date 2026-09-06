<?php

use App\Http\Controllers\Api\CommitteeSyncController;
use App\Http\Controllers\Api\DataTransferController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\GallerySyncController;
use App\Http\Controllers\Api\NewsSyncController;
use App\Http\Controllers\Api\NoticeSyncController;
use App\Http\Controllers\Api\OptionSyncController;
use App\Http\Controllers\Api\PostSyncController;
use App\Http\Controllers\Api\SpeechSyncController;
use App\Http\Controllers\Api\StaffSyncController;
use App\Http\Controllers\Api\TeacherSyncController;
use App\Http\Controllers\Api\TransferExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('api.token')->group(function () {
        Route::post('upload', [FileUploadController::class, 'upload']);
        Route::post('options/sync', [OptionSyncController::class, 'sync']);
        Route::post('teachers/sync', [TeacherSyncController::class, 'sync']);
        Route::post('staff/sync', [StaffSyncController::class, 'sync']);
        Route::post('committees/sync', [CommitteeSyncController::class, 'sync']);
        Route::post('notices/sync', [NoticeSyncController::class, 'sync']);
        Route::post('news/sync', [NewsSyncController::class, 'sync']);
        Route::post('galleries/sync', [GallerySyncController::class, 'sync']);
        Route::post('speeches/sync', [SpeechSyncController::class, 'sync']);
        Route::post('posts/sync', [PostSyncController::class, 'sync']);
        Route::post('transfer/all', [DataTransferController::class, 'transferAll'])->name('api.transfer.all');
    });

    Route::get('students', [TransferExportController::class, 'students']);
    Route::get('student/enrollments', [TransferExportController::class, 'studentEnrollments']);
    Route::get('subjects', [TransferExportController::class, 'subjects']);
    Route::get('classes', [TransferExportController::class, 'classes']);
    Route::get('teachers', [TransferExportController::class, 'teachers']);
    Route::get('exams', [TransferExportController::class, 'exams']);
    Route::get('exams/schedules', [TransferExportController::class, 'examSchedules']);
    Route::get('exams/results', [TransferExportController::class, 'examResults']);
    Route::get('slider-images', [TransferExportController::class, 'sliderImages']);
    Route::get('committees', [TransferExportController::class, 'committees']);
    Route::get('governing-body', [TransferExportController::class, 'governingBody']);
    Route::get('options', [TransferExportController::class, 'options']);
    Route::get('notices', [NoticeSyncController::class, 'index']);
    Route::get('news', [NewsSyncController::class, 'index']);
    Route::get('galleries', [GallerySyncController::class, 'index']);
    Route::get('speeches', [SpeechSyncController::class, 'index']);
    Route::get('posts', [PostSyncController::class, 'index']);
    Route::get('posts/{type}', [PostSyncController::class, 'indexByType'])
        ->where('type', '[a-z_]+');
    Route::get('posts/{type}/{id}', [PostSyncController::class, 'show'])
        ->where('type', '[a-z_]+')
        ->where('id', '[0-9]+');
});
