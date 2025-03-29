<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sentence;

use Illuminate\Support\Facades\Validator;

use App\Jobs\UploadSentencesJob;
use App\Jobs\TestJob;
use Throwable;

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
            return response()->json(['message' => 'Sentence not found'], 404);
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
     * ファイル拡張子はチェックしない
     * txtファイルなのに、application/x-dosexecと判定されるファイルがあるため
     */
    public function upload(Request $request)
    {
        // ファイルアップロードのバリデーション
        $validator = Validator::make($request->all(), [
            'file' => 'required|max:40960'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // ファイルを保存
        $file = $request->file('file');
        $file->move(storage_path('app/uploads'), $file->getClientOriginalName());

        \Log::info('File moved to: ' . storage_path('app/uploads/' . $file->getClientOriginalName()));


        // 内容DB保存は非同期で処理する
        try {
            UploadSentencesJob::dispatch($file->getClientOriginalName());
        } catch (Throwable $e) {
            \Log::error('Failed to dispatch UploadSentencesJob: ' . $e->getMessage());
        }

        return ['message' => 'Uploading sentences...'];
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
        $totalSentences = $sentences->count();

        return response()->json([
            'totalCount' => $totalSentences,
            'sentences' => $sentences->slice($offset, $pageSize)->values()
        ]);
    }
}
