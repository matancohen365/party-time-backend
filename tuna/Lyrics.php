<?php namespace Tuna;

use Exception;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;
use Tuna\Google\Api as Google;

class Lyrics
{
    /**
     * @param string $query
     * @return string
     * @deprecated
     */
    public static function get($query)
    {
        return '';

        try {
            /** @var \Illuminate\Support\Collection $results */
            $results = resolve(Google::class)->search($query)->getResults();

            foreach ($results as $result) {
                if ($crawler = LyricsCrawlerFactory::make($result['displayLink'])) {

                    /** @var \Psr\Http\Message\ResponseInterface $response */
                    $response = resolve(Client::class)->get($result['link']);

                    if ($response->getStatusCode() === 200) {

                        $crawler->addHtmlContent($response->getBody()->getContents());

                        if ($lyrics = $crawler->getLyrics()) {
                            return $lyrics;
                        }

                    }

                }
            }

        } catch (Exception $e) {
        }

        return '';
    }
}

abstract class LyricsCrawler extends Crawler
{
    /**
     * @return string
     */
    abstract public function getLyrics();
}

class AZCrawler extends LyricsCrawler
{
    /**
     * @return string
     */
    public function getLyrics()
    {
        try {

            $lyrics = $this->filter('br ~ div')->first()->html();

            $lyrics = str_replace(["\n", "\r", "\t"], '', $lyrics);

            $lyrics = str_replace('<br>', PHP_EOL, $lyrics);

            $lyrics = strip_tags($lyrics);

            return trim($lyrics);
        } catch (InvalidArgumentException $e) {
        }
    }
}

class ShironetCrawler extends LyricsCrawler
{
    /**
     * @return string
     */
    public function getLyrics()
    {
        try {
            return trim($this->filter('.artist_lyrics_text')->first()->text());
        } catch (InvalidArgumentException $e) {
        }
    }
}

class MusixMatchCrawler extends LyricsCrawler
{
    /**
     * @return string
     */
    public function getLyrics()
    {
        try {
            return trim(implode(PHP_EOL, $this->filter(' p.mxm-lyrics__content')->extract(['_text'])));
        } catch (InvalidArgumentException $e) {
        }
    }
}

class LyricsCrawlerFactory
{
    /**
     * @param string
     *
     * @return LyricsCrawler
     */
    public static function make($url)
    {
        if ($crawler = array_get(
            $crawlers = [
                'www.azlyrics.com' => AZCrawler::class,
                'shironet.mako.co.il' => ShironetCrawler::class,
                'www.musixmatch.com' => MusixMatchCrawler::class,
            ],
            $url
        )
        ) {
            return new $crawler;
        }
    }
}
