<?php namespace Tuna\Google;

use GuzzleHttp\Client;

class Api
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     *
     * @{url: https://console.developers.google.com/apis/credentials/key/}
     */
    protected $key;

    /**
     * @var string
     * @{url: https://cse.google.com/cse/all}
     */
    protected $searchKey;

    /**
     * Api constructor.
     *
     * @param \GuzzleHttp\Client $client
     * @param string $key
     * @param string $searchKey
     */
    public function __construct(Client $client = null, $key = '', $searchKey = '')
    {
        $this->client = $client ?: new Client;

        $this->key = $key;
        $this->searchKey = $searchKey;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSearchKey()
    {
        return $this->searchKey;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $query
     *
     * @return \Tuna\Google\Search
     */
    public function search($query)
    {
        return new Search($this, $query);
    }
}