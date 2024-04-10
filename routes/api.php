<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SentenceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('/sentences', SentenceController::class);
// 文章アップロード用ルート
Route::post('/sentences/upload', [SentenceController::class, 'upload']);
// 記事アップロード用ルート
Route::post('/articles/upload', [ArticleController::class, 'upload']);
