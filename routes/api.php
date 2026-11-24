<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\GoogleAuthController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Chats\ConversationController;
use App\Http\Controllers\Api\V1\Chats\MessageController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Meetings\MeetingController;
use App\Http\Controllers\Api\V1\Meetings\MeetingNoteController;
use App\Http\Controllers\Api\V1\Meetings\MeetingSummaryController;
use App\Http\Controllers\Api\V1\Meetings\TranscriptController;
use App\Http\Controllers\Api\V1\Projects\ProjectController;
use App\Http\Controllers\Api\V1\Projects\ProjectMemberController;
use App\Http\Controllers\Api\V1\Tasks\CommentController;
use App\Http\Controllers\Api\V1\Tasks\TaskController;
use App\Http\Controllers\Api\V1\Tasks\TaskReorderController;
use App\Http\Controllers\Api\V1\Tasks\TaskStatusController;
use App\Http\Controllers\Api\V1\Tasks\TimeLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function (): void {
    // Health check
    Route::get('/health', HealthController::class)->name('health');

    // Auth routes
    Route::prefix('auth')->as('auth.')->group(function (): void {
        // Public guest endpoints
        Route::post('/register', RegisterController::class)->name('register');
        Route::post('/login', LoginController::class)->middleware('throttle:login')->name('login');
        Route::post('/google', GoogleAuthController::class)->middleware('throttle:login')->name('google');
        Route::post('/forgot-password', ForgotPasswordController::class)->middleware('throttle:6,1')->name('forgot-password');
        Route::post('/reset-password', ResetPasswordController::class)->middleware('throttle:6,1')->name('reset-password');
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');

        // Authenticated auth endpoints
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', MeController::class)->name('me');
            Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
            Route::post('/logout-all', [LogoutController::class, 'logoutAll'])->name('logout-all');
            Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
                ->middleware('throttle:6,1')
                ->name('verification.send');
        });
    });

    // Authenticated core application endpoints
    Route::middleware('auth:sanctum')->group(function (): void {
        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::get('projects/{project}/members', [ProjectMemberController::class, 'index'])->name('projects.members.index');
        Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
        Route::delete('projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
        Route::put('projects/{project}/tasks/reorder', [TaskReorderController::class, 'update'])->name('projects.tasks.reorder');

        // Tasks
        Route::get('projects/{project}/tasks', [TaskController::class, 'index'])->name('projects.tasks.index');
        Route::post('projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
        Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::patch('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('tasks/{task}/status', [TaskStatusController::class, 'update'])->name('tasks.status.update');

        // Time Logs
        Route::get('tasks/{task}/time-logs', [TimeLogController::class, 'index'])->name('tasks.time-logs.index');
        Route::post('tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('tasks.time-logs.store');
        Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');

        // Comments
        Route::get('tasks/{task}/comments', [CommentController::class, 'index'])->name('tasks.comments.index');
        Route::post('tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Chats & Conversations
        Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::post('conversations/direct', [ConversationController::class, 'direct'])->name('conversations.direct');
        Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::post('conversations/{conversation}/read', [ConversationController::class, 'read'])->name('conversations.read');
        Route::post('conversations/{conversation}/instant-meeting', [ConversationController::class, 'instantMeeting'])->name('conversations.instant-meeting');

        // Messages
        Route::get('conversations/{conversation}/messages', [MessageController::class, 'index'])->name('conversations.messages.index');
        Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])
            ->middleware('throttle:messages')
            ->name('conversations.messages.store');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        // Meetings & Calendar
        Route::apiResource('meetings', MeetingController::class);
        Route::post('meetings/{meeting}/rsvp', [MeetingController::class, 'rsvp'])->name('meetings.rsvp');
        Route::post('meetings/{meeting}/recording', [MeetingController::class, 'uploadRecording'])->name('meetings.recording');
        Route::get('meetings/{meeting}/transcripts', [TranscriptController::class, 'index'])->name('meetings.transcripts.index');
        Route::post('meetings/{meeting}/transcripts', [TranscriptController::class, 'store'])->name('meetings.transcripts.store');
        Route::get('meetings/{meeting}/summary', [MeetingSummaryController::class, 'show'])->name('meetings.summary.show');
        Route::post('meetings/{meeting}/summaries/generate', [MeetingSummaryController::class, 'generate'])->name('meetings.summaries.generate');
        Route::get('meetings/{meeting}/notes', [MeetingNoteController::class, 'index'])->name('meetings.notes.index');
        Route::post('meetings/{meeting}/notes', [MeetingNoteController::class, 'store'])->name('meetings.notes.store');
        Route::delete('meetings/notes/{note}', [MeetingNoteController::class, 'destroy'])->name('meetings.notes.destroy');
    });
});
