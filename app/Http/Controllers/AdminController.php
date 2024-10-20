<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * 文章表示のための認証を行う
     */
    public function checkSentenceAdmin(Request $request)
    {
        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has('sentence_session_key') === false) {
            $request->session()->put('sentence_session_key', 'not_verified');
            return response()->json(['isVerified' => 'false', 'env' => 'sentence_session_key']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get('sentence_session_key');

        // クエリがfalseの場合はfalseを返す
        if ($query === 'not_verified') {
            return response()->json(['isVerified' => 'false', 'env' => 'sentence_session_key']);
        }

        // クエリがtrueの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json(['isVerified' => 'true', 'env' => 'sentence_session_key']);
        }

        // クエリがfalseでもtrueでもない場合はfalseを返す
        return response()->json(['isVerified' => 'false', 'env' => 'sentence_session_key']);
    }

    /**
     * アップロードのための認証を行う
     */
    public function checkUploadAdmin(Request $request)
    {
        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has('upload_session_key') === false) {
            $request->session()->put('upload_session_key', 'not_verified');
            return response()->json(['isVerified' => 'false', 'message' => 'セッションが生成できません']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get('upload_session_key');

        // クエリがfalseの場合はfalseを返す
        if ($query === 'not_verified') {
            return response()->json(['isVerified' => 'false', 'message' => 'セッションが生成できません']);
        }

        // クエリがtrueの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json(['isVerified' => 'true', 'message' => '認証が完了しました']);
        }

        // クエリがfalseでもtrueでもない場合はfalseを返す
        return response()->json(['isVerified' => 'false', 'message' => 'クエリが不正です']);
    }
}
