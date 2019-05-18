<?php namespace Tuna\iTunes;

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
     * @param        $term
     * @param string $entity
     * @param int $limit
     *
     * @return \Tuna\iTunes\Search
     */
    public function search($term, $entity = 'song', $limit = 25)
    {
        return new Search($this->getClient(), $term, $entity, $limit);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTopSingles()
    {
        $response = $this
            ->getClient()
            ->request('GET', 'https://www.apple.com/itunes/charts/songs/')
            ->getBody()
            ->getContents();

        $crawler = new Crawler($response);

        return new Collection(
            $crawler->filter('.section-content li')->each(
                function (Crawler $node) {

                    return (object)[
                        'title' => trim($node->filter('h3')->first()->text()),
                        'artist' => trim($node->filter('h4')->first()->text()),
                        'cover' => 'https://www.apple.com'.$node->filter('img')->first()->attr('src'),
                        'position' => trim($node->filter('strong')->first()->text(), " \t\n\r\0\x0B."),
                    ];

                }
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'iTunes';
    }
}