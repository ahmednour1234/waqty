<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(config('scribe.laravel.middleware', []))->group(function () {
    Route::view('/docs/admin', 'scribe_admin.index')->name('scribe.admin');
    Route::view('/docs/provider', 'scribe_provider.index')->name('scribe.provider');
    Route::view('/docs/user', 'scribe_user.index')->name('scribe.user');
    Route::view('/docs/employee', 'scribe_employee.index')->name('scribe.employee');
    Route::view('/docs/public', 'scribe_public.index')->name('scribe.public');
});
