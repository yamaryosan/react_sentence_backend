<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * 管理者認証画面からの内容を確認し、パスワードが特定の文字列の場合は、アップロード画面に遷移
     */
    public function verify(Request $request)
    {
        // パスワードが特定の文字列の場合は、アップロード画面を許可
        $password = $request->password;
        if ($password === env('UPLOAD_PERMISSION_USERNAME')) {
            return response()->json([
                'isVerified' => true
            ]);
        }

        return response()->json([
            'isVerified' => false
        ]);
    }
}
