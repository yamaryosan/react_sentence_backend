<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Sentence;
use Throwable;

use Illuminate\Support\Facades\Log;

class SaveSentencesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sentence;

    /**
     * Create a new job instance.
     */
    public function __construct($sentence)
    {
        $this->sentence = $sentence;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('SaveSentencesJob started: ' . $this->sentence);
        try {
            $model = new Sentence;
            $model->sentence = $this->sentence;
            $model->save();
            \Log::info('Saved sentence: ' . $this->sentence);
        } catch (Throwable $e) {
            \Log::error('Failed to save sentence: ' . $this->sentence);
        }
    }
}
