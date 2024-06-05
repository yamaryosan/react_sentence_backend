<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Http\UploadedFile;

use App\Models\Sentence;

class SentenceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * 文章一覧取得テスト
     */
    public function testGetAllSentences()
    {
        $response = $this->get('api/sentences');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'sentence',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    /**
     * 特定IDの文章取得テスト
     */
    public function testGetSentence()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();
        $response = $this->get('api/sentences/' . $sentence->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sentence' => 'This is a test sentence.'
        ]);
    }

    /**
     * 存在しないIDの文章取得テスト
     */
    public function testGetSentenceNotFound()
    {
        $response = $this->get('api/sentences/' . 0);

        $response->assertStatus(404);
        $response->assertJson([
            'message' => '文章が見つかりません'
        ]);
    }

    /**
     * 文章作成テスト
     */
    public function testCreateSentence()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();

        $response = $this->get('api/sentences');
        $response->assertStatus(200);

        // レコードが作成されているか確認
        $response->assertJsonFragment([
            'sentence' => 'This is a test sentence.'
        ]);
    }

    /**
     * 文章更新テスト
     */
    public function testUpdateSentence()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();

        $response = $this->put('api/sentences/' . $sentence->id, [
            'sentence' => 'This is an updated test sentence.'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sentence' => 'This is an updated test sentence.'
        ]);
    }

    /**
     * 文章削除テスト
     */
    public function testDeleteSentence()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();

        $response = $this->delete('api/sentences/' . $sentence->id);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sentence' => 'This is a test sentence.'
        ]);
    }

    /**
     * ファイルアップロードテスト
     */
    public function testUpload()
    {
        // テスト用のアップロードファイルを作成
        $fileContent = "This is a test sentence.";
        $filename = 'test.txt';
        $file = UploadedFile::fake()->createWithContent($filename, $fileContent);
        // ファイルアップロード
        $response = $this->json('POST', 'api/sentences/upload', [
            'file' => $file,
        ]);
        // アップロード成功を確認
        $response->assertStatus(200);

        $response->assertJsonFragment([
            'sentence' => 'This is a test sentence.'
        ]);
    }

    /**
     * ファイルアップロードバリデーションエラーテスト（ファイルがない場合）
     */
    public function testUploadWithValidationError()
    {
        // リクエストデータを作成（ファイルがない場合）
        $response = $this->json('POST', 'api/sentences/upload', [
            'file' => null,
        ]);
        $response->assertStatus(400);
        // エラーメッセージを確認
        $response->assertJson([
            'error' => [
                'file' => [
                    'The file field is required.'
                ]
            ],
        ]);
    }

    /**
     * ファイルアップロードバリデーションエラーテスト（ファイルサイズオーバー）
     */
    public function testUploadWithValidationErrorOverSize()
    {
        // テスト用のアップロードファイルを作成
        $file = UploadedFile::fake()->create('test.txt', 20481);
        // ファイルアップロード
        $response = $this->json('POST', 'api/sentences/upload', [
            'file' => $file,
        ]);
        $response->assertStatus(400);
    }

    /**
     * 文章検索テスト
     */
    public function testSearchSentence()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();

        $response = $this->get('sentences/search?keyword=test');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'sentence' => 'This is a test sentence.'
        ]);
    }

    /**
     * 文章検索テスト（キーワードが空文字）
     */
    public function testSearchSentenceWithEmptyKeyword()
    {
        $response = $this->get('sentences/search?keyword=');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * 文章検索テスト（キーワードが存在しない）
     */
    public function testSearchSentenceWithNoResult()
    {
        $sentence = new Sentence();
        $sentence->sentence = 'This is a test sentence.';
        $sentence->save();

        $response = $this->get('sentences/search?keyword=notfound');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * 文章検索テスト（NGワードを含む場合、検索結果を表示しない）
     */
    public function testSearchSentenceWithNgWord()
    {
        $sentence = new Sentence();
        $oneOfNgWords = explode(',', env('NG_WORDS'))[0];
        $sentence->sentence = $oneOfNgWords;
        $sentence->save();

        $response = $this->get('sentences/search?keyword=' . $oneOfNgWords);

        $response->assertStatus(200);
        $response->assertJson([]);
    }
}
