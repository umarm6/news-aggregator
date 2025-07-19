<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->only(['index']);
});



