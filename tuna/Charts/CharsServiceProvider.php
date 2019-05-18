<?php namespace Tuna\Charts;

use GuzzleHttp\Client;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tuna\BBC\Api as BBC;
use Tuna\Galgalatz\Api as Galgalatz;
use Tuna\iTunes\Api as iTunes;
use Tuna\SpotifyCharts\Api as SpotifyCharts;
use Tuna\YouTube\Api as YouTube;


class CharsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->add(SpotifyCharts::class);

        $this->add(
            YouTube::class,
            function (Application $app) {

                $yt = new YouTube(['key' => config('services.youtube.key')]);

                $yt->setClient($app->make(Client::class));

                return $yt;
            }
        );

        $this->add([iTunes::class, BBC::class, Galgalatz::class]);
    }

    /**
     * @param string|array $services
     * @param callable|null $concrete
     * @return void
     */
    protected function add($services, $concrete = null)
    {
        foreach ((array)$services as $service) {

            $this->app->singleton(
                $service,
                $concrete ?: function (Application $app) use ($service) {
                    return new $service($app->make(Client::class));
                }
            );

            $this->app->tag($service, ChartResolver::TAG);

        }
    }
}