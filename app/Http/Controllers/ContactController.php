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
        // ユーザ名が特定の文字列の場合は、アップロード画面を許可
        $name = $request->name;
        if ($name === env('UPLOAD_PERMISSION_USERNAME')) {
            return response()->json([
                'isVerified' => true
            ]);
        }

        return response()->json([
            'isVerified' => false
        ]);
    }
}
