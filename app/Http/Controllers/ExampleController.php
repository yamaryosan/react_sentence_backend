<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class ExampleController extends Controller
{
    public function uploadToS3()
    {
        // ファイルの存在確認
        if (!Storage::exists('test.txt')) {
            return 'ファイルが存在しません';
        }
        $fileContent = Storage::get('test.txt');
        Storage::disk('s3')->put('test.txt', $fileContent);
        $path = Storage::disk('s3')->temporaryUrl('test.txt', now()->addMinutes(5));
        return $path;
    }

    public function downloadFromS3()
    {
        $path = Storage::disk('s3')->temporaryUrl('test.txt', now()->addMinutes(5));
        $fileContent = Storage::disk('s3')->get('test.txt');
        Storage::put('test2.txt', $fileContent);
        return 'ファイルをダウンロードしました';
    }

    // 画像表示用ルート
    public function showImage()
    {
        // ファイルの存在確認
        if (!Storage::exists('noimage.png')) {
            return 'ファイルが存在しません';
        }
        // アップロード
        $fileContent = Storage::get('noimage.png');
        Storage::disk('s3')->put('noimage.png', $fileContent);
        // 表示
        $path = Storage::disk('s3')->temporaryUrl('noimage.png', now()->addMinutes(5));
        return '<img src="' . $path . '">';
    }
}
