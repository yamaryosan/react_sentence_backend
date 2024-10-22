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
        // リクエストヘッダーとCookieの詳細なログ
        \Log::info('Request Details', [
            'headers' => collect($request->headers->all())
                ->map(fn($item) => is_array($item) ? $item[0] : $item)
                ->toArray(),
            'cookies' => $request->cookies->all(),
            'session_cookie_name' => config('session.cookie'),
            'has_session_cookie' => $request->hasCookie(config('session.cookie')),
        ]);
        // セッション設定の確認
        \Log::info('Session Config', [
            'driver' => config('session.driver'),
            'domain' => config('session.domain'),
            'secure' => config('session.secure'),
            'same_site' => config('session.same_site'),
            'path' => config('session.path'),
            'lifetime' => config('session.lifetime'),
        ]);

        $sessionKey = env('SENTENCE_SESSION_KEY');

        if ($sessionKey === null) {
            return response()->json(['message' => 'SENTENCE_SESSION_KEYが設定されていません']);
        }

        // セッションの状態をログに記録
        \Log::info('Session State:', [
            'session_id' => $request->session()->getId(),
            'all_session_data' => $request->session()->all(),
            'current_query' => $request->session()->get($sessionKey)
        ]);

        $query = $request->session()->get($sessionKey);

        if ($query === null) {
            $request->session()->put($sessionKey, 'not_verified');

            // 保存の確認
            \Log::info('After null check:', [
                'new_value' => $request->session()->get($sessionKey)
            ]);
            return response()->json([
                'message' => 'クエリがnullだったので、セッションにnot_verifiedをセットしました。',
                'debug' => [
                    'session_id' => $request->session()->getId(),
                    'new_session_value' => $request->session()->get($sessionKey)
                ]
            ]);
        }

        if ($query === 'verified') {
            return response()->json([
                'isVerified' => 'true',
                'message' => 'セッションにクエリがverifiedのため、trueを返しました。',
                'session' => $query,
                'debug' => [
                    'session_id' => $request->session()->getId()
                ]
            ]);
        }

        if ($query === 'not_verified') {
            $request->session()->put($sessionKey, 'not_verified');
            $request->session()->save();  // 明示的な保存

            return response()->json([
                'isVerified' => 'false',
                'message' => 'セッションにクエリがnot_verifiedのため、falseを返しました。',
                'session' => $query,
                'debug' => [
                    'session_id' => $request->session()->getId(),
                    'verified_status' => $request->session()->get($sessionKey)
                ]
            ]);
        }

        // 不正な値の場合
        return response()->json([
            'message' => 'クエリが不正です',
            'session' => $query,
            'debug' => [
                'current_value' => $query,
                'session_id' => $request->session()->getId(),
                'all_session_data' => $request->session()->all()
            ]
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
