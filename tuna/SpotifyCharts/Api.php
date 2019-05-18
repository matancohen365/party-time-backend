<?php namespace Tuna\SpotifyCharts;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;
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
        $response = $this
            ->getClient()
            ->request('GET', 'https://spotifycharts.com/regional/')
            ->getBody()
            ->getContents();

        $crawler = new Crawler($response);

        $singles = new Collection(
            $crawler->filter('.chart-table tbody tr')->each(
                function (Crawler $node, $index) {

                    try {

                        // new entry to the top :D

                        $node->filter('svg[fill="#4687d7"]')->first()->html();
                        $position = $index - 200;
                    } catch (\Exception $e) {
                        $position = $index + 1;
                    }

                    return (object)[
                        'title' => trim($node->filter('.chart-table-track strong')->first()->text()),
                        'artist' => trim(substr($node->filter('.chart-table-track span')->first()->text(), 3)),
                        'cover' => $node->filter('.chart-table-image img[src]')->first()->attr('src'),
                        'position' => $position,
                    ];

                }
            )
        );

        return $singles->sortBy('position');
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Spotify';
    }
}