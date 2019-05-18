<?php namespace App\Providers;

use App\Observers\TrackObserver;
use App\Track;
use Tuna\Search;
use Tuna\CacheSearch;
use GuzzleHttp\Client;
use Tuna\SearchInterface;
use GuzzleHttp\Cookie\CookieJar;
use Tuna\MusixMatch\ApiInterface;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Application;
use Tuna\MusixMatch\Api as MusixMatch;
use Illuminate\Support\ServiceProvider;
use Tuna\MusixMatch\CacheApi as MusixMatchCache;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootBlade();

        Track::observe(TrackObserver::class);
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        if (config('app.env') === 'production') {
            $this->getRequest()->server->set('HTTPS', true);
        }

        $this->registerClientProvider();
        $this->registerSearchProvider();
        $this->registerMusixMatchApiProvider();
    }

    /**
     * @return void
     */
    protected function bootBlade()
    {
        /* Usage: @nl2br($expression) */
        Blade::directive(
            'nl2br',
            function ($expression) {
                return "<?php echo nl2br(e($expression)); ?>";
            }
        );
    }

    /**
     * @return void
     */
    protected function registerClientProvider()
    {
        $this->app->singleton(
            Client::class,
            function () {
                return new Client(
                    [
                        'verify' => false,
                        'cookies' => new CookieJar(),
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:54.0) Gecko/20100101 Firefox/54.0',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language' => 'he,en;q=0.5',
                            'Accept-Encoding' => 'gzip, deflate',
                            'X-Forwarded-For' => $this->getRequest()->ip(),
                        ],
                        'connect_timeout' => pi(),
                    ]
                );
            }
        );
    }

    /**
     * @return void
     */
    protected function registerSearchProvider()
    {
        $this->app->bind(
            SearchInterface::class,
            function () {
                return new CacheSearch(new Search());
            }
        );
    }

    /**
     * @return void
     */
    protected function registerMusixMatchApiProvider()
    {
        $this->app->bind(
            ApiInterface::class,
            function (Application $app) {
                return new MusixMatchCache(new MusixMatch($app->make(Client::class)));
            }
        );
    }

    /**
     * @return \Illuminate\Http\Request
     */
    private function getRequest()
    {
        return $this->app['request'];
    }
}
