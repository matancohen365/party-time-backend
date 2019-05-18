<?php namespace Tuna\YouTube;

use DateTime;
use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Tuna\FileSystem\File;

class Video
{
    /**
     * @var \Tuna\YouTube\Api
     */
    protected $api;

    /**
     * @var array
     */
    protected $info = [];

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    private $crawler;

    /**
     * Video constructor.
     *
     * @param Api $api
     * @param string $key
     * @param array $info
     */
    public function __construct(Api $api, $key, $info = [])
    {
        $this->setApi($api);
        $this->setKey($key);
        $this->setInfo($info);
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->setInfo('key', $key);
    }

    /**
     * Download (mp3)
     *
     * @return \Tuna\FileSystem\File
     * @throws \Exception
     */
    public function download()
    {
        $file = new File(storage_path("downloads/yt_{$this->getKey()}.mp3"));

        if (!$file->exists()) {
            $downloader = new Downloader;

            $downloader->fetch($this, $file);
        }

        return $file;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->getInfo('key');
    }

    /**
     * @param string|null $info
     * @param string|null $default
     *
     * @return string|null
     */
    public function getInfo($info = null, $default = null)
    {
        if (!is_string($info)) {
            $this->info;
        }

        if (key_exists($info, $this->info)) {
            return $this->info[$info];
        }

        return $default;
    }

    /**
     * @param              $info
     * @param array|string $value
     */
    public function setInfo($info, $value = null)
    {
        if (is_string($info)) {
            $this->info[$info] = $value;
        } else {
            $this->info = array_merge($this->info, $info);
        }
    }

    /**
     * @return \Tuna\YouTube\Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param \Tuna\YouTube\Api $api
     */
    public function setApi(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return null|string
     * @throws Exception
     */
    public function getTerm()
    {
        try {

            $response = $this->getApi()->getClient()->request(
                'GET',
                $this->getUrl(),
                [
                    'headers' => [
                        'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
                    ],
                ]
            );

            if ($response->getStatusCode() == 200) {

                $this->crawler = $crawler = new Crawler();
                $crawler->addHtmlContent($response->getBody()->getContents());

                $tag = $crawler->filter('li.watch-meta-item ul li')->last()->text();

                $tag = str_replace('\'', '"', $tag);

                if (preg_match('/"(.+)"\s+(by|מאת)\s+([^(<]+)/i', $tag, $matches)) {

                    return implode(
                        ' - ',
                        [
                            'artist' => trim($matches[3], " \t\n\r\0\x0B(\"'"),
                            'title' => trim($matches[1], " \t\n\r\0\x0B(\"'"),
                        ]
                    );
                }

            }

        } catch (Exception $e) {
        }

        return $this->load()->getTitle();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return sprintf('%s?v=%s', $this->getApi()->getUrl('watch'), $this->getKey());
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->getInfo('title');
    }

    /**
     * @return self
     * @throws \Exception
     */
    public function load()
    {
        if ($this->getTitle() === null) {

            $info = $this->getApi()->getVideoInfo($this->getKey());

            if (isset($info->kind) && $info->kind === 'youtube#video') {

                $thumbnail = isset($info->snippet->thumbnails->standard) ? $info->snippet->thumbnails->standard : $info->snippet->thumbnails->default;

                $this->setInfo(
                    [
                        'title' => $info->snippet->title,
                        'description' => $info->snippet->description,
                        'thumbnail' => $thumbnail->url,
                    ]
                );


            }
        }

        return $this;
    }

    public function validate()
    {
        try {
            return ! empty($this->getApi()->getVideoInfo($this->getKey()));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return $this->getInfo('description');
    }

    /**
     * @return null|string
     */
    public function getThumbnail()
    {
        return $this->getInfo('thumbnail');
    }

    /**
     * @return string
     */
    public function getReleaseDate()
    {
        if ($this->crawler instanceof Crawler) {

            try {
                return $this->crawler->filter('meta[itemprop="datePublished"]')->first()->attr('content');
            } catch (\Exception $e) {
            }
        }

        return (new DateTime)->format('Y-m-d');
    }
}