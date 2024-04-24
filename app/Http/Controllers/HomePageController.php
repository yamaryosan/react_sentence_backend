<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        $content = file_get_contents(public_path('home.txt'));
        return ["body" => $content];
    }
}
