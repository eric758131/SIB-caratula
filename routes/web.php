<?php

use App\Http\Controllers\CaratulaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StandardController;
use App\Http\Controllers\PrimaryCategoryController;
use App\Http\Controllers\SecondaryCategoryController;
use App\Http\Controllers\TertiaryCategoryController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\ParameterController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\RequiredDocumentController;
use Illuminate\Support\Facades\Route;

// Pública
Route::get('/', [CaratulaController::class, 'index'])->name('caratula.index');

// Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ Módulo real
    Route::resource('countries', CountryController::class)->except(['show']);
    Route::resource('standards', StandardController::class)->except(['show']);
    Route::resource('primary-categories', PrimaryCategoryController::class)->except(['show']);
    Route::resource('secondary-categories', SecondaryCategoryController::class)->except(['show']);
    Route::resource('tertiary-categories', TertiaryCategoryController::class)->except(['show']);
    Route::resource('universities', UniversityController::class)->except(['show']);
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('specialties', SpecialtyController::class)->except(['show']);
    Route::resource('engineers', EngineerController::class)->except(['show']);
    Route::resource('parameters', ParameterController::class)->except(['show']);
    Route::resource('owners', OwnerController::class)->except(['show']);
    Route::resource('required-documents', RequiredDocumentController::class)->except(['show']);
});