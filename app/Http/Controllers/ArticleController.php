<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

use App\Models\Article;
use App\Models\ArticleImage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 記事の一覧を取得する
        $articles = Article::all();
        // 記事の連想配列に画像のパスを追加する
        foreach ($articles as $article) {
            $article->imagePaths = $this->getImagePaths($article->id);
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
        if (empty($article)) {
            return response()->json(['message' => '記事が見つかりません'], 404);
        }
        $article->imagePaths = $this->getImagePaths($id);
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
        $categories = $request->input('categories');

        // カテゴリが指定されていない場合はエラー
        if (empty($categories)){
            return response()->json(['error' => 'カテゴリが選択されていません'], 400);
        }

        if (empty($files)) {
            return response()->json(['error' => 'ファイルが選択されていません'], 400);
        }

        // カテゴリーの割り当てに備える
        $filesWithCategories = [];
        $filesWithCategories = [];
        foreach ($files as $index => $file) {
            $filesWithCategories[] = [
                'file' => $file,
                'category' => $categories[$index] // 対応するカテゴリーを割り当てる
            ];
        }

        // ファイルの拡張子がmdでない場合は配列から削除
        $filesWithCategories = array_filter($filesWithCategories, function ($fileWithCategory) {
            return $fileWithCategory['file']->getClientOriginalExtension() === 'md';
        });

        // ファイルをアップロード
        foreach ($filesWithCategories as $fileWithCategory) {
            $file = $fileWithCategory['file'];
            $category = $fileWithCategory['category'];

            $file->move(storage_path('app/uploads'), $file->getClientOriginalName());
            $lines = file(storage_path('app/uploads/' . $file->getClientOriginalName()));

            // ファイル名からタイトルを取得
            $filename = $file->getClientOriginalName();
            $title = str_replace('.md', '', $filename);

            // 各行に対して画像のパスを変換
            $convertedLines = $this->convertImagePath($lines);

            // 文字列に変換
            $content = implode($convertedLines);

            // 画像のパスを取得
            $imagePaths = [];
            foreach ($convertedLines as $line) {
                preg_match_all('/!\[.*?\]\((.*?)\)/', $line, $matches);
                foreach ($matches[1] as $match) {
                    $imagePaths[] = $match;
                }
            }

            // 記事を保存
            $new_article = new Article();
            $new_article->title = $title;
            $new_article->content = $content;
            $new_article->category = $category;
            $new_article->save();

            // 画像を保存
            foreach ($imagePaths as $imagePath) {
                $articleImage = new ArticleImage();
                $articleImage->name = basename($imagePath);
                $articleImage->path = $imagePath;
                $articleImage->article_id = $new_article->id;
                $articleImage->save();
            }
        }
        $count = count($filesWithCategories);
        return response()->json(['message' => $count . '個のファイルをアップロードしました']);
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

        // キーワードに該当する記事を取得
        $articles = Article::where('title', 'like', "%$keyword%")->get();
        $articles = $articles->merge(Article::where('content', 'like', "%$keyword%")->get());

        // 記事の連想配列に画像のパスを追加する
        foreach ($articles as $article) {
            $article->imagePaths = $this->getImagePaths($article->id);
        }

        return $articles;
    }

    /**
     * ../_resources形式の画像のパスを変換し、/images/xxx.pngの形式にする関数
     * フロントエンドで画像を表示するために使用
     * ![text](../_resources/example.png) -> ![example](/images/example.png)
     */
    public function convertImagePath(array $lines)
    {
        $convertImagePath = function ($line) {
            // preg_replace_callbackは、正規表現にマッチした文字列をコールバック関数で置換する関数
            return preg_replace_callback('/!\[.*?\]\((.*?)\)/', function ($matches) {
                $imagePath = $matches[1]; // 画像のパス(../_resources/example.png)
                if (strpos($imagePath, '../_resources/') === 0) {
                    $fileImagePath = Storage::disk('public')->url('/images/' . basename($imagePath));
                    return '![' . basename($imagePath) . '](' . $fileImagePath . ')';
                }
                return $matches[0]; // 画像のパスが変換対象でない場合はそのまま返す
            }, $line);
        };

        return array_map($convertImagePath, $lines);
    }

    /**
     * 記事IDに該当する画像のパス群を取得
     * 該当する記事がない場合はデフォルトの画像を返す
     */
    public function getImagePaths(string $articleId): array
    {
        $defaultImagePath = [Storage::disk('public')->url('noimage.png')];

        // デフォルトの画像にアクセスできるか確認
        if (!Storage::disk('public')->exists('noimage.png')) {
            return response()->json(['message' => 'デフォルトの画像が存在しません'], 500);
        }

        // デフォルトの画像がない場合はエラー
        if (empty($defaultImagePath)) {
            return response()->json(['message' => 'デフォルトの画像が設定されていません'], 500);
        }

        // 記事IDに該当する画像のパス群を返す (該当する記事がない場合はデフォルトの画像を返す)
        $images = ArticleImage::where('article_id', $articleId)->get()->pluck('path')->toArray();
        if (count($images) > 0) {
            return $images;
        } else {
            return $defaultImagePath;
        }
    }

    /**
     * 記事のカテゴリーを取得
     */
    public function getCategories()
    {
        $categoriesObjectArray = Article::select('category')->distinct()->get();
        // カテゴリーのみを取り出す
        $categories = [];
        foreach ($categoriesObjectArray as $category) {
            $categories[] = $category->category;
        }

        return $categories;
    }

    /**
     * カテゴリーに該当する記事を取得
     */
    public function getArticlesByCategory(Request $request)
    {
        $category = $request->category;
        $articles = Article::where('category', $category)->get();
        // 記事の連想配列に画像のパスを追加する
        foreach ($articles as $article) {
            $article->imagePaths = $this->getImagePaths($article->id);
        }
        return $articles;
    }

    /**
     * 記事をランダムに取得
     */
    public function getRandom(Request $request)
    {
        $count = 10;
        $articles = Article::inRandomOrder()->limit($count)->get();
        // 記事の連想配列に画像のパスを追加する
        foreach ($articles as $article) {
            $article->imagePaths = $this->getImagePaths($article->id);
        }
        return $articles;
    }
}
