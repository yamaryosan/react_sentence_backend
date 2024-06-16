<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomePageController;
use App\Http\Controllers\SentenceController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleImageController;
use App\Http\Controllers\ContactController;

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

// 記事全削除用ルート
Route::delete('/articles/deleteAll', [ArticleController::class, 'truncate']);
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

// 問い合わせ用ルート
Route::post('/contacts', [ContactController::class, 'store']);
