<?php namespace App\Observers;

use App\Track;
use App\Metadata\MetadataFactory;

class TrackObserver
{
    /**
     * Listen to the Track created event.
     *
     * @param \App\Track $track
     * @return void
     */
    public function created(Track $track)
    {
        dd(MetadataFactory::make($track)->getMetadata());

        $track->fill(array_only(
            MetadataFactory::make($track)->getMetadata(),
            ['title', 'artist', 'album', 'year', 'genre', 'track', 'cover', 'lyrics']
        ))->save();
    }
}