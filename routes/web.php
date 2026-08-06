<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReplyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/sentry-test', [\App\Http\Controllers\SentryTestController::class, 'trigger']);

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::get('/tickets', [TicketController::class, 'index'])->middleware(['auth'])->name('tickets.index');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->middleware(['auth'])->name('tickets.show');
Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->middleware(['auth'])->name('tickets.update');
Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->middleware(['auth'])->name('tickets.assign');
Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store'])->middleware(['auth'])->name('tickets.replies.store');
Route::post('/tickets/polish-reply', [TicketReplyController::class, 'polish'])->middleware(['auth'])->name('tickets.replies.polish');
Route::post('/tickets/{ticket}/summarize', [TicketController::class, 'summarize'])->middleware(['auth'])->name('tickets.summarize');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
