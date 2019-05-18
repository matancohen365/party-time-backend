<?php namespace App\Metadata;

use DateTime;
use Illuminate\Support\Collection;

class iTuneMetadata implements Metadatable
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $results;

    /**
     * iTuneMetadata constructor.
     * @param \Illuminate\Support\Collection $results
     */
    public function __construct(Collection $results)
    {
        $this->results = $results;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $track = $this->results->first();

        // best match algorithm :D
        foreach ($this->results as $result) {
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
}