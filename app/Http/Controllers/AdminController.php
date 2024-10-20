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
        // セッションが生成できない場合はエラーを返す
        if ($request->session()->getId() === '') {
            return response()->json(['message' => 'セッションが生成できません'], 500);
        }

        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has(env('SENTENCE_SESSION_KEY')) === false) {
            $request->session()->put(env('SENTENCE_SESSION_KEY'), 'not_verified');
            return response()->json(['isVerified' => 'false']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get(env('SENTENCE_SESSION_KEY'));

        // クエリがfalseの場合はfalseを返す
        if ($query === 'not_verified') {
            return response()->json(['isVerified' => 'false']);
        }

        // クエリがtrueの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json(['isVerified' => 'true']);
        }

        // クエリがfalseでもtrueでもない場合はfalseを返す
        return response()->json(['isVerified' => 'false']);
    }

    /**
     * アップロードのための認証を行う
     */
    public function checkUploadAdmin(Request $request)
    {
        // セッションが生成できない場合はエラーを返す
        if ($request->session()->getId() === '') {
            return response()->json(['message' => 'セッションが生成できません'], 500);
        }

        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has(env('UPLOAD_SESSION_KEY')) === false) {
            $request->session()->put(env('UPLOAD_SESSION_KEY'), 'not_verified');
            return response()->json(['isVerified' => 'false', 'message' => 'セッションが生成できません']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get(env('UPLOAD_SESSION_KEY'));

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
