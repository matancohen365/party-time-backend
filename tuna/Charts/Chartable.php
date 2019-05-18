<?php namespace Tuna\Charts;


interface Chartable
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTopSingles();

    /**
     * @return string
     */
    public function getName();
}