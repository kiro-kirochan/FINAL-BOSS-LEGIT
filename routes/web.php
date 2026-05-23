<?php

use Illuminate\Support\Facades\Route;

// Catch-all: any non-API URL loads the Blade shell that boots React
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
