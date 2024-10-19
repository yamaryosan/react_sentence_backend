<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        // publicディレクトリが存在しない場合は作成する
        if (!Storage::disk('s3')->exists('images/')) {
            Storage::disk('s3')->put('images/.gitkeep', '');
        }
        foreach ($images as $image) {
            Storage::disk('s3')->putFileAs('images/', $image, $image->getClientOriginalName());
        }
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
