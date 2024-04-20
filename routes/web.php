<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

// 認証用ルート
Route::get('/verify', [AdminController::class, 'checkAdmin']);
// 記事検索用ルート
Route::get('/search', [ArticleController::class, 'search']);
