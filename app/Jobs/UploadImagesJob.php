<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;
use App\Jobs\SaveImagesJob;

class UploadImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imageDir;

    /**
     * Create a new job instance.
     */
    public function __construct($imageDir)
    {
        $this->imageDir = $imageDir;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('UploadImagesJob started');
        // imagesディレクトリがS3に存在しない場合は作成する
        if (!Storage::disk('s3')->exists('images/')) {
            Storage::disk('s3')->put('images/.gitkeep', '');
        }

        $imagePaths = glob($this->imageDir . '/*');
        foreach ($imagePaths as $imagePath) {
            SaveImagesJob::dispatch($imagePath);
        }

        \Log::info('UploadImagesJob finished');
    }
}
