<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\SentenceController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\ExampleController;

Route::get('/', function () {
    return view('welcome');
});

// 文章表示のための認証用ルート
Route::get('/sentenceVerify', [AdminController::class, 'checkSentenceAdmin']);
// アップロードのための認証用ルート
Route::get('/uploadVerify', [AdminController::class, 'checkUploadAdmin']);

// 管理者認証ルート
Route::get('/admin', [AdminController::class, 'verify']);

// 記事検索用ルート
Route::get('/articles/search', [ArticleController::class, 'search']);
// 文章検索用ルート
Route::get('/sentences/search', [SentenceController::class, 'search']);

// S3アップロード用ルート
Route::get('/uploadToS3', [ExampleController::class, 'uploadToS3']);
Route::get('/downloadFromS3', [ExampleController::class, 'downloadFromS3']);
Route::get('/showImage', [ExampleController::class, 'showImage']);
