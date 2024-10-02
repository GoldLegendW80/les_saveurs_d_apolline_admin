<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::get('/menus/all', [MenuController::class, 'getAllMenus']);
