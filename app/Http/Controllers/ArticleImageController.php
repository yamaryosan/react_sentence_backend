<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Jobs\UploadImagesJob;
class ArticleImageController extends Controller
{
    /**
     * 画像をS3にアップロードする
     */
    public function upload(Request $request)
    {
        // ファイルの存在確認
        $images = $request->file('images');
        if (empty($images)) {
            return response()->json(['message' => 'ファイルが存在しません'], 400);
        }

        // ファイル群を一時保存
        $imageDir = storage_path('app/images');
        //以前のapp/imagesのディレクトリとファイルを削除
        $previousFiles = glob($imageDir . '/*');
        foreach ($previousFiles as $previousFile) {
            unlink($previousFile);
        }
        // app/imagesのディレクトリを削除
        if (is_dir($imageDir)) {
            rmdir($imageDir);
        }
        // app/imagesのディレクトリを作成
        mkdir($imageDir);
        // ファイルを一時保存
        foreach ($images as $image) {
            $image->move($imageDir, $image->getClientOriginalName());
        }

        // 画像をS3にアップロード
        UploadImagesJob::dispatch($imageDir);

        return response()->json(['message' => '画像をアップロードしました']);
    }

    /**
     * 画像を全削除する
     */
    public function truncate()
    {
        Storage::disk('s3')->deleteDirectory('images/');
        return response()->json(['message' => '画像を削除しました']);
    }
}
