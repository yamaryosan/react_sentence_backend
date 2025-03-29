<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Article;

class SaveArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;
    protected $content;
    protected $category;

    public function __construct($title, $content, $category)
    {
        $this->title = $title;
        $this->content = $content;
        $this->category = $category;
    }

    public function handle(): void
    {
        \Log::info('SaveArticlesJob started: ' . $this->title);
        $new_article = new Article();
        $new_article->title = $this->removeTitleNumber($this->title);
        $new_article->content = $this->content;
        $new_article->category = $this->category;
        $new_article->save();
    }

    public function removeTitleNumber(string $title): string
    {
        // タイトルの先頭の数字を削除
        return preg_replace('/^\d{6}_/', '', $title);
    }
}
