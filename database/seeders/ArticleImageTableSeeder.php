<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\ArticleImage;

class ArticleImageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $new_article_image = new ArticleImage();
        $new_article_image->name = 'test_image_name';
        $new_article_image->path = 'test_image_path';
        $new_article_image->article_id = 1;
        $new_article_image->save();
    }
}
