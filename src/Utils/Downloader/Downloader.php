<?php

namespace Kiwilan\Steward\Utils\Downloader;

class Downloader
{
    protected function __construct(
        protected ?string $filename = null,
        protected ?int $size = null,
        protected string $mimeType = 'application/octet-stream',
        protected int $maxExecutionTime = 36000,
    ) {
    }

    /**
     * Initialize the downloader.
     */
    public static function make(): self
    {
        return new self();
    }

    public function direct(string $path): DownloaderDirect
    {
        $download = new DownloaderDirect($path);
        $download->filename = basename($path);

        return $download;
    }

    public function stream(string $filename): DownloaderZipStream
    {
        $zip = new DownloaderZipStream();
        $zip->filename = "{$filename}.zip";

        return $zip;
    }

    /**
     * Set the value of maxExecutionTime, in seconds.
     *
     * @default 36000
     */
    public function maxExecutionTime(int $maxExecutionTime): static
    {
        $this->maxExecutionTime = $maxExecutionTime;

        return $this;
    }

    /**
     * Set the value of mimeType. If null, it will be automatically determined from the filename.
     */
    public function mimeType(?string $mimeType = null): static
    {
        if ($mimeType) {
            $this->mimeType = $mimeType;
        } else {
            $extension = pathinfo($this->filename, PATHINFO_EXTENSION);
            $this->mimeType = $this->extensionToMimetype($extension);
        }

        return $this;
    }

    protected function sendHeaders(): void
    {
        if (headers_sent($filename, $linenum)) {
            throw new \Exception("Headers have already been sent. File: {$filename} Line: {$linenum}");
        }
        header("Content-Type: {$this->mimeType}");
        header('Content-Description: file transfer');
        header("Content-Disposition: attachment;filename={$this->filename}");

        if ($this->size) {
            header("Content-Length: {$this->size}");
        }
        header('Accept-Ranges: bytes');
        header('Pragma: public');
        header('Expires: -1');
        header('Cache-Control: no-cache');
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
    }

    private function extensionToMimetype(string $extension): string
    {
        return match ($extension) {
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'mp4' => 'video/mp4',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
