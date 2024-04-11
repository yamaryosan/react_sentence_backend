<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Article::all();
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
        return Article::find($id);
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

    public function upload(Request $request)
    {
        // テキストファイルのみ受け付ける
        $request->validate([
            'file' => 'required|mimes:txt',
        ]);

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
        $pattern = '/【本文】(\n+)(.+)/u';
        if (preg_match($pattern, $article, $matches)) {
            return $matches[2];
        }
        return '';
    }
}
