<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sentence;

use Illuminate\Support\Facades\Validator;

class SentenceController extends Controller
{
    /**
     * 文章一覧を取得
     */
    public function index(Request $request)
    {
        $page = max(1, $request->page ?? 1);
        $pageSize = max(1, min(100, $request->pageSize ?? 10));
        $offset = ($page - 1) * $pageSize;

        return Sentence::limit($pageSize)->offset($offset)->get();
    }

    /**
     * 文章を保存
     */
    public function store(Request $request)
    {
        $sentence = new Sentence();
        $sentence->sentence = $request->sentence;
        $sentence->save();
        return $sentence;
    }

    /**
     * 文章を取得
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
     * 文章を更新
     */
    public function update(Request $request, string $id)
    {
        $sentence = Sentence::find($id);
        $sentence->sentence = $request->sentence;
        $sentence->save();
        return $sentence;
    }

    /**
     * 文章を削除
     */
    public function destroy(string $id)
    {
        $sentence = Sentence::find($id);
        $sentence->delete();
        return $sentence;
    }

    /**
     * 文章を全て削除
     */
    public function truncate()
    {
        Sentence::truncate();
        return ['message' => '全ての文章を削除しました'];
    }

    /**
     * 文章をアップロード
     * ファイル拡張子はあえてチェックしない
     * txtファイルなのに、application/x-dosexecと判定されるファイルがあるため
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
     * 改行コードが1つの場合は、1つの文章として扱う
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
        $page = max(1, $request->page ?? 1);
        $pageSize = max(1, min(100, $request->pageSize ?? 10)); // ページサイズは100件まで
        $offset = ($page - 1) * $pageSize;

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

        $totalSentences = $sentences->count();

        return response()->json([
            'totalCount' => $totalSentences,
            'sentences' => $sentences->slice($offset, $pageSize)->values()
        ]);
    }
}
