<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\EngineerApiController;
use App\Http\Controllers\Api\OwnerApiController;
use App\Http\Controllers\Api\ProcedureApiController;

/* ============================================================
 |  Catálogos (públicos)
 * ============================================================ */
Route::get('/primary-categories', [CatalogController::class, 'primaryCategories']);
Route::get('/primary-categories/{primaryCategory}/secondaries', [CatalogController::class, 'secondaryCategories']);
Route::get('/secondaries/{secondaryCategory}/tertiaries', [CatalogController::class, 'tertiaryCategories']);
Route::get('/tertiaries/{tertiaryCategory}', [CatalogController::class, 'tertiaryCategoryDetail']);
Route::get('/tertiaries/{tertiaryCategory}/required-documents/download', [CatalogController::class, 'downloadAllRequiredDocuments'])
    ->name('api.tertiaries.required-documents.download');

Route::get('/topics', [CatalogController::class, 'topics']);
Route::get('/standards', [CatalogController::class, 'standards']);

Route::get('/required-documents/{requiredDocument}/download', [CatalogController::class, 'downloadRequiredDocument'])
    ->name('api.required-documents.download');

/* ============================================================
 |  Propietarios e ingenieros: búsqueda + alta rápida
 * ============================================================ */
Route::get('/owners', [CatalogController::class, 'owners']);
Route::get('/engineers', [CatalogController::class, 'engineers']);
Route::get('/engineers/form-options', [CatalogController::class, 'engineerFormOptions']);

Route::middleware('throttle:30,1')->group(function () {
    Route::post('/owners', [OwnerApiController::class, 'store']);
    Route::post('/engineers', [EngineerApiController::class, 'store']);
});

/* ============================================================
 |  Trámites (asistente público, identificados por hash_code)
 * ============================================================ */
Route::post('/procedures', [ProcedureApiController::class, 'store'])->middleware('throttle:30,1');

Route::prefix('/procedures/{procedure:hash_code}')->group(function () {
    Route::get('/', [ProcedureApiController::class, 'show']);
    Route::put('/', [ProcedureApiController::class, 'update']);
    Route::post('/submit', [ProcedureApiController::class, 'submit']);

    Route::post('/documents', [ProcedureApiController::class, 'uploadDocument']);
    Route::get('/documents/{procedureDocument}', [ProcedureApiController::class, 'downloadDocument'])
        ->name('api.procedures.documents.download');
    Route::delete('/documents/{procedureDocument}', [ProcedureApiController::class, 'deleteDocument']);
});
