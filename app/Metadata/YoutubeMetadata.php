<?php namespace App\Metadata;

use DateTime;
use Tuna\YouTube\Video;

class YoutubeMetadata implements Metadatable
{
    /**
     * @var Video
     */
    protected $video;

    /**
     * YoutubeMetadata constructor.
     * @param Video $video
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        list($title, $artist) = $this->getTitleAndArtist();

        return [
            'title' => $title,
            'artist' => $artist,
            'album' => "{$title} - Single",
            'year' => (new DateTime($this->video->getReleaseDate()))->format('Y'),
            'genre' => 'Other',
            'track' => 1,
            'cover' => $this->video->getThumbnail(),
        ];
    }

    protected function getTitleAndArtist()
    {
        $tags = preg_split('/[\-\|\:]/', $this->video->getTitle(), 2, PREG_SPLIT_NO_EMPTY);

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
}