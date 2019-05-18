<?php namespace Tuna\Google;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            Api::class,
            function () {
                return new Api(
                    resolve(Client::class),
                    config('services.google.key', 'AIzaSyCik8JBuCDmF6lqkpv1ORtNsomc8RhvqwQ'),
                    config('services.google.search.id', '001884763667061673598:nl9zdkfksac')
                );
            }
        );
    }
}