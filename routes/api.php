<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
});



