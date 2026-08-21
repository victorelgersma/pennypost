<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\LoginLinkController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [LoginLinkController::class, 'store'])
        ->name('login.link');

    Route::get('login/{user}', [LoginLinkController::class, 'authenticate'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('login.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/onboarding/name', [OnboardingController::class, 'editName'])
        ->name('onboarding.name');

    Route::post('/onboarding/name', [OnboardingController::class, 'updateName'])
        ->name('onboarding.name.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});