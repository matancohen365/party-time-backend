<?php namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Tuna\Charts\ChartResolver;
use Tuna\Lyrics;
use Tuna\SearchInterface;
use Tuna\YouTube\Api as YouTube;

class HomeController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        return view('tracks', [
            'tracks' => ChartResolver::make($request->get('chart'))->getTopSingles(),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Tuna\SearchInterface $search
     *
     * @param \Tuna\YouTube\Api $youtube
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View|string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function search(Request $request, SearchInterface $search, YouTube $youtube)
    {
        // empty query
        if( ! $request->has('term') )
            return $this->index($request);

        // force download
        if( $request->exists('force') )
            $request->session()->put('force', true);

        $search->setTerm($request->get('term'));

        $results = $search->getResults();

        if( ! $results->isEmpty() ) {
            // one track,
           if( $results->count() == 1 ) {
                return redirect(route('track', $results->first()->hash));
           }

            // many tracks,

            $hash = $results->shift()->hash;

            $track = \Cache::get("track." . $hash);

            if( empty($track) )
                return view('error', [
                    'error' => 'Not Found',
                ]);

            if( ! isset($track->lyrics) ) {

                $track->lyrics = Lyrics::get("{$track->artist} - {$track->title}");

                \Cache::forever("track." . $hash, $track);
            }

            if( $request->session()->pull('force', false) )
                return redirect()->route('download', $hash);

            $track->video = $youtube->video($track->video);

            return view('track', [
                'track' => $track,
                'tracks' => $results,
            ]);
        }

        // no tracks,

        // force download?
        if( $request->session()->pull('force', false) ) {

            $route = e(route('force_download', ['video' => $request->get('term')]));

            return <<<JS
Loading..
<script>setTimeout(function() {
    window.location.replace('{$route}');
}, 2000)</script>
JS;

        }

        // nothing else ..
        return view('error', [
            'error' => "Can't find that..",
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function force_download(Request $request)
    {
        try {
            /** @var YouTube $youtube */
            $youtube = resolve(YouTube::class);

            /** @var Client $client */
            $client = resolve(Client::class);

            if( $video = $youtube->getVideoByUrl($request->get('video')) ) {

                $video->load();

                $tags = explode('-', $video->getTitle(), 2);
                $title = trim(last($tags));
                $artist = trim(head($tags));

                return $video->download()->setTags([
                                                       'title'  => $title,
                                                       'artist' => $artist,
                                                       'album'  => title_case("{$title} - single"),
                                                       'track'  => 1,
                                                       'cover'  => $client->request('GET', $video->getThumbnail())->getBody()->getContents(),
                                                   ])->toResponse();
            }

        } catch( \Exception $e ) {

            return view('error', [
                'error' => $e->getMessage(),
            ]);
        }

        return view('error', [
            'error' => '404..',
        ]);
    }
}
