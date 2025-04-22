<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ResourceController;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Rotas para Tenants
    Route::resource('tenants', TenantController::class);
    
    // Rotas para Serviços
    Route::resource('services', ServiceController::class);
    
    // Rotas para Recursos
    Route::resource('resources', ResourceController::class);
});
