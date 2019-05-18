<?php namespace Tuna\Galgalatz;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Tuna\Charts\Chartable;

class Api implements Chartable
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
     * @return \Illuminate\Support\Collection
     */
    public function getTopSingles()
    {
        $singles = new Collection;

        foreach ($this->requestJson('/umbraco/api/charts/getsubmenu') as $chart) {

            if ($title = array_get($chart, 'title')) {
                $singles = $singles->merge(
                    $this->requestTracks(
                        '/umbraco/api/charts/GetChart',
                        ['urlname' => str_replace(' ', '-', $title)]
                    )
                );
            }

        }

        return $singles;
    }

    /**
     * @param string $uri
     * @param array $query
     * @param string $site
     * @return array
     */
    private function requestJson($uri, $query = [], $site = '1920')
    {
        try {
            return \GuzzleHttp\json_decode(
                $this
                    ->getClient()
                    ->request(
                        'GET',
                        $this->getEndpoint().$uri,
                        [
                            'headers' => [
                                'site' => $site,
                            ],
                            'query' => $query + [
                                    'rootId' => $site,
                                ],
                        ]
                    )
                    ->getBody()
                    ->getContents(),
                true
            );

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    protected function getEndpoint()
    {
        return 'https://glz.co.il';
    }

    /**
     * @param  string $uri
     * @param array $query
     *
     * @return \Illuminate\Support\Collection
     */
    private function requestTracks($uri, $query = [])
    {
        $response = $this->requestJson($uri, $query);

        if ($tracks = array_get($response, 'chart.tracks')) {

            return new Collection(
                array_map(
                    function ($key, $track) {

                        return (object)[
                            'title' => array_get($track, 'quote'),
                            'artist' => array_get($track, 'quoteName'),
                            'cover' => $this->getEndpoint().array_get($track, 'image'),
                            'term' => array_get($track, 'fileUrl'),
                            'position' => $key + 1,
                        ];


                    },
                    array_keys($tracks),
                    $tracks
                )
            );

        }

        return new Collection;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Glz';
    }
}