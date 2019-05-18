<?php namespace Tuna\MusixMatch;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class Api implements ApiInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Api constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client;
    }

    /**
     * @param string $term
     *
     * @return string
     */
    public function getLyrics($term)
    {
        try {

            $results = $this->search($term);

            if ($results->isNotEmpty()) {

                $crawler = $this->request($results->first().'/translation/hebrew');

                try {

                    return $this->filterLyrics(
                        $crawler->filter('.mxm-translatable-line-readonly .col-xs-6')->extract(['_text'])
                    );

                } catch (InvalidArgumentException $e) {
                    return 'Error processing the DOM.';
                }

            }

        } catch (RuntimeException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $term
     *
     * @return Collection
     */
    public function search($term)
    {
        $term = urlencode(trim(preg_replace(['/[^\w\d\s]/ui', '/\s+/ui'], ' ', $term)));

        $crawler = $this->request("search/{$term}/tracks");

        try {

            return new Collection($crawler->filter('a.title[href]')->extract(['href']));

        } catch (InvalidArgumentException $e) {
        }

        return new Collection;
    }

    /**
     * @param string $uri
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \RuntimeException
     */
    protected function request($uri = '/')
    {
        $response = $this->getClient()->request('GET', $this->getEndpoint($uri));

        if ($response->getStatusCode() == 200) {

            $crawler = new Crawler;
            $crawler->addHtmlContent($response->getBody()->getContents());

            return $crawler;
        }

        throw new RuntimeException('Error processing the request.');
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    public function getEndpoint($uri = '')
    {
        return 'https://www.musixmatch.com/'.ltrim($uri, '/');
    }

    /**
     * @param array $lyrics
     *
     * @return string
     */
    private function filterLyrics($lyrics)
    {
        $keyword = '---777---DELETE---777---';

        foreach ($lyrics as $key => &$lyric) {
            if (!empty($lyric) && isset($lyrics[$key + 1]) && $lyric === $lyrics[$key + 1]) {
                $lyric = $keyword;
            }
        }

        return trim(
            implode(
                PHP_EOL,
                array_filter(
                    $lyrics,
                    function ($lyric) use ($keyword) {
                        return $lyric !== $keyword;
                    }
                )
            )
        );
    }
}