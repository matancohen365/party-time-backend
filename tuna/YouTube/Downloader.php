<?php namespace Tuna\YouTube;

/**
 * Class Downloader
 * @package Tuna\YouTube
 * @see https://rg3.github.io/youtube-dl/download.html
 * @see https://www.ffmpeg.org/download.html
 */

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;
use Tuna\FileSystem\FileInterface as File;

class Downloader
{
    /**
     * @param \Tuna\YouTube\Video $video
     * @param \Tuna\FileSystem\FileInterface $file
     * @param string $type
     * @param int $quality
     * @throws \Exception
     */
    public function fetch(Video $video, File $file, $type = 'mp3', $quality = 320)
    {
        $this->updatedDownloader();

        new Download($this, $video, $file, $type, $quality);
    }

    /**
     * @link https://rg3.github.io/youtube-dl/download.html
     * @return void
     * @throws \Exception
     */
    protected function updatedDownloader()
    {
        $this->cmd($this->getYoutubeDownloaderPath(), ['-U']);
    }

    /**
     * @param string $cmd
     * @param array $args
     * @throws \Exception
     */
    public function cmd($cmd, $args = [])
    {
        try {

            $builder = new ProcessBuilder();

            $process = $builder
                ->setPrefix($cmd)
                ->setArguments($args)
                ->getProcess()
                ->setTimeout(120);

            $process->run();
            $process->wait();

            if (!$process->isSuccessful()) {
                throw new \Exception($process->getErrorOutput());
            }
        } catch (ProcessFailedException $e) {
        }
    }

    /**
     * @return string
     */
    public function getYoutubeDownloaderPath()
    {
        return 'youtube-dl';
    }
}

class Download
{
    /**
     * @var \Tuna\YouTube\Downloader
     */
    protected $downloader;

    /**
     * @var \Tuna\YouTube\Video
     */
    protected $video;

    /**
     * @var \Tuna\FileSystem\FileInterface
     */
    protected $file;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $quality;

    /**
     * @var \Tuna\FileSystem\FileInterface
     */
    protected $tmpFile;

    /**
     * Download constructor.
     * @param \Tuna\YouTube\Downloader|null $downloader
     * @param \Tuna\YouTube\Video $video
     * @param \Tuna\FileSystem\FileInterface $file
     * @param string $type
     * @param int $quality
     * @throws \Exception
     */
    public function __construct(Downloader $downloader = null, Video $video, File $file, $type = 'mp3', $quality = 320)
    {
        $this->downloader = $downloader ?: new Downloader();
        $this->video = $video;
        $this->file = $file;
        $this->type = $type;
        $this->quality = $quality;

        $this->download()->convert()->deleteTmpFile();
    }

    /**
     * @return self
     */
    protected function deleteTmpFile()
    {
        $this->getTmpFile()->delete();

        return $this;
    }

    /**
     * @return \Tuna\FileSystem\FileInterface
     */
    protected function getTmpFile()
    {
        if (is_null($this->tmpFile)) {

            $this->tmpFile = new \Tuna\FileSystem\File($this->getFile()->getFile().'.tmp');
        }

        return $this->tmpFile;
    }

    /**
     * @return \Tuna\FileSystem\FileInterface
     */
    protected function getFile()
    {
        return $this->file;
    }

    /**
     * @link https://www.ffmpeg.org/download.html
     * @return self
     * @throws \Exception
     */
    protected function convert()
    {
        if (!$this->getFile()->exists()) {
            $this->getDownloader()->cmd(
                'ffmpeg',
                [
                    // input file
                    '-i',
                    $this->getTmpFile()->getFile(),
                    // volume 120%
                    '-af',
                    'volume=1.2',
                    // output type
                    '-f',
                    $this->getType(),
                    // output file
                    $this->getFile()->getFile(),
                ]
            );
        }

        return $this;
    }

    /**
     * @return \Tuna\YouTube\Downloader
     */
    protected function getDownloader()
    {
        return $this->downloader;
    }

    /**
     * @return string
     */
    protected function getType()
    {
        return $this->type;
    }

    /**
     * @link https://rg3.github.io/youtube-dl/download.html
     * @return self
     * @throws \Exception
     */
    protected function download()
    {
        if (!$this->getTmpFile()->exists()) {
            $this->getDownloader()->cmd(
                $this->getDownloader()->getYoutubeDownloaderPath(),
                [
                    '--no-warnings',
                    '-f',
                    $this->getQuality(),
                    '-o',
                    $this->getTmpFile()->getFile(),
                    $this->getVideo()->getUrl(),
                ]
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getQuality()
    {
        return 'bestaudio';
    }

    /**
     * @return \Tuna\YouTube\Video
     */
    protected function getVideo()
    {
        return $this->video;
    }
}