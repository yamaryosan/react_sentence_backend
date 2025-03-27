<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Jobs\SaveSentencesJob;

use Throwable;

use Illuminate\Support\Facades\Log;

class UploadSentencesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filename;

    /**
     * Create a new job instance.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('UploadSentencesJob started: ' . $this->filename);
        try {
            \Log::info('[UploadSentencesJob] Reading file: ' . $this->filename);
            $sentences = file(storage_path('app/uploads/' . $this->filename));
        } catch (Throwable $e) {
            \Log::error('[UploadSentencesJob] Failed to read file: ' . $this->filename);
            return;
        }

        try {
            \Log::info('[UploadSentencesJob] Splitting sentences: ' . $this->filename);
            $sentences = $this->split($sentences);
        } catch (Throwable $e) {
            \Log::error('[UploadSentencesJob] Failed to split sentences: ' . $this->filename);
            return;
        }

        // ひとつも文章がない場合は、その旨をログに出力
        try {
            \Log::info('[UploadSentencesJob] Counting sentences: ' . $this->filename);
            if (count($sentences) === 0) {
                \Log::error('[UploadSentencesJob] No sentences found in file: ' . $this->filename);
                return;
            }
        } catch (Throwable $e) {
            \Log::error('[UploadSentencesJob] Failed to count sentences: ' . $this->filename);
            return;
        }

        // データベースに保存
        foreach ($sentences as $sentence) {
            SaveSentencesJob::dispatch($sentence);
        }
    }

    /**
     * 改行コードを処理する
     * 2つ以上の改行コードがある場合は、それを区切りとして分割する
     * 改行コードが1つの場合は、1つの文章として扱う
     */
    private function split(array $sentences)
    {
        $result = [];
        $sentence = '';
        foreach ($sentences as $line) {
            if (trim($line) === '') {
                if ($sentence !== '') {
                    $result[] = $sentence;
                    $sentence = '';
                }
            } else {
                $sentence .= $line;
            }
        }
        if ($sentence !== '') {
            $result[] = $sentence;
        }
        return $result;
    }
}
