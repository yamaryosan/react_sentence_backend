<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\UploadedFile;

use App\Jobs\SaveArticlesJob;

class UploadArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempDir;
    protected $categories;

    public function __construct($tempDir, $categories)
    {
        $this->tempDir = $tempDir;
        $this->categories = $categories;
    }

    public function handle(): void
    {
        // ファイルを1つずつ処理する
        $filePaths = glob($this->tempDir . '/*');
        \Log::info('UploadArticlesJob started');

        foreach ($filePaths as $index => $filePath) {
            $file = new UploadedFile($filePath, basename($filePath));

            // ファイルの拡張子が.mdの場合のみ処理を行う
            if ($file->getClientOriginalExtension() !== 'md') {
                continue;
            }

            // ファイルの内容を取得する
            $fileContent = file_get_contents($file);

            // ファイル名からタイトルを取得
            $filename = $file->getClientOriginalName();
            $title = str_replace('.md', '', $filename);

            // 各行に対して画像のパスを変換する
            $content = $this->convertImagePath($fileContent);

            // 記事を保存する
            SaveArticlesJob::dispatch($title, $content, $this->categories[$index]);

            // ファイルを削除
            unlink($filePath);
        }
    }

    /**
     * もとの.mdファイルのうち、../_resources/から始まる画像のパスをS3のURLに変換する
     * @param string $content 記事の内容
     * @return string 変換後の記事の内容
     */
    public function convertImagePath(string $content)
    {
        // 改行で文章を分割する
        $lines = explode("\r\n", $content);
        $convertImagePath = function ($line) {
            // 正規表現にマッチした文字列をコールバック関数で置換する
            return preg_replace_callback('/!\[.*?\]\((.*?)\)/', function ($matches) {
                $imagePath = $matches[1];
                if (strpos($imagePath, '../_resources/') === 0) {
                    $fileImagePath = Storage::disk('s3')->url('images/' . basename($imagePath));
                    return '![' . basename($imagePath) . '](' . $fileImagePath . ')';
                }
                return $matches[0]; // 画像のパスが変換対象でない場合はそのまま返す
            }, $line);
        };
        $convertedLines = array_map($convertImagePath, $lines);

        // 文字列に変換する
        return implode("\r\n", $convertedLines);
    }
}
