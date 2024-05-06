<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

use App\Models\Article;
use App\Models\ArticleImage;

class ArticleController extends Controller
{
    const SECRET_KEYWORD = 'magic';
    const SECRET_FALSE_KEYWORD = 'false';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 記事の一覧を取得する
        $articles = Article::all();
        // 記事の連想配列に画像のパスを追加する
        foreach ($articles as $article) {
            $article->imagePaths = [$this->getImagePaths($article->id)];
        }
        return $articles;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $article = new Article();
        $article->title = $request->title;
        $article->content = $request->content;
        $article->save();
        return $article;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = Article::find($id);
        $article->imagePaths = [$this->getImagePaths($id)];
        return $article;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $article = Article::find($id);
        $article->title = $request->title;
        $article->content = $request->content;
        $article->save();
        return $article;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::find($id);
        $article->delete();
        return $article;
    }

    /**
     * 記事を全削除する
     */
    public function truncate()
    {
        Article::truncate();
        if (Article::all()->isEmpty()) {
            return response()->json(['message' => '全ての記事を削除しました']);
        } else {
            return response()->json(['message' => '記事の削除に失敗しました'], 500);
        }
    }

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
        $lines = file(storage_path('app/uploads/' . $file->getClientOriginalName()));
        $line = implode("", $lines);

        $articles = $this->split($line);

        // 記事を保存する
        foreach ($articles as $article) {
            $new_article = new Article();
            $new_article->title = $this->getTitle($article);
            $new_article->content = $this->getContent($article);
            $new_article->save();
        }

        return Article::all();
    }

    /**
     * テキストファイルの内容を記事ごとに分割する
     * ファイルは「【タイトル】〜〜〜【本文】〜〜〜【タイトル】〜〜〜【本文】〜〜〜」の形式で保存されている
     * これを「【タイトル】〜〜〜【本文】」ごとに分割する
     */
    private function split(string $line)
    {
        $articles = [];
        $pattern = '/(【タイトル】[^【]+【本文】[^【]+)/u';
        if (preg_match_all($pattern, $line, $matches)) {
            $articles = $matches[0];
            // 文字列の最後に改行がある場合、削除する
            $articles = array_map(function ($article) {
                return rtrim($article);
            }, $articles);
        }
        return $articles;
    }

    /**
     * 記事からタイトルを取得する
     * 【タイトル】の後に続く文字列を取得する
     */
    private function getTitle(string $article)
    {
        $pattern = '/【タイトル】(\n+)(.+)/u';
        if (preg_match($pattern, $article, $matches)) {
            return $matches[2];
        }
        return '';
    }

    /**
     * 記事から本文を取得する
     */
    private function getContent(string $article)
    {
        $pattern = '/【本文】\s*(.*)/s';
        if (preg_match($pattern, $article, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * 検索
     */
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        // キーワードが特定の文字列の場合、セッションにクエリを保存し、認証合格とする
        if ($keyword === self::SECRET_KEYWORD) {
            $request->session()->put('query', 'true');
        }
        if ($keyword === self::SECRET_FALSE_KEYWORD) {
            $request->session()->put('query', 'false');
        }

        // 認証いかんにかかわらず、記事を検索する
        $articles = Article::where('title', 'like', "%$keyword%")->get();
        $articles = $articles->merge(Article::where('content', 'like', "%$keyword%")->get());
        return $articles;
    }

    /**
     * 記事IDに該当する画像のパスを取得
     * 該当する記事がない場合はデフォルトの画像を返す
     */
    public function getImagePaths(string $articleId)
    {
        $defaultImagePath = Storage::disk('public')->url('noimage.png');

        // 記事IDに該当する画像のパス群を返す
        $images = ArticleImage::where('article_id', $articleId)->get();
        if ($images->isNotEmpty()) {
            return $images;
        } else {
            return $defaultImagePath;
        }
    }
}
