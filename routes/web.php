<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\AuthenticateSession;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login',[AuthController::class, 'loginview'])->name('loginview');
Route::post('/login',[AuthController::class, 'login'])->name('login');
Route::get('/register',[AuthController::class, 'registerview'])->name('registerview');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/piramid', [AuthController::class, 'piramid']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (){
        return view('dashboard');
    });
    Route::get('/profile', function (){
        return view ('auth.profile'); 
    });
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/getusers', [UserController::class, 'getusers']);
});

