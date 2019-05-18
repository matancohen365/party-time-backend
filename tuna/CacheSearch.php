<?php namespace Tuna;

use Cache;


class CacheSearch implements SearchInterface
{
    /**
     * one day in minutes
     */
    const ONE_DAY = 60 * 24;

    /**
     * @var \Tuna\SearchInterface
     */
    protected $next;

    /**
     * CacheSearch constructor.
     *
     * @param \Tuna\SearchInterface $next
     */
    public function __construct(SearchInterface $next)
    {
        $this->next = $next;
    }

    /**
     * @param string $term
     */
    public function setTerm($term)
    {
        $this->getNext()->setTerm($term);
    }

    /**
     * @return \Tuna\SearchInterface
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getResults()
    {
        return Cache::remember(
            "tracks.search.".$this->getTerm(),
            static::ONE_DAY,
            function () {

                return $this->getNext()->getResults();

            }
        );
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->getNext()->getTerm();
    }
}