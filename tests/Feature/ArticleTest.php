<?php
namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Article;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ArticleController;

use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 記事未作成時の記事一覧取得テスト
     */
    public function testGetArticlesWhenNoArticle(): void
    {
        $response = $this->getJson('/api/articles');
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * 記事作成時の記事一覧取得テスト
     */
    public function testGetArticlesWhenArticleExists(): void
    {
        $article = new Article();
        $article->title = 'test title';
        $article->content = 'test content';
        $article->save();

        $response = $this->getJson('/api/articles');
        $response->assertStatus(200);
        $response->assertJson([
            [
                'title' => $article->title,
                'content' => $article->content,
            ],
        ]);
    }

    /**
     * 特定IDの記事取得テスト
     */
    public function testGetArticle(): void
    {
        $article = new Article();
        $article->title = 'test title';
        $article->content = 'test content';
        $article->save();

        $response = $this->getJson("/api/articles/{$article->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'title' => $article->title,
            'content' => $article->content,
        ]);
    }

    /**
     * 不在IDの記事取得テスト
     */
    public function testGetArticleWhenArticleDoesNotExist(): void
    {
        $response = $this->getJson('/api/articles/0');
        $response->assertStatus(404);
        $response->assertJson(['message' => '記事が見つかりません']);
    }

    /**
     * 記事更新テスト
     */
    public function testUpdateArticle(): void
    {
        $article = new Article();
        $article->title = 'test title';
        $article->content = 'test content';
        $article->save();

        $response = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'updated title',
            'content' => 'updated content',
        ]);
        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'updated title',
            'content' => 'updated content',
        ]);
    }

    /**
     * 記事削除テスト
     */
    public function testDeleteArticle(): void
    {
        $article = new Article();
        $article->title = 'test title';
        $article->content = 'test content';
        $article->save();

        $response = $this->deleteJson("/api/articles/{$article->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'title' => $article->title,
            'content' => $article->content,
        ]);
        // 削除されていることを確認
        $this->assertNull(Article::find($article->id));
    }

    /**
     * 記事全削除テスト
     */
    public function testDeleteAllArticles(): void
    {
        $article1 = new Article();
        $article1->title = 'test title1';
        $article1->content = 'test content1';
        $article1->save();

        $article2 = new Article();
        $article2->title = 'test title2';
        $article2->content = 'test content2';
        $article2->save();

        $response = $this->deleteJson('/api/articles/deleteAll');
        $response->assertStatus(200);
        // 削除されていることを確認
        $this->assertEmpty(Article::all());
    }

    /**
     * ファイル用初期化
     */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * ファイルアップロードテスト
     */
    public function test_upload()
    {
        // テスト用のアップロードファイルを作成
        $fileContent = "![test image](path/to/image.jpg)\nSample content";
        $filename = 'test.md';
        $file = UploadedFile::fake()->createWithContent($filename, $fileContent);
        // ファイルアップロード
        $response = $this->json('POST', 'api/articles/upload', [
            'files' => [$file],
        ]);
        // アップロード成功を確認
        $response->assertStatus(200);

        // データベースに記事が保存されていることを確認
        $this->assertDatabaseHas('articles', [
            'title' => 'test',
            'content' => "![test image](path/to/image.jpg)\nSample content",
        ]);

        // データベースに画像のパスが保存されていることを確認
        $article = Article::first();
        $this->assertDatabaseHas('article_images', [
            'article_id' => $article->id,
            'name' => 'image.jpg',
            'path' => 'path/to/image.jpg',
        ]);
    }

    /**
     * ファイルアップロードバリデーションエラーテスト
     */
    public function test_upload_with_validation_error()
    {
        // リクエストデータを作成（ファイルがない場合）
        $response = $this->json('POST', 'api/articles/upload', [
            'files' => [],
        ]);
        $response->assertStatus(400);
        // エラーメッセージを確認
        $response->assertJson([
            'error' => 'ファイルが選択されていません',
            ],
        );
    }

    /**
     * 記事検索テスト
     */
    public function testSearchArticles(): void
    {
        $article1 = new Article();
        $article1->title = 'test title1';
        $article1->content = 'test content1';
        $article1->save();
        $article2 = new Article();
        $article2->title = 'test title2';
        $article2->content = 'test content2';
        $article2->save();

        $response = $this->getJson('/articles/search?keyword=test');
        $response->assertStatus(200);
        $response->assertJson([
            [
                'title' => $article1->title,
                'content' => $article1->content,
            ],
            [
                'title' => $article2->title,
                'content' => $article2->content,
            ],
        ]);
    }
    /**
     * 記事検索テスト（キーワードが含まれない場合）
     */
    public function testSearchArticlesWhenKeywordDoesNotExist(): void
    {
        $article1 = new Article();
        $article1->title = 'test title1';
        $article1->content = 'test content1';
        $article1->save();
        $article2 = new Article();
        $article2->title = 'test title2';
        $article2->content = 'test content2';
        $article2->save();

        $response = $this->getJson('/articles/search?keyword=not_exist');
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * 画像パス取得テスト（画像パスが含まれない場合）
     */
    public function testGetImagePaths(): void
    {
        $article = new Article();
        $article->title = 'test title';
        $article->content = "Sample content";
        $article->save();

        $controller = new ArticleController();
        $imagePath = $controller->getImagePaths($article->content)[0];
        $defaultPath = 'storage/noimage.png';
        $this->assertStringContainsString($defaultPath, $imagePath);
    }
}
