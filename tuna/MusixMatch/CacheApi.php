<?php namespace Tuna\MusixMatch;

use Cache;

class CacheApi implements ApiInterface
{
    /**
     * @var ApiInterface
     */
    protected $next;

    /**
     * CacheApi constructor.
     *
     * @param ApiInterface $next
     */
    public function __construct(ApiInterface $next)
    {
        $this->next = $next;
    }

    /**
     * @param string $term
     *
     * @return string
     */
    public function getLyrics($term)
    {
        return Cache::rememberForever(
            md5(__CLASS__.__METHOD__.$term),
            function () use ($term) {
                return $this->next->getLyrics($term);
            }
        );
    }

    /**
     * @param string $term
     *
     * @return \Illuminate\Support\Collection
     */
    public function search($term)
    {
        return Cache::rememberForever(
            md5(__CLASS__.__METHOD__.$term),
            function () use ($term) {
                return $this->next->search($term);
            }
        );

    }
}