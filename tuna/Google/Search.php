<?php namespace Tuna\Google;


use Illuminate\Support\Collection;

class Search
{
    /**
     * @var \Tuna\Google\Api
     */
    protected $api;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $results;

    /**
     * Search constructor.
     *
     * @param \Tuna\Google\Api $api
     * @param                  $query
     */
    function __construct(Api $api, $query)
    {
        $this->api = $api;
        $this->query = $query;
    }

    /**
     * @param bool $fresh
     *
     * @return \Illuminate\Support\Collection
     */
    public function getResults($fresh = false)
    {
        if ($fresh || is_null($this->results)) {
            $this->results = $this->fatchResults();
        }

        return $this->results;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function fatchResults()
    {
        $results = new Collection;

        $response = $this->getApi()->getClient()->get(
            'https://www.googleapis.com/customsearch/v1',
            [
                'query' => [
                    'key' => $this->getApi()->getKey(),
                    'cx' => $this->getApi()->getSearchKey(),
                    'q' => $this->getQuery(),
                ],
            ]
        );

        if ($response->getStatusCode() === 200) {

            try {
                $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), 1);

                if (!empty($json['items'])) {

                    foreach ($json['items'] as $key => $item) {

                        if ($item['kind'] === 'customsearch#result') {

                            $results[$key] = array_only($item, ['title', 'link', 'displayLink']);

                        }
                    }
                }

            } catch (\InvalidArgumentException $e) {
            }

        }

        return $results;
    }

    /**
     * @return \Tuna\Google\Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

}