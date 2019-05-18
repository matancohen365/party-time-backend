<?php namespace Tuna;


interface SearchInterface
{
    /**
     * @param string $term
     */
    public function setTerm($term);


    /**
     * @return string
     */
    public function getTerm();

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getResults();
}