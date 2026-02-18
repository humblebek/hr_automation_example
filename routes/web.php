<?php

use App\Http\Controllers\ApplicationResumeController;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/', function () {
    return view('welcome');
});



Route::get('setwebhook', function () {
    $url = env('TELEGRAM_WEBHOOK_URL');
    $response = Telegram::setWebhook(['url' => $url]);
    return $response;
});


Route::middleware(['web','auth']) // adjust your admin guards
->get('/admin/applications/{application}/resume', [ApplicationResumeController::class, 'download'])
    ->name('admin.applications.resume');

