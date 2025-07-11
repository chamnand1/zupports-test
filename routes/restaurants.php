<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Restaurants\RestaurantController;

Route::get('/restaurants/search', [RestaurantController::class, 'search'])->name('restaurants.search');