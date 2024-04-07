<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sentence;

class SentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Sentence::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sentence = new Sentence();
        $sentence->sentence = $request->sentence;
        $sentence->save();
        return $sentence;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Sentence::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $sentence = Sentence::find($id);
        $sentence->sentence = $request->sentence;
        $sentence->save();
        return $sentence;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sentence = Sentence::find($id);
        $sentence->delete();
        return $sentence;
    }
}
