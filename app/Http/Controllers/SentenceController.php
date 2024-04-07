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

    public function upload(Request $request)
    {
        // テキストファイルのみ受け付ける
        $request->validate([
            'file' => 'required|mimes:txt'
        ]);

        $file = $request->file('file');
        $file->move(storage_path('app/uploads'), $file->getClientOriginalName());

        $sentences = file(storage_path('app/uploads/' . $file->getClientOriginalName()));
        $sentences = $this->split($sentences);

        foreach ($sentences as $sentence) {
            $sentence_model = new Sentence;
            $sentence_model->sentence = $sentence;
            $sentence_model->save();
        }

        return Sentence::all();
    }

    /**
     * 改行コードを処理する
     * 2つ以上の改行コードがある場合は、それを区切りとして分割する
     * 改行コードが1つの場合は、正しく1つの文章として扱う
     */
    private function split(array $sentences)
    {
        $result = [];
        $sentence = '';
        foreach ($sentences as $line) {
            if (trim($line) === '') {
                if ($sentence !== '') {
                    $result[] = $sentence;
                    $sentence = '';
                }
            } else {
                $sentence .= $line;
            }
        }
        if ($sentence !== '') {
            $result[] = $sentence;
        }
        return $result;
    }
}
