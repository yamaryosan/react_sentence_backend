<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Http\UploadedFile;

use Tests\TestCase;

class ArticleImageTest extends TestCase
{
    use RefreshDatabase;
    /**
     * 画像アップロードテスト(ダミーの画像データを送信する)
     */
    public function testUpload()
    {
        $response = $this->postJson('/api/articleImages/upload', [
            'images' => [
                UploadedFile::fake()->image('test_image.jpg'),
                UploadedFile::fake()->image('test_image2.jpg'),
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => '画像をアップロードしました']);
    }

    /**
     * 画像を全削除
     */
    public function testTruncate()
    {
        $response = $this->deleteJson('/api/articleImages/deleteAll');
        $response->assertStatus(200);
        $response->assertJson(['message' => '全ての画像を削除しました']);
    }
}
