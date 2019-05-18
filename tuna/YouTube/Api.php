<?php namespace Tuna\YouTube;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Madcoda\Youtube\Youtube as MadcodaYoutube;
use Tuna\Charts\Chartable;

class Api extends MadcodaYoutube implements Chartable
{
    /**
     * @var string
     */
    protected $endpoint = 'https://www.youtube.com/';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function getUrl($uri = '')
    {
        return sprintf('%s/%s', rtrim($this->getEndpoint(), '/'), ltrim($uri, '/'));
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param string $url
     *
     * @return \Tuna\YouTube\Video|null
     */
    public function getVideoByUrl($url)
    {
        if ($key = $this->getVideoKeyByUrl($url)) {
            return $this->video($key);
        }
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function getVideoKeyByUrl($url)
    {
        return preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            $url,
            $matches
        ) ? $matches[1] : '';
    }

    /**
     * @param string $key
     * @param array $info
     *
     * @return \Tuna\YouTube\Video
     */
    public function video($key, $info = [])
    {
        return new Video($this, $key, $info);
    }

    /**
     * Get the first video from the search results
     * or throw an exception
     *
     * @param $q
     *
     * @return \Tuna\YouTube\Video|null
     * @throws \Exception
     */
    public function firstOrThrow($q)
    {
        $video = $this->searchVideos($q)->first();

        if (!($video instanceof Video)) {
            throw new Exception;
        }

        return $video;
    }

    /**
     * @param string $q
     * @param int $maxResults
     * @param null $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function searchVideos($q, $maxResults = 10, $order = null)
    {
        $results = parent::searchVideos($q, $maxResults, $order);

        if (is_array($results)) {

            foreach ($results as &$result) {

                $result = $this->video(
                    $result->id->videoId,
                    [
                        'title' => $result->snippet->title,
                        'description' => $result->snippet->description,
                        'thumbnail' => $result->snippet->thumbnails->high->url,
                    ]
                );
            }

        }

        return new Collection($results);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTopSingles()
    {
        $singles = [];

        try {

            foreach (parent::getPlaylistItemsByPlaylistId('PLFgquLnL59alW3xmYiWRaoz0oM3H17Lth') as $key => $item) {

                try {

                    $singles[] = (object)[
                        'title' => $item->snippet->title,
                        'artist' => '',
                        'cover' => $item->snippet->thumbnails->default->url,
                        'position' => $key + 1,
                        'term' => 'https://youtu.be/'.$item->snippet->resourceId->videoId,
                    ];

                } catch (\Exception $e) {
                    // Private video ..
                }

            }

        } catch (\Exception $e) {
            // Private playlist ..
        }

        return new Collection($singles);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'YouTube';
    }
}