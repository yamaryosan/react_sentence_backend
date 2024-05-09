<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleImageController extends Controller
{
    public function upload(Request $request)
    {
        $images = $request->file('images');
        foreach ($images as $image) {
            // 重複する画像がある場合は上書きする
            $image->move(storage_path('app/public/images'), $image->getClientOriginalName());
        }
        return response()->json(['message' => '画像をアップロードしました']);
    }

    /**
     * 画像を全削除する
     */
    public function truncate()
    {
        $files = glob(storage_path('app/public/images/*'));
        foreach ($files as $file) {
            unlink($file);
        }
        if (empty(glob(storage_path('app/public/images/*')))) {
            return response()->json(['message' => '全ての画像を削除しました']);
        } else {
            return response()->json(['message' => '画像の削除に失敗しました'], 500);
        }
    }
}
