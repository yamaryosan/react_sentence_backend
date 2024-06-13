<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleImageController extends Controller
{
    public function upload(Request $request)
    {
        $imagesPath = storage_path('app/public/images');
        // ディレクトリが存在しない場合は作成する
        if (!file_exists($imagesPath)) {
            mkdir($imagesPath, 0755, true);
            exec('sudo chown -R www-data:www-data ' . $imagesPath);
        }

        // ファイルの存在確認
        $images = $request->file('images');
        foreach ($images as $image) {
            // 重複する画像がある場合は上書きする
            $image->move($imagesPath, $image->getClientOriginalName());
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
