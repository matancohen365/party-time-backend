<?php namespace Tuna\YouTube;

use Tuna\FileSystem\FileInterface as File;

/**
 * Interface DownloaderInterface
 * @package Tuna\YouTube
 */
interface DownloaderInterface
{
    /**
     * @param \Tuna\YouTube\Video $video
     * @param \Tuna\FileSystem\FileInterface $file
     * @param string $type
     * @param int $quality
     * @return void
     */
    public function fetch(Video $video, File $file, $type = 'mp3', $quality = 320);
}