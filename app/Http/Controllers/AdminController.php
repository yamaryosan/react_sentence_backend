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
        if ($request->session()->has('query') === false) {
            $request->session()->put('query', 'true');
            return response()->json(['isVerified' => 'false']);
        }
        $query = $request->session()->get('query');

        if ($this->checkForAdminKeyword($query)) {
            return response()->json(['isVerified' => 'true']);
        } else {
            return response()->json(['isVerified' => 'false']);
        }
    }
    /**
     * 認証チェック用ロジック
     */
    private function checkForAdminKeyword(string $isVerified)
    {
        if ($isVerified === 'true') {
            return true;
        } else {
            return false;
        }
    }
}
