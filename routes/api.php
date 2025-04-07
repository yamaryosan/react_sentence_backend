<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomePageController;
use App\Http\Controllers\SentenceController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleImageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ホーム画面用ルート
Route::get('/home', [HomePageController::class, 'index']);

// 文章アップロード用ルート
Route::post('/sentences/upload', [SentenceController::class, 'upload']);
// 文章全削除用ルート
Route::delete('/sentences/deleteAll', [SentenceController::class, 'truncate']);
// 文章用ルート
Route::resource('/sentences', SentenceController::class);

// 記事アップロード用ルート
Route::post('/articles/upload', [ArticleController::class, 'upload']);

// 記事カテゴリー別削除用ルート
Route::delete('/articles/deleteByCategory', [ArticleController::class, 'deleteByCategory']);
// 記事カテゴリー別取得用ルート
Route::get('/articles/categories/{category}', [ArticleController::class, 'getArticlesByCategory']);
// 記事カテゴリー取得用ルート
Route::get('/articles/categories', [ArticleController::class, 'getCategories']);
// 記事ランダム取得用ルート
Route::get('/articles/random', [ArticleController::class, 'getRandom']);
// 記事用ルート
Route::resource('/articles', ArticleController::class);

// 記事画像アップロード用ルート
Route::post('/articleImages/upload', [ArticleImageController::class, 'upload']);
Route::delete('/articleImages/deleteAll', [ArticleImageController::class, 'truncate']);
