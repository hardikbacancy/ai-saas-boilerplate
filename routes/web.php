<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');

Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
Route::get('/billing', fn () => Inertia::render('Billing'))->name('billing');
Route::get('/security', fn () => Inertia::render('Security'))->name('security');

Route::get('/email/verify/{id}/{hash}', function (int $id, string $hash) {
    $user = User::findOrFail($id);

    if (! hash_equals((string) sha1($user->getEmailForVerification()), $hash)) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect('/dashboard?verified=1');
})->middleware('signed')->name('verification.verify');
