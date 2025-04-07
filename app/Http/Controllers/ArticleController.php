<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Article;
use App\Jobs\UploadArticlesJob;

class ArticleController extends Controller
{
    private $maxPageSize = 500;
    private $defaultPageSize = 50;
    /**
     * 記事の一覧を取得する
     * 件数およびページ数も指定可能
     */
    public function index(Request $request)
    {
        // ページ数およびページサイズを取得
        $page = max(1, $request->page ?? 1);
        $pageSize = max(1, min($this->maxPageSize, $request->pageSize ?? $this->defaultPageSize));
        $offset = ($page - 1) * $pageSize;

        // 記事の総数を取得
        $totalArticles = Article::count();

        // 記事を取得
        $articles = Article::limit($pageSize)->offset($offset)->get();

        return response()->json([
            'totalCount' => $totalArticles,
            'articles' => $articles
        ]);
    }

    /**
     * 記事を保存する
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
     * 記事を取得する
     */
    public function show(string $id)
    {
        $article = Article::find($id);
        if (empty($article)) {
            return response()->json(['message' => '記事が見つかりません'], 404);
        }
        return $article;
    }

    /**
     * 記事を更新する
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
     * 記事を削除する
     */
    public function destroy(string $id)
    {
        $article = Article::find($id);
        $article->delete();
        return $article;
    }

    /**
     * 記事をカテゴリーごとに削除する
     */
    public function deleteByCategory(Request $request)
    {
        if (empty($request->categories)) {
            return response()->json(['message' => 'カテゴリーが選択されていません'], 400);
        }
        $categories = $request->categories;
        Article::whereIn('category', $categories)->delete();
        return response()->json(['message' => '記事を削除しました']);
    }

    /**
     * 記事を全削除する
     */
    public function truncate()
    {
        // ファイルの削除
        $files = glob(storage_path('app/uploads/*'));
        foreach ($files as $file) {
            unlink($file);
        }

        // truncateだと外部キー制約がある場合エラーになるので、全削除する
        Article::query()->delete();
        if (Article::all()->isEmpty()) {
            return response()->json(['message' => '全ての記事を削除しました']);
        } else {
            return response()->json(['message' => '記事の削除に失敗しました'], 500);
        }
    }

    /**
     * 記事をアップロードする
     */
    public function upload(Request $request)
    {
        // ファイルアップロードのバリデーション
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|max:2048000'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $files = $request->file('files');

        // ファイルが選択されていない場合はエラー
        if (empty($files)) {
            return response()->json(['error' => 'ファイルが選択されていません'], 400);
        }

        $categories = $request->input('categories');

        // カテゴリが指定されていない場合はエラー
        if (empty($categories)){
            return response()->json(['error' => 'カテゴリが選択されていません'], 400);
        }

        // ファイル群を一時保存
        $tempDir = storage_path('app/temp');
        //以前のapp/temp内のファイルを削除
        $previousFiles = glob($tempDir . '/*');
        foreach ($previousFiles as $previousFile) {
            unlink($previousFile);
        }
        // app/tempのディレクトリを削除
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }

        // app/tempのディレクトリを作成
        mkdir($tempDir);

        // ファイルを一時保存
        foreach ($files as $index => $file) {
            $originalName = $file->getClientOriginalName();
            $newName = sprintf('%06d', $index) . '_' . $originalName;
            $file->move($tempDir, $newName);
        }

        // ファイル群をキューで処理
        UploadArticlesJob::dispatch($tempDir, $categories);

        return response()->json(['message' => count($files) . '個のファイルをアップロードしました']);
    }

    /**
     * 記事を検索する
     */
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $page = max(1, $request->page ?? 1);
        $pageSize = max(1, min(100, $request->pageSize ?? 10)); // ページサイズは100件まで
        $offset = ($page - 1) * $pageSize;

        // キーワードに該当する記事の総数を取得(重複は排除)
        $totalArticles = Article::where('title', 'like', "%$keyword%")->get();
        $totalArticles = $totalArticles->merge(Article::where('content', 'like', "%$keyword%")->get());
        $totalArticles = $totalArticles->unique('id')->count();

        // キーワードに該当する記事を取得(合計がページサイズになるようにする)
        $articles = Article::where('title', 'like', "%$keyword%")->get();
        $articles = $articles->merge(Article::where('content', 'like', "%$keyword%")->get());
        $articles = $articles->unique('id')->slice($offset, $pageSize); // ページサイズ分の記事を取得($articlesはCollection)
        $articles = $articles->values(); // $articlesを配列に変換する

        return response()->json([
            'totalCount' => $totalArticles,
            'articles' => $articles
        ]);
    }

    /**
     * 記事のカテゴリーとその記事の数を取得
     */
    public function getCategories()
    {
        $categoriesObjectArray = Article::select('category')->distinct()->get();
        // カテゴリーのみを取り出す
        $categories = [];
        foreach ($categoriesObjectArray as $category) {
            $categories[] = $category->category;
        }

        $categoriesWithCount = [];
        foreach ($categories as $category) {
            $categoriesWithCount[] = [
                'category' => $category,
                'count' => Article::where('category', $category)->count()
            ];
        }

        return $categoriesWithCount;
    }

    /**
     * カテゴリーに該当する記事を取得
     * 件数も指定可能
     */
    public function getArticlesByCategory(Request $request)
    {
        $category = $request->category;
        $page = max(1, $request->page ?? 1);
        $pageSize = max(1, min($this->maxPageSize, $request->pageSize ?? $this->defaultPageSize));
        $offset = ($page - 1) * $pageSize;

        // カテゴリーに該当する記事の総数を取得
        $totalArticles = Article::where('category', $category)->count();

        // カテゴリーに該当する記事を取得
        $articles = Article::where('category', $category)->limit($pageSize)->offset($offset)->get();

        return response()->json([
            'totalCount' => $totalArticles,
            'articles' => $articles
        ]);
    }

    /**
     * 記事をランダムに取得
     */
    public function getRandom(Request $request)
    {
        $count = 5;
        $articles = Article::inRandomOrder()->limit($count)->get();
        return $articles;
    }
}
