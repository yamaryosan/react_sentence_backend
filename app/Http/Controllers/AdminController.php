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
        if (env('SENTENCE_SESSION_KEY') === null) {
            return response()->json(['message' => 'SENTENCE_SESSION_KEYが設定されていません']);
        }

        // セッションからクエリを取得
        $query = $request->session()->get(env('SENTENCE_SESSION_KEY'));

        if ($query === null) {
            // セッションにnot_verifiedをセット
            $request->session()->put(env('SENTENCE_SESSION_KEY'), 'not_verified');
            return response()->json(['message' => 'クエリがnullだったので、セッションにnot_verifiedをセットしました。']);
        }

        // クエリがtrueの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json([
                'isVerified' => 'true',
                'message' => 'セッションにクエリがverifiedのため、trueを返しました。',
                'session' => $request->session()->get(env('SENTENCE_SESSION_KEY'))
            ]);
        }

        // クエリがfalseの場合はfalseを返す
        if ($query === 'not_verified') {
            // 再度、セッションにnot_verifiedをセット
            $request->session()->put(env('SENTENCE_SESSION_KEY'), 'not_verified');
            return response()->json([
                'isVerified' => 'false',
                'message' => 'セッションにクエリがnot_verifiedのため、falseを返しました。',
                'session' => $request->session()->get(env('SENTENCE_SESSION_KEY'))
            ]);
        }

        // クエリがfalseでもtrueでもない場合はエラーを返す
        return response()->json([
            'message' => 'クエリが不正です',
            'session' => $request->session()->get(env('SENTENCE_SESSION_KEY'))
        ]);
    }

    /**
     * アップロードのための認証を行う
     */
    public function checkUploadAdmin(Request $request)
    {
        if (env('UPLOAD_SESSION_KEY') === null) {
            return response()->json(['message' => 'UPLOAD_SESSION_KEYが設定されていません']);
        }
        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセットしfalseを返す
        if ($request->session()->has(env('UPLOAD_SESSION_KEY')) === false) {
            $request->session()->put(env('UPLOAD_SESSION_KEY'), 'not_verified');
            return response()->json([
                'isVerified' => 'false',
                'message' => 'セッションにクエリがなかったので、セッションにnot_verifiedをセットしました。',
                'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
            ]);
        }

        // セッションからクエリを取得
        $query = $request->session()->get(env('UPLOAD_SESSION_KEY'));

        if ($query === null) {
            return response()->json(['message' => 'クエリがnullです']);
        }

        // クエリがnot_verifiedの場合はfalseを返す
        if ($query === 'not_verified') {
            return response()->json([
                'isVerified' => 'false',
                'message' => 'セッションにクエリがnot_verifiedのため、falseを返しました。',
                'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
            ]);
        }

        // クエリがverifiedの場合はtrueを返す
        if ($query === 'verified') {
            return response()->json([
                'isVerified' => 'true',
                'message' => 'セッションにクエリがverifiedのため、trueを返しました。',
                'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
            ]);
        }

        // クエリがnot_verifiedでもverifiedでもない場合はエラーを返す
        return response()->json([
            'message' => 'クエリが不正です',
            'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
        ]);
    }
}
