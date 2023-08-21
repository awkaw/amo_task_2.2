<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [\App\Http\Controllers\Controller::class, "home"])->name("home");
Route::get('/input', [\App\Http\Controllers\Controller::class, "input"])->name("input");
Route::get('/getToken', [\App\Http\Controllers\TokenController::class, "getToken"])->name("getToken");
Route::get('/saveToken', [\App\Http\Controllers\TokenController::class, "saveToken"])->name("saveToken");
Route::post('/send', [\App\Http\Controllers\Controller::class, "send"])->name("send");
