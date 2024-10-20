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
        if ($request->session()->has('upload_session_key') === false) {
            $request->session()->put('upload_session_key', 'not_verified');
            return response()->json([
                'isVerified' => 'false',
            ]);
        }
        $name = $request->name;
        if ($name === 'spe') {
            $request->session()->put('upload_session_key', 'verified');
            return response()->json([
                'isVerified' => 'true',
                'message' => $name
            ]);
        }

        return response()->json([
            'message' => '問い合わせ内容を受け付けました。',
            'isVerified' => 'false',
            'message' => $name
        ]);
    }
}
