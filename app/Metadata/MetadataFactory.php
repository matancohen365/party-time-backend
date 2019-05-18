<?php namespace App\Metadata;

use App\Track;
use Tuna\SearchInterface;

class MetadataFactory
{
    /**
     * @param \App\Track $track
     * @return \App\Metadata\Metadatable
     */
    public static function make(Track $track)
    {
        if(empty($track->video)) {
            return new NullMetadata;
        }

        // iTune
        try {

            /** @var \Tuna\SearchInterface $search */
            $search = app(SearchInterface::class);

            $search->setTerm($track->video->getTerm());

            dd($track->video->getTerm(), $search->getResults());

            if( ! $search->getResults()->isEmpty()) {
                return new iTuneMetadata($search->getResults());
            }

        } catch (\Exception $e) {}

        // Youtube
        return new YoutubeMetadata($track->video);
    }
}