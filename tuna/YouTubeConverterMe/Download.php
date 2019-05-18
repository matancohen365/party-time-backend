<?php namespace Tuna\YouTubeConverterMe;

use Exception;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Tuna\FileSystem\FileInterface as File;
use Tuna\YouTube\Video;


class Download
{
    /**
     * @var \Tuna\YouTubeConverterMe\Api
     */
    protected $api;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \Tuna\YouTube\Video
     */
    protected $video;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $quality;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $response;

    /**
     * Download constructor.
     *
     * @param \Tuna\YouTubeConverterMe\Api $api
     * @param Video $video
     * @param string $type
     * @param int $quality
     */
    public function __construct(Api $api, Video $video, $type = 'mp3', $quality = 320)
    {
        $this->setApi($api);
        $this->setVideo($video);
        $this->setQuality($quality);
    }

    /**
     * @param \Tuna\FileSystem\FileInterface $file
     */
    public function saveTo(File $file)
    {
        $this->getClient()->request(
            'GET',
            $this->getUrl(),
            [
                'sink' => $file->getFile(),
            ]
        );
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        if (is_null($this->client)) {
            $this->client = $this->getApi()->getClient();
        }

        return $this->client;
    }

    /**
     * @return \Tuna\YouTubeConverterMe\Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param \Tuna\YouTubeConverterMe\Api $api
     */
    protected function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if (is_null($this->url)) {

            $this->process();

        }

        return $this->url;
    }

    /**
     * @param string $url
     */
    protected function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param bool $first
     *
     * @throws Exception
     */
    protected function process($first = true)
    {
        $response = $this->request($first);

        if (!isset($response->status)) {
            throw new Exception(
                "I did not understand DOWNLOAD-API Response (rebuild?)",
                502
            );
        }

        switch ($response->status) {

            case "downloading":
            case "converting":
                usleep(500000);
                $this->process(false);
                break;

            case "success":
                $this->setUrl(
                    sprintf(
                        '%s/download/%s/%s?quality=%d',
                        $this->getApi()->getEndpoint(),
                        $this->getVideo()->getKey(),
                        md5(rand()),
                        $this->getQuality()
                    )
                );
                break;

            default:
                throw new Exception(
                    "I did not understand DOWNLOAD-API Response (rebuild?)",
                    502
                );

        }
    }

    protected function request($first = true)
    {
        $params = [
            'quality' => $this->getQuality(),
            'url' => $this->getVideo()->getUrl(),
        ];

        if ($first) {
            array_add($params, 'first', 'true');
        }

        $response = $this->getClient()->post(
            $this->getApi()->getEndpoint().'/success/status',
            [
                'form_params' => $params,
                'headers' => [
                    'X-CSRF-TOKEN' => $this->getCSRFToken(),
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception(
                "DOWNLOAD-API HTTP Request Error ".$response->getStatusCode()." ".$response->getReasonPhrase(),
                $response->getStatusCode()
            );
        }

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    /**
     * @return int
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @param int $quality
     */
    protected function setQuality($quality)
    {
        if (!in_array($quality, $qualities = [64, 128, 320], true)) {
            throw new InvalidArgumentException(
                "Quality must be one of: ".implode(',', $qualities).", '{$quality}' giving."
            );
        }

        $this->quality = $quality;
    }

    /**
     * @return \Tuna\YouTube\Video
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param \Tuna\YouTube\Video $video
     */
    protected function setVideo(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @throws Exception
     * @return string
     */
    private function getCSRFToken()
    {
        static $csrf_token = null;

        if (is_null($csrf_token)) {

            $response = $this->getClient()->get($this->getApi()->getEndpoint());

            if ($response->getStatusCode() !== 200) {
                throw new Exception(
                    "DOWNLOAD-API HTTP Request Error ".$response->getStatusCode()." ".$response->getReasonPhrase(),
                    $response->getStatusCode()
                );
            }

            $crawler = new Crawler($response->getBody()->getContents());

            $csrf_token = $crawler->filter('#token')->attr('value');

        }

        return $csrf_token;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getType()
    {
        return 'mp3';
    }

    /**
     * @param string $type
     *
     * @deprecated
     */
    protected function setType($type)
    {
    }
}