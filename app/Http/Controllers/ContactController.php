<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * 問い合わせフォームからの内容を確認し、ユーザ名が特定の文字列の場合は、アップロード画面に遷移
     */
    public function verify(Request $request)
    {
        // セッションにクエリがない場合(初回アクセス時)はnot_verifiedをセット
        if ($request->session()->has(env('UPLOAD_SESSION_KEY')) === false) {
            $request->session()->put(env('UPLOAD_SESSION_KEY'), 'not_verified');
            return response()->json([
                'isVerified' => 'false',
                'message' => 'セッションにクエリがなかったので、セッションにnot_verifiedをセットしました。',
                'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
            ]);
        }
        // ユーザ名が特定の文字列の場合は、アップロード画面を許可
        $name = $request->name;
        if ($name === env('UPLOAD_PERMISSION_USERNAME')) {
            $request->session()->put(env('UPLOAD_SESSION_KEY'), 'verified');
            return response()->json([
                'isVerified' => 'true',
                'message' => 'ユーザ名が特定の文字列のため、セッションにverifiedをセットしました。',
                'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
            ]);
        }

        return response()->json([
            'message' => '問い合わせ内容を受け付けました。',
            'isVerified' => 'false',
            'message' => 'ユーザ名が特定の文字列ではないため、セッションにnot_verifiedをセットしました。',
            'session' => $request->session()->get(env('UPLOAD_SESSION_KEY'))
        ]);
    }
}
