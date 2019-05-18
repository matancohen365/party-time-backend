<?php namespace Tuna\Charts;

use Cache;

class ChartResolver
{

    const TAG = 'charts';

    /**
     * @var \Tuna\Charts\Chartable
     */
    protected $chart;

    /**
     * ChartResolver constructor.
     *
     * @param string $chart
     */
    public function __construct($chart = '')
    {
        $this->chart = $this->getProviderByName($chart);
    }

    /**
     * @param string $name
     *
     * @return \Tuna\Charts\Chartable
     */
    public function getProviderByName($name)
    {
        foreach ($this->getProviders() as $provider) {
            if (strtolower($provider->getName()) === strtolower($name)) {
                return $provider;
            }
        }

        return $this->getDefaultProvider();
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return app()->tagged(static::TAG);
    }

    /**
     * @return \Tuna\Charts\Chartable
     */
    protected function getDefaultProvider()
    {
        return array_first($this->getProviders());
    }

    /**
     * @param string $chart
     *
     * @return \Tuna\Charts\ChartResolver
     */
    public static function make($chart = '')
    {
        return new static($chart);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getTopSingles()
    {
        /** @var \Illuminate\Support\Collection $singles */
        $singles = Cache::remember(
            $this->getCacheKey(),
            $this->getCacheTime(),
            function () {
                return $this->getChart()->getTopSingles();
            }
        );

        if ($singles->isEmpty()) {
            Cache::forget($this->getCacheKey());
        }

        return $singles;
    }

    /**
     * @return string
     */
    private function getCacheKey()
    {
        return implode('.', [static::TAG, $this->getName()]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getChart()->getName();
    }

    /**
     * @return \Tuna\Charts\Chartable
     */
    public function getChart()
    {
        return $this->chart;
    }

    /**
     * @return int
     */
    private function getCacheTime()
    {
        return 120;
    }
}