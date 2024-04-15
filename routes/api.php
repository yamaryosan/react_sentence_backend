<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SentenceController;
use App\Http\Controllers\ArticleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 文章アップロード用ルート
Route::post('/sentences/upload', [SentenceController::class, 'upload']);
// 文章用ルート
Route::resource('/sentences', SentenceController::class);

// 記事アップロード用ルート
Route::post('/articles/upload', [ArticleController::class, 'upload']);
// 記事検索用ルート
Route::get('/search', [ArticleController::class, 'search']);
// 記事全削除用ルート
Route::delete('/articles/deleteAll', [ArticleController::class, 'truncate']);
// 記事用ルート
Route::resource('/articles', ArticleController::class);
