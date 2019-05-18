<?php namespace Tuna;

use Cache;
use DateTime;
use Exception;
use Tuna\iTunes\Api as iTunes;
use Tuna\YouTube\Api as YouTube;

class Search implements SearchInterface
{
    /**
     * @var string
     */
    protected $term = '';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $results;

    /**
     * Search constructor.
     *
     * @param string $term
     */
    public function __construct($term = '')
    {
        $this->setTerm($term);
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function getResults()
    {
        if (is_null($this->results)) {
            $this->results = $this->getFreshResults();
        }

        return $this->results;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws Exception
     */
    public function getFreshResults()
    {
        /** @var \Tuna\iTunes\Api $iTunes */
        $iTunes = resolve(iTunes::class);

        /** @var \Tuna\YouTube\Api $youtube */
        $youtube = resolve(YouTube::class);

        if ($video = $youtube->getVideoByUrl($this->getTerm())) {

            $this->setTerm($video->getTerm());

        }

        $tracks = $iTunes
            ->search($this->getTerm())
            ->getResults()
            ->unique($this->getUniqueFilter())
            ->map($this->getMapper())
            ->filter();

        foreach ($tracks as &$track) {

            if (!isset($video)) {
                try {
                    // try by artist & title
                    $track->video = $youtube->firstOrThrow("{$track->artist} - {$track->title}")->getKey();
                } catch (Exception $e) {
                    try {
                        // try by term
                        $track->video = $youtube->firstOrThrow($this->getTerm())->getKey();
                    } catch (Exception $e) {
                        // cant find video
                        unset($track);
                        continue;
                    }
                }

            } else {
                $track->video = $video->getKey();
            }

            $track->hash = spl_object_hash($track);

            Cache::forever("track.{$track->hash}", $track); // $model->save();

        }

        return $tracks;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param string $term
     */
    public function setTerm($term)
    {
        $this->term = trim($term, " \t\n\r\0\x0B.-");
    }

    /**
     * @return \Closure
     */
    private function getUniqueFilter()
    {
        return function ($wrapper) {
            return md5(strtolower("{$wrapper->artistName} - {$wrapper->trackName} - {$wrapper->collectionName}"));
        };
    }

    /**
     * @return \Closure
     */
    private function getMapper()
    {
        return function ($wrapper) {
            try {
                return (object)[
                    'title' => $wrapper->trackName,
                    'artist' => $wrapper->artistName,
                    'album' => $wrapper->collectionName,
                    'release' => (new DateTime($wrapper->releaseDate)),
                    'cover' => str_replace('30x30bb', '600x600bb', $wrapper->artworkUrl30),
                    'genre' => $wrapper->primaryGenreName,
                    'disc' => "{$wrapper->discNumber}/{$wrapper->discCount}",
                    'number' => "{$wrapper->trackNumber}/{$wrapper->trackCount}",
                    'duration' => number_format($wrapper->trackTimeMillis / 1000 / 60, 2),
                    'preview' => $wrapper->previewUrl,
                ];

            } catch (Exception $e) {
            }
        };
    }
}