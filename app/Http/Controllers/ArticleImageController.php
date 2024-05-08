<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArticleImageController extends Controller
{
    public function upload(Request $request)
    {
        $images = $request->file('images');
        foreach ($images as $image) {
            $image->move(storage_path('app/public/images'), $image->getClientOriginalName());
        }
    return response()->json(['message' => '画像をアップロードしました']);
    }
}
