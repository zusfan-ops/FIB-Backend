<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'app' => 'SakuraKotoba (桜言葉)',
    'version' => '1.0.0',
    'live_url' => 'https://fib.ordr.my.id',
    'docs' => '/api/v1',
    'status' => 'online',
]));

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/auth/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
        Route::post('/auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

        Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);

        // SRS
        Route::apiResource('decks', App\Http\Controllers\Api\DeckController::class);
        Route::post('decks/{deck}/import', [App\Http\Controllers\Api\DeckController::class, 'import']);
        Route::get('decks/{deck}/cards', [App\Http\Controllers\Api\CardController::class, 'index']);
        Route::post('decks/{deck}/cards', [App\Http\Controllers\Api\CardController::class, 'store']);
        Route::put('cards/{card}', [App\Http\Controllers\Api\CardController::class, 'update']);
        Route::delete('cards/{card}', [App\Http\Controllers\Api\CardController::class, 'destroy']);
        Route::get('review/due', [App\Http\Controllers\Api\ReviewController::class, 'due']);
        Route::get('review/stats', [App\Http\Controllers\Api\ReviewController::class, 'stats']);
        Route::post('review/{card}', [App\Http\Controllers\Api\ReviewController::class, 'submit']);

        // Reading tracker + clips
        Route::apiResource('books', App\Http\Controllers\Api\BookController::class);
        Route::apiResource('books.chapters', App\Http\Controllers\Api\ChapterController::class)->except(['index']);
        Route::get('books/{book}/chapters', [App\Http\Controllers\Api\ChapterController::class, 'index']);
        Route::post('chapters/{chapter}/progress', [App\Http\Controllers\Api\ChapterController::class, 'updateProgress']);
        Route::post('chapters/{chapter}/notes', [App\Http\Controllers\Api\ChapterController::class, 'storeNote']);
        Route::delete('notes/{note}', [App\Http\Controllers\Api\ChapterController::class, 'destroyNote']);
        Route::apiResource('clips', App\Http\Controllers\Api\ClipController::class)->except(['create', 'edit']);
        Route::post('clips/{clip}/to-card', [App\Http\Controllers\Api\ClipController::class, 'toCard']);

        // Grammar
        Route::get('grammar', [App\Http\Controllers\Api\GrammarController::class, 'index']);
        Route::get('grammar/{grammarPattern}', [App\Http\Controllers\Api\GrammarController::class, 'show']);
        Route::post('grammar', [App\Http\Controllers\Api\GrammarController::class, 'store']);
        Route::put('grammar/{grammarPattern}', [App\Http\Controllers\Api\GrammarController::class, 'update']);
        Route::delete('grammar/{grammarPattern}', [App\Http\Controllers\Api\GrammarController::class, 'destroy']);

        // Translation practice
        Route::apiResource('translations', App\Http\Controllers\Api\TranslationController::class)->except(['create', 'edit']);
        Route::post('translations/{translation}/submit-revision', [App\Http\Controllers\Api\TranslationController::class, 'submitRevision']);

        // Agenda & Tasks
        Route::apiResource('schedule-items', App\Http\Controllers\Api\ScheduleItemController::class)->except(['create', 'edit']);
        Route::apiResource('jlpt-targets', App\Http\Controllers\Api\JlptTargetController::class)->except(['create', 'edit']);
        Route::post('jlpt-targets/{jlptTarget}/check-item', [App\Http\Controllers\Api\JlptTargetController::class, 'checkItem']);
        Route::apiResource('plan-tasks', App\Http\Controllers\Api\PlanTaskController::class)->except(['create', 'edit']);
        Route::patch('plan-tasks/{planTask}/move', [App\Http\Controllers\Api\PlanTaskController::class, 'move']);

        // Fitur FIB UNDIP: Jadwal Kuliah (Reminder 2 Jam), Diary Kampus, & Foto Timeline
        Route::apiResource('class-schedules', App\Http\Controllers\Api\ClassScheduleController::class)->except(['create', 'edit']);
        Route::apiResource('campus-diaries', App\Http\Controllers\Api\CampusDiaryController::class)->except(['create', 'edit']);
        Route::apiResource('campus-photos', App\Http\Controllers\Api\CampusPhotoController::class)->except(['create', 'edit']);
        Route::get('my-photos', [App\Http\Controllers\Api\CampusPhotoController::class, 'myPhotos']);
        Route::post('campus-photos/{campusPhoto}/like', [App\Http\Controllers\Api\CampusPhotoController::class, 'like']);
        Route::get('campus-photos/{campusPhoto}/comments', [App\Http\Controllers\Api\CampusPhotoController::class, 'getComments']);
        Route::post('campus-photos/{campusPhoto}/comments', [App\Http\Controllers\Api\CampusPhotoController::class, 'addComment']);
        Route::delete('campus-photos/comments/{comment}', [App\Http\Controllers\Api\CampusPhotoController::class, 'deleteComment']);

        // Kamus Kanji & Kosakata
        Route::get('dictionary', [App\Http\Controllers\Api\DictionaryController::class, 'index']);
        Route::get('dictionary/quiz', [App\Http\Controllers\Api\DictionaryController::class, 'quiz']);
        Route::get('dictionary/{dictionaryEntry}', [App\Http\Controllers\Api\DictionaryController::class, 'show']);
        Route::post('dictionary/{dictionaryEntry}/save-to-deck', [App\Http\Controllers\Api\DictionaryController::class, 'saveToDeck']);

        // Simulasi Ujian JLPT
        Route::apiResource('jlpt-mock-results', App\Http\Controllers\Api\JlptMockResultController::class)->only(['index', 'store']);

        // Kalkulator IPK
        Route::apiResource('course-grades', App\Http\Controllers\Api\CourseGradeController::class)->except(['show', 'create', 'edit']);

        // Tracker Skripsi / Tugas Akhir
        Route::get('thesis', [App\Http\Controllers\Api\ThesisController::class, 'show']);
        Route::put('thesis/profile', [App\Http\Controllers\Api\ThesisController::class, 'updateProfile']);
        Route::post('thesis/milestones', [App\Http\Controllers\Api\ThesisController::class, 'storeMilestone']);
        Route::put('thesis/milestones/{thesisMilestone}', [App\Http\Controllers\Api\ThesisController::class, 'updateMilestone']);
        Route::delete('thesis/milestones/{thesisMilestone}', [App\Http\Controllers\Api\ThesisController::class, 'destroyMilestone']);
    });
});
