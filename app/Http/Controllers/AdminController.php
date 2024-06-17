<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * 認証を行う
     */
    public function checkAdmin(Request $request)
    {
        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has('query') === false) {
            $request->session()->put('query', 'not_verified');
            return response()->json(['isVerified' => 'false']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get('query');

        // クエリがfalseの場合はfalseを返す
        if ($query === 'not_verified') {
            return response()->json(['isVerified' => 'false']);
        }

        // クエリがtrueの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json(['isVerified' => 'true']);
        }

        // クエリがfalseでもtrueでもない場合はエラーを返す
        return response()->json(['message' => 'クエリが不正です'], 400);
    }
}
