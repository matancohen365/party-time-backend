<?php namespace App\Http\Controllers;

use DateTime;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tuna\SearchInterface;
use Tuna\YouTube\Api as YouTube;
use Tuna\YouTube\Video;

class ApiController extends Controller
{
    const STATUS_OK = 'ok';
    const STATUS_ERROR = 'error';
    const CACHE_TRACK_KEY = 'api.tracks.';

    const DOWNLOAD_PENDING = 'download_pending';

    /**
     * @var \Cache
     */
    private $cache;
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;
    /**
     * @var \Tuna\YouTube\Api
     */
    private $youtube;

    /**
     * @var \Tuna\SearchInterface
     */
    private $search;

    /**
     * ApiController constructor.
     *
     * @param \GuzzleHttp\Client $client
     * @param \Tuna\YouTube\Api $youtube
     * @param \Tuna\SearchInterface $search
     */
    public function __construct(Client $client, YouTube $youtube, SearchInterface $search)
    {
        $this->client = $client;
        $this->search = $search;
        $this->youtube = $youtube;
        $this->cache = app('cache');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function search(Request $request)
    {
        return [
            'status' => static::STATUS_ERROR,
            'error' => "*** END OF SUPPORT ***\n
Please update your application to continue\n
Search `Tuna Abutbul` on the Play Store.\n
\n
אנא עדכן את גירסת האפליקציה כדי להמשיך\n
חפש `Tuna Abutbul` בחנות האפליקציות.\n
*** DO FOR LOVE ***\n"
        ];
        /*
        $this->getCache()->forever($key = 'request_count', $request_count = $this->getCache()->get($key, 0) + 1);

        if ($track = $this->getTrack($request->get('video'))) {

            $track['title'] = $this->filename_sanitizer($track['title']);
            $track['artist'] = $this->filename_sanitizer($track['artist']);

            return [
                'status' => static::STATUS_OK,
                'track' => $track,
                'request' => $request->all() + ['count' => $request_count],
                'download_url' => route('android_api_v1_download', $track['id']),
            ];
        } else {
            return [
                'status' => static::STATUS_ERROR,
                'error' => "Sorry .. Can't do much with that,\nTry a YouTube \ iTunes \ Shazam url..",
            ];
        }*/
    }

    /**
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|null
     * @throws Exception
     */
    public function download($id)
    {
        if ($track = $this->getCache()->get(static::CACHE_TRACK_KEY.$id)) {

            /*********************************************************************/
            ignore_user_abort(false);
            while($this->downloadPending($track)) {
                sleep(30);
                $track = $this->getCache()->get(static::CACHE_TRACK_KEY.$id);
            }
            ignore_user_abort(true);
            /*********************************************************************/

            if ($video = $this->getYoutube()->getVideoByUrl(array_get($track, 'video'))) {

                try {

                    $this->downloadPending($track, true);

                    $response = $video->download()->setTags($this->tracksToTags($track))->toResponse();

                    $this->downloadPending($track, false);

                    return $response;

                } catch (Exception $e) {

                    $this->downloadPending($track, false);

                    abort(429, $e->getMessage());
                }

            }

        }

        abort(404);

        return null;
    }

    /**
     * @param array $track
     * @param null $pending
     * @return bool
     */
    private function downloadPending($track, $pending = null)
    {
        if(is_null($pending))
            return array_get($track, static::DOWNLOAD_PENDING, false);

        $track[static::DOWNLOAD_PENDING] = $pending;

        $this->getCache()->put(static::CACHE_TRACK_KEY.$track['id'], $track, 60 * 24);

        return $pending;
    }

    /**
     * @return \Cache
     */
    private function getCache()
    {
        return $this->cache;
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function getTrack($url)
    {
        return $this->getCache()->rememberForever(
            'android_api_v1.search.'.md5($url),
            function () use ($url) {

                if ($track = $this->loadTrack($url)) {
                    $track = array_map(
                        function ($str) {
                            return trim(preg_replace(['/[\t\n]/ui', '/\s+/ui'], ' ', $str));
                        },
                        $track
                    );

                    if (!isset($video)) {
                        /** @var \Tuna\YouTube\Video $video */
                        $video = $this->getYoutube()->searchVideos("{$track['artist']} - {$track['title']}", 1)->first(
                        );
                    }

                    $track['video'] = $video->getUrl();

                    // cache for download use
                    $track['id'] = md5(json_encode($track));
                    $this->getCache()->put(static::CACHE_TRACK_KEY.$track['id'], $track, 60 * 24);

                    return $track;
                }

                return null;
            }
        );
    }

    /**
     * @param string $url
     * @return array|null
     */
    private function loadTrack($url)
    {
        try {
            return $this->loadTrackFromYoutube($url);
        } catch (\Exception $e) {
        }

        try {
            return $this->loadTrackFromApple($url);
        } catch (\Exception $e) {
        }

        try {
            return $this->loadTrackFromShazam($url);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function loadTrackFromYoutube($url)
    {
        $video = $this->getYoutube()->getVideoByUrl($url);

        if ($video && $video->load()) {

            $this->getSearch()->setTerm($video->getTerm());

            if ($this->getSearch()->getResults()->isEmpty()) {
                return $this->loadTagsFromYoutubeVideo($video);
            } else {
                return $this->loadTagsFromSearchResults($this->getSearch()->getResults());
            }

        }
    }

    /**
     * @return \Tuna\YouTube\Api
     */
    private function getYoutube()
    {
        return $this->youtube;
    }

    /**
     * @return \Tuna\SearchInterface
     */
    private function getSearch()
    {
        return $this->search;
    }

    /**
     * @param \Tuna\YouTube\Video $video
     *
     * @return array
     */
    private function loadTagsFromYoutubeVideo(Video $video)
    {
        list($title, $artist) = $this->filterYoutubeVideoTitle($video);

        return [
            'title' => $title,
            'artist' => $artist,
            'album' => "{$title} - Single",
            'year' => (new DateTime($video->getReleaseDate()))->format('Y'),
            'genre' => 'Other',
            'track' => 1,
            'cover' => $video->getThumbnail(),
        ];
    }

    /**
     * @param \Tuna\YouTube\Video $video
     *
     * @return array
     */
    private function filterYoutubeVideoTitle(Video $video)
    {
        $tags = preg_split('/[\-\|\:]/', $video->getTitle(), 2, PREG_SPLIT_NO_EMPTY);

        $filter = function ($str) {

            $str = trim(
                preg_replace(
                    '/\[[^\)]+\]|\(|\)|video|official|audio|music|clip|youtube|itunes|google|lyrics?|\||\:|\"\s+/ui',
                    ' ',
                    $str
                ),
                " \t\n\r\0\x0B-"
            );

            return preg_replace(
                [
                    '/\sft.?\s/ui',
                ],
                [' feat. '],
                $str
            );
        };

        return [$filter(last($tags)), $filter(head($tags))];
    }

    /**
     * @param \Illuminate\Support\Collection $results
     *
     * @return array
     */
    private function loadTagsFromSearchResults(Collection $results)
    {
        $track = $results->first();

        // best match algorithm :D
        foreach ($results as $result) {
            if ($track->album === $result->title) {
                $track = $result;
                break;
            }
        }

        return [
            'title' => $track->title,
            'artist' => $track->artist,
            'album' => $track->album,
            'year' => (new DateTime($track->release->date))->format('Y'),
            'genre' => $track->genre,
            'track' => $track->number,
            'cover' => $track->cover,
        ];
    }

    /**
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function loadTrackFromApple($url)
    {
        if (preg_match('/(\?|\&)i=(\d+)/i', $url, $matches)) {

            return $this->loadTagsFromAppleId($matches[2]);

        }

        throw new \Exception;
    }

    /**
     * @param integer $appleId
     *
     * @return array
     */
    private function loadTagsFromAppleId($appleId)
    {
        $wrapper = (json_decode(file_get_contents("https://itunes.apple.com/lookup?id={$appleId}"))->results[0]);

        return [
            'title' => $wrapper->trackName,
            'artist' => $wrapper->artistName,
            'album' => $wrapper->collectionName,
            'year' => (new DateTime($wrapper->releaseDate))->format('Y'),
            'genre' => $wrapper->primaryGenreName,
            'track' => "{$wrapper->trackNumber}/{$wrapper->trackCount}",
            'cover' => str_replace('30x30bb', '600x600bb', $wrapper->artworkUrl30),
        ];
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function filename_sanitizer($string)
    {
        $string = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', ' ', $string);
        $string = mb_ereg_replace('([\.\s]{2,})', ' ', $string);

        return trim($string);
    }

    /**
     * @param array $track
     *
     * @return array
     */
    private function tracksToTags($track)
    {
        $tags = array_only($track, ['title', 'artist', 'album', 'year', 'genre', 'track', 'cover', 'lyrics']);

        if (empty($tags['album'])) {
            $tags['album'] = "{$tags['title']} - Single";
        }

        if (empty($tags['track'])) {
            $tags['track'] = 1;
        }

        $tags['cover'] = $this->getClient()->request('GET', $tags['cover'])->getBody()->getContents();

        return array_filter($tags);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    private function loadTrackFromShazam($url)
    {
        if (preg_match('/track\/(\d+)/i', $url, $matches)) {

            return $this->loadTagsFromShazamId($matches[1]);

        }

        throw new \Exception;
    }

    /**
     * @param integer $shazamId
     * @return array
     * @throws Exception
     */
    private function loadTagsFromShazamId($shazamId)
    {
        $wrapper = (json_decode(
            file_get_contents("https://www.shazam.com/discovery/v4/en-US/IL/web/-/track/{$shazamId}")
        ));

        if (isset($wrapper->stores, $wrapper->stores->apple, $wrapper->stores->apple->trackid)) {
            return $this->loadTagsFromAppleId($wrapper->stores->apple->trackid);
        }

        throw new \Exception;
    }

}