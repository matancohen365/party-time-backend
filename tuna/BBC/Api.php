<?php namespace Tuna\BBC;

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
            ->get('http://www.bbc.co.uk/radio1/chart/singles')
            ->getBody()
            ->getContents();

        $crawler = new Crawler($response);

        $singles = new Collection(
            $crawler
                ->filter('.cht-entries .cht-entry-wrapper .entrybox')
                ->each(
                    function (Crawler $node, $index) {
                        return (object)[
                            'title' => trim($node->filter('.cht-entry-title')->first()->text()),
                            'artist' => trim($node->filter('.cht-entry-artist')->first()->text()),
                            'cover' => str_replace(
                                'http:',
                                '',
                                $node->filter('img.cht-entry-image')->first()->attr('src')
                            ),
                            'position' => $index + 1,
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
        return 'BBC';
    }
}