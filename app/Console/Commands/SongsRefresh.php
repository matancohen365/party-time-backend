<?php

namespace App\Console\Commands;

use getID3;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Tuna\FileSystem\File;
use Tuna\iTunes\Api as iTunes;
use Tuna\MusixMatch\Api as MusixMatch;
use Tuna\Search;

class SongsRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'songs:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all song in a the device';

    /**
     * @var \Tuna\Search
     */
    private $search;

    /**
     * @var \getID3
     */
    private $id3;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \Tuna\MusixMatch\Api
     */
    private $musix;

    /**
     * @var \Tuna\iTunes\Api
     */
    private $itunes;

    /**
     * SongsRefresh constructor.
     *
     * @param \Tuna\Search         $search
     * @param \getID3              $id3
     * @param \GuzzleHttp\Client   $client
     * @param \Tuna\MusixMatch\Api $musix
     * @param \Tuna\iTunes\Api     $itunes
     */
    public function __construct(Search $search, getID3 $id3, Client $client, MusixMatch $musix, iTunes $itunes)
    {
        parent::__construct();

        $this->search = $search;
        $this->id3 = $id3;
        $this->client = $client;
        $this->musix = $musix;
        $this->itunes = $itunes;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $songs = glob('C:/music/*.mp3');
        $count = sizeof($songs);
        $sleep = 5;
        $filename_sanitizer = function($string) {
            $string = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", ' ', $string);
            $string = mb_ereg_replace("([\.\s]{2,})", ' ', $string);

            return trim($string);
        };

        foreach( $songs as $song ) {

            $this->info("{$count} songs / ".number_format($count * $sleep / 60)." mins");
            $count--;

            try {
                $filename = last(explode('/', $song));

                list($artist, $title) = explode(' - ', str_replace('.mp3', '', $filename));

                if( empty($artist) || empty($title) )
                    throw new \Exception;
            } catch( \Exception $e ) {
                $this->info("File {$song} not in order '%artist% - %title%");
                continue;
            }

            $name = "{$artist} - {$title}";

            $filename = $filename_sanitizer($name);

            $new = new File("C:/music/new/{$filename}.mp3");

            if( $new->exists() ) {
                rename($song, "C:/music/old/{$filename}.mp3"); // old
                continue;
            }

            $this->info("Refreshing '{$name}'");

            sleep($sleep);

            if( $track = $this->search($name) ) {

                copy($song, $new->getFile());

                rename($song, "C:/music/old/{$filename}.mp3"); // old

                $new->setTags([
                                  'title'  => array_get($track, 'title'),
                                  'artist' => array_get($track, 'artist'),
                                  'album'  => array_get($track, 'album', title_case(array_get($track, 'title').' - single')),
                                  'year'   => array_get($track, 'release'),
                                  'genre'  => array_get($track, 'genre'),
                                  'track'  => array_get($track, 'number', "1/1"),
                                  'cover'  => $this->client->request('GET', array_get($track, 'cover'))->getBody()->getContents(),
                                  'lyrics' => $this->musix->getLyrics($name),
                              ]);

                $this->alert("{$artist} - {$title} Refreshed!");

            } else {
                $this->alert("Cant find tags for {$artist} - {$title}");
            }
        }

        $this->info("Bye.");
    }

    /**
     * @param string $query
     *
     * @return array
     */
    protected function search($query)
    {
        return $this->itunes
            ->search($query)
            ->getResults()
            ->unique(function($wrapper) {
                return md5(strtolower("{$wrapper->artistName} - {$wrapper->trackName} - {$wrapper->collectionName}"));
            })
            ->map(function($wrapper) {

                try {
                    return [
                        'title'   => $wrapper->trackName,
                        'artist'  => $wrapper->artistName,
                        'album'   => $wrapper->collectionName,
                        'release' => (new \DateTime($wrapper->releaseDate))->format('Y'),
                        'cover'   => str_replace('30x30bb', '600x600bb', $wrapper->artworkUrl30),
                        'genre'   => $wrapper->primaryGenreName,
                        'number'  => "{$wrapper->trackNumber}/{$wrapper->trackCount}",
                    ];

                } catch( \Exception $e ) {
                }
            })
            ->filter()->first();
    }
}
