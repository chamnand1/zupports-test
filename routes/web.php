<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/restaurants', function () {
    return Inertia::render('Restaurants');
})->name('restaurants');

require __DIR__.'/restaurants.php';
