<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * 問い合わせフォームからの内容を受け取り、データベースに保存
     */
    public function store(Request $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '入力内容に誤りがあります',
                'errors' => $validator->errors()
            ], 400);
        }

        // 特別な内容が含まれている場合は特別なレスポンスを返す
        if ($this->isMessageSpecial($request)) {
            return response()->json([
                'secret' => env('KYE_TO_UPLOAD_ARTICLE')
            ]);
        }

        // 問い合わせ内容をデータベースに保存
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->save();

        return response()->json([
            'message' => '問い合わせ内容を受け付けました。'
        ]);
    }

    /**
     * 問い合わせ内容に特別な内容が含まれるかチェック
     */
    public function isMessageSpecial(Request $request)
    {
        $message = $request->message;

        if (strpos($message, env('SPECIAL_MESSAGE')) !== false) {
            return true;
        }
        return false;
    }
}
