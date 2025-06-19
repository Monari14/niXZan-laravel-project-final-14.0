<?php

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');
