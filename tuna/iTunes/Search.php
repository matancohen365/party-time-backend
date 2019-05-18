<?php namespace Tuna\iTunes;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class Search
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $results;

    /**
     * @param \GuzzleHttp\Client $client
     * @param string $term
     * @param string $entity
     * @param integer $limit
     */
    public function __construct(Client $client, $term, $entity = 'song', $limit = 25)
    {
        $term = $this->escapeTerm($term);

        $this->results = new Collection(
            json_decode(
                $client->request(
                    'POST',
                    'http://itunes.apple.com/search',
                    [
                        'form_params' => compact('term', 'entity', 'limit') + [
                                'explicit' => 'yes',
                                'country' => 'IL',
                                'lang' => 'he',
                            ],
                    ]
                )->getBody()->getContents()
            )->results
        );


    }

    /**
     * @param string $term
     *
     * @return string
     */
    protected function escapeTerm($term)
    {
        return trim(
            preg_replace(
                [
                    // bad symbols
                    '/\[[^\)]+\]|\(|\)|\||\:|\"/ui',
                    // bad words
                    '/(\b|\s)(video|official|audio|music|clip|youtube|itunes|google|lyrics?|ft\.?|h[qd])(\b|\s)/ui',
                    // extra spaces
                    '/\s+/ui'
                ],
                ' ',
                $term
            ),
            " \t\n\r\0\x0B-"
        );
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getResults()
    {
        return $this->results;
    }
}
