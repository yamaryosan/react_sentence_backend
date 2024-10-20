<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sentence;

use Illuminate\Support\Facades\Validator;

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
        $sentence = Sentence::find($id);
        if ($sentence === null) {
            return response()->json(['message' => '文章が見つかりません'], 404);
        }
        return $sentence;
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

    public function truncate()
    {
        Sentence::truncate();
        return ['message' => '全ての文章を削除しました'];
    }

    /**
     * 文章アップロード
     * ファイル拡張子はあえてチェックしない(txtファイルなのに、application/x-dosexecと判定されるファイルがあるため)
     */
    public function upload(Request $request)
    {
        // ファイルアップロードのバリデーション
        $validator = Validator::make($request->all(), [
            'file' => 'required|max:20480'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

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

    /**
     * 検索
     */
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        // キーワードが特定の文字列の場合、文章の検索を有効化または無効化する
        if ($keyword === env('LOCK_KEYWORD')) {
            $request->session()->put(env('SENTENCE_SESSION_KEY'), 'not_verified');
            return [];
        } else if ($keyword === env('UNLOCK_KEYWORD')) {
            $request->session()->put(env('SENTENCE_SESSION_KEY'), 'verified');
            return [];
        }

        // NGワードが含まれている場合は、検索しない
        $ngWords = explode(',', env('NG_WORDS'));
        if (in_array($keyword, $ngWords)) {
            return [];
        }

        // 認証いかんにかかわらず、記事を検索する
        $sentences = Sentence::where('sentence', 'like', "%$keyword%")->get();

        // 検索結果からNGワードを含む文章を除外
        $sentences = $sentences->filter(function ($sentence) {
            $ngWords = explode(',', env('NG_WORDS'));
            foreach ($ngWords as $ngWord) {
                if (strpos($sentence->sentence, $ngWord) !== false) {
                    return false;
                }
            }
            return true;
        });

        return $sentences->values()->all();
    }
}
