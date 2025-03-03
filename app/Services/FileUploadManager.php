<?php

namespace App\Services;

use App\Services\EsmSessionManager;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\FilesystemException;

class FileUploadManager
{
    protected $disk;
    protected $resourcesDir;
    protected $esmSessionManager;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
        $this->resourcesDir = config('filesystems.resources_dir', 'resources');
        $this->esmSessionManager = app(EsmSessionManager::class);
    }

    /**
     * ファイルをアップロードする
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return bool アップロードの成否
     */
    public function upload(UploadedFile $file, string $folder, string $filename = null): bool
    {
        if (is_null($folder) || is_null($filename)) {
            throw new \InvalidArgumentException('Folder and filename are required.');
        }

        $accountCode = $this->esmSessionManager->getAccountCode();
        $folder = "/{$accountCode}/{$folder}";

        return $this->uploadFile($file, $folder, $filename);
    }

    /**
     * リソースディレクトリにファイルをアップロードする
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $filename
     * @return bool アップロードの成否
     */
    public function resourcesUpload(UploadedFile $file, string $folder, string $filename = null): bool
    {
        if (is_null($folder) || is_null($filename)) {
            throw new \InvalidArgumentException('Folder and filename are required.');
        }

        $accountCode = $this->esmSessionManager->getAccountCode();
        $folder = "/{$this->resourcesDir}/{$accountCode}/{$folder}";

        return $this->uploadFile($file, $folder, $filename);
    }

    /**
     * ファイルをアップロードする共通処理
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $filename
     * @return bool ファイルアプロードの成否
     */
    protected function uploadFile(UploadedFile $file, string $folder, string $filename): bool
    {
        try {
            $filesystem = Storage::disk($this->disk)->getDriver();
            $stream = fopen($file->getRealPath(), 'r');
            $path = "{$folder}/{$filename}";

            // Flysystemの直接書き込み
            $filesystem->writeStream($path, $stream);

            Log::info("File uploaded successfully using Flysystem.", [
                'disk' => $this->disk,
                'folder' => $folder,
                'filename' => $filename,
                'path' => $path,
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            return true;

        } catch (UnableToWriteFile $e) {
            // Flysystemの書き込みエラーを補足
            Log::error("Flysystem unable to write file.", [
                'disk' => $this->disk,
                'folder' => $folder,
                'filename' => $filename,
                'message' => $e->getMessage(),
            ]);
            return false;
        } catch (FilesystemException $e) {
            // その他のFlysystem例外
            Log::error("Flysystem exception occurred.", [
                'disk' => $this->disk,
                'folder' => $folder,
                'filename' => $filename,
                'message' => $e->getMessage(),
            ]);
            return false;
        } catch (\Exception $e) {
            // 一般的な例外を捕捉
            Log::error("An unexpected exception occurred during file upload.", [
                'disk' => $this->disk,
                'folder' => $folder,
                'filename' => $filename,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * ファイルを削除する
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * リソースファイルを削除する
     *
     * @param string $path
     * @return bool
     */
    public function resourcesDelete(string $path): bool
    {
        $path = "{$this->resourcesDir}/{$path}";
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * ファイルのURLを取得する
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }
}
