<?php namespace Tuna\MusixMatch;


interface ApiInterface
{
    /**
     * @param string $term
     *
     * @return string
     */
    public function getLyrics($term);

    /**
     * @param string $term
     *
     * @return \Illuminate\Support\Collection
     */
    public function search($term);
}