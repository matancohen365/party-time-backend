<?php namespace Tuna\YouTubeConverterMe;

use GuzzleHttp\Client;
use Tuna\FileSystem\FileInterface as File;
use tuna\YouTube\DownloaderInterface;
use Tuna\YouTube\Video;

class Api implements DownloaderInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return 'http://youtubeconverter.me';
    }

    /**
     * @param \Tuna\YouTube\Video $video
     * @param \Tuna\FileSystem\FileInterface $file
     * @param string $type
     * @param int $quality
     */
    public function fetch(Video $video, File $file, $type = 'mp3', $quality = 320)
    {
        $this->download($video, $type, $quality)->saveTo($file);
    }

    /**
     * @param \Tuna\YouTube\Video $video
     * @param string $type
     * @param int $quality
     *
     * @return \Tuna\YouTubeConverterMe\Download
     */
    public function download(Video $video, $type = 'mp3', $quality = 320)
    {
        return new Download($this, $video, $type, $quality);
    }

    /**
     * @param \Tuna\YouTube\Video $video
     * @param string $type
     * @param int $quality
     *
     * @return string
     */
    public function getDownloadUrl(Video $video, $type = 'mp3', $quality = 320)
    {
        return $this->download($video, $type, $quality)->getUrl();
    }
}