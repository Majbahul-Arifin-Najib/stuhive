<?php

use App\Http\Controllers\AccommodationController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CandidController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExamScheduleController;
use App\Http\Controllers\LostFoundController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MoneyController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PostDownloadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\StudyGroupController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');

    Route::get('notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
    Route::patch('notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    /* Shared post interactions -------------------------------------------- */

    Route::post('posts/{post}/reactions', [ReactionController::class, 'store'])->name('reactions.store');
    Route::delete('posts/{post}/reactions', [ReactionController::class, 'destroy'])->name('reactions.destroy');

    Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('posts/{post}/image', [PostDownloadController::class, 'image'])->name('posts.image');

    /* 1. Lost & Found ------------------------------------------------------ */

    Route::get('lost-found', [LostFoundController::class, 'index'])->name('lost_found.index');
    Route::post('lost-found', [LostFoundController::class, 'store'])->name('lost_found.store');
    Route::patch('lost-found/{post}/found', [LostFoundController::class, 'markFound'])->name('lost_found.found');
    Route::delete('lost-found/{post}', [LostFoundController::class, 'destroy'])->name('lost_found.destroy');

    /* 2. Candid sharing wall ----------------------------------------------- */

    Route::middleware('section:candid')->group(function () {
        Route::get('candid', [CandidController::class, 'index'])->name('candid.index');
        Route::post('candid', [CandidController::class, 'store'])->name('candid.store');
        Route::delete('candid/{post}', [CandidController::class, 'destroy'])->name('candid.destroy');
    });

    /* 3. Event announcements ----------------------------------------------- */

    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::post('events/{post}/interest', [EventController::class, 'toggleInterest'])->name('events.interest');
    Route::delete('events/{post}', [EventController::class, 'destroy'])->name('events.destroy');

    /* 5. Polls and voting --------------------------------------------------- */

    Route::get('polls', [PollController::class, 'index'])->name('polls.index');
    Route::post('polls', [PollController::class, 'store'])->name('polls.store');
    Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');
    Route::delete('polls/{poll}', [PollController::class, 'destroy'])->name('polls.destroy');

    /* 6. Resources library (note sharing) ----------------------------------- */

    Route::middleware('section:note')->group(function () {
        Route::get('resources', [ResourceController::class, 'index'])->name('resources.index');
        Route::post('resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::get('resources/{post}/download', [ResourceController::class, 'download'])->name('resources.download');
        Route::delete('resources/{post}', [ResourceController::class, 'destroy'])->name('resources.destroy');
    });

    /* 7. Exam schedule ------------------------------------------------------ */

    Route::get('exams', [ExamScheduleController::class, 'index'])->name('exams.index');
    Route::post('exams', [ExamScheduleController::class, 'store'])
        ->middleware('role:admin,faculty')
        ->name('exams.store');
    Route::delete('exams/{exam}', [ExamScheduleController::class, 'destroy'])
        ->middleware('role:admin,faculty')
        ->name('exams.destroy');

    /* 8. Campus pet allocation ---------------------------------------------- */

    Route::get('pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('pets', [PetController::class, 'store'])->name('pets.store');
    Route::delete('pets/{post}', [PetController::class, 'destroy'])->name('pets.destroy');

    /* 9. Consultation hub ---------------------------------------------------- */

    Route::get('consultations', [ConsultationController::class, 'index'])->name('consultations.index');
    Route::post('consultations', [ConsultationController::class, 'store'])
        ->middleware('role:faculty')
        ->name('consultations.store');
    Route::patch('consultations/{post}/postpone', [ConsultationController::class, 'postpone'])
        ->middleware('role:faculty')
        ->name('consultations.postpone');
    Route::post('consultations/{post}/book', [ConsultationController::class, 'book'])
        ->middleware('role:student')
        ->name('consultations.book');
    Route::delete('consultations/{post}/book', [ConsultationController::class, 'cancelBooking'])
        ->middleware('role:student')
        ->name('consultations.cancel');
    Route::delete('consultations/{post}', [ConsultationController::class, 'destroy'])->name('consultations.destroy');

    /* 10. Course discussion and review --------------------------------------- */

    Route::middleware('section:course_review')->group(function () {
        Route::get('course-reviews', [CourseReviewController::class, 'index'])->name('course_reviews.index');
        Route::post('course-reviews', [CourseReviewController::class, 'store'])->name('course_reviews.store');
        Route::delete('course-reviews/{post}', [CourseReviewController::class, 'destroy'])->name('course_reviews.destroy');
    });

    /* 11. Marketplace --------------------------------------------------------- */

    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::post('marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::patch('marketplace/{post}/sold', [MarketplaceController::class, 'markSold'])->name('marketplace.sold');
    Route::delete('marketplace/{post}', [MarketplaceController::class, 'destroy'])->name('marketplace.destroy');

    /* 12. Study group finder --------------------------------------------------- */

    Route::middleware('section:study_group')->group(function () {
        Route::get('study-groups', [StudyGroupController::class, 'index'])->name('study_groups.index');
        Route::post('study-groups', [StudyGroupController::class, 'store'])->name('study_groups.store');
        Route::post('study-groups/{post}/join', [StudyGroupController::class, 'join'])->name('study_groups.join');
        Route::get('study-groups/{post}/messages', [StudyGroupController::class, 'messages'])->name('study_groups.messages');
        Route::post('study-groups/{post}/messages', [StudyGroupController::class, 'sendMessage'])->name('study_groups.messages.store');
        Route::delete('study-groups/{post}', [StudyGroupController::class, 'destroy'])->name('study_groups.destroy');
    });

    /* 13. Accommodation -------------------------------------------------------- */

    Route::middleware('section:accommodation')->group(function () {
        Route::get('accommodations', [AccommodationController::class, 'index'])->name('accommodations.index');
        Route::post('accommodations', [AccommodationController::class, 'store'])->name('accommodations.store');
        Route::delete('accommodations/{post}', [AccommodationController::class, 'destroy'])->name('accommodations.destroy');
    });

    /* 14. Money management ------------------------------------------------------ */

    Route::middleware('role:student,admin')->group(function () {
        Route::get('money', [MoneyController::class, 'index'])->name('money.index');
        Route::post('money/budget', [MoneyController::class, 'storeBudget'])->name('money.budget.store');
        Route::post('money/expenses', [MoneyController::class, 'storeExpense'])->name('money.expenses.store');
        Route::delete('money/expenses/{expense}', [MoneyController::class, 'destroyExpense'])->name('money.expenses.destroy');
        Route::post('money/summary', [MoneyController::class, 'generateSummary'])->name('money.summary');
    });

    /* Administration ------------------------------------------------------------ */

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('moderation', [ModerationController::class, 'index'])->name('moderation.index');
        Route::delete('moderation/posts/{post}', [ModerationController::class, 'destroyPost'])->name('moderation.posts.destroy');
        Route::delete('moderation/polls/{poll}', [ModerationController::class, 'destroyPoll'])->name('moderation.polls.destroy');
    });
});
