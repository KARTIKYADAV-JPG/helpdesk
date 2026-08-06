<?php

use App\Http\Controllers\WebhookTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the Application with the "api" middleware group.
|
*/

Route::post('/webhooks/tickets', [WebhookTicketController::class, 'store'])->name('api.webhooks.tickets.store');
Route::post('/tickets/webhook', [WebhookTicketController::class, 'store']);
