<?php namespace App\Metadata;

class NullMetadata implements Metadatable
{
    /**
     * @return array
     */
    public function getMetadata()
    {
        return [
            'title'     => null,
            'artist'    => null,
            'album'     => null,
            'year'      => null,
            'genre'     => null,
            'track'     => null,
            'cover'     => null
        ];
    }
}