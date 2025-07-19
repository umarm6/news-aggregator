<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthorsController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SourceController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->only(['index']);
    Route::apiResource('sources', SourceController::class)->only(['index']);
    Route::apiResource('authors', AuthorsController::class)->only(['index']);

});



