<?php

namespace App;

use App\Jobs\DownloadTrack;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Tuna\FileSystem\File;
use Tuna\YouTube\Api as YouTube;

/**
 * Class Track
 * @package App
 *
 * @property string url
 * @property string title
 * @property string artist
 * @property string album
 * @property integer year
 * @property string genre
 * @property integer track
 * @property string cover
 * @property string lyrics
 * @property string status
 * @property \Tuna\YouTube\Video video
 * @property \Tuna\FileSystem\File file
 * @property-read array download
 */
class Track extends Model
{
    // download error
    const STATUS_ERROR = 'ERROR';
    // in queue
    const STATUS_PENDING = 'PENDING';
    // download succeed
    const STATUS_SUCCESS = 'SUCCESS';
    // download in progress
    const STATUS_PROCESSING = 'PROCESSING';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $visible = ['title', 'artist', 'album', 'year', 'genre', 'track', 'cover', 'lyrics', 'download'];

    /**
     * @var array
     */
    protected $appends = ['download'];

    /**
     * @return bool
     */
    public function download()
    {
        if ($this->file->exists()) {
            $this->status = static::STATUS_SUCCESS;

            return $this->save();
        }

        if ($this->inProcess() || $this->isPending()) {
            return true;
        }

        $this->status = static::STATUS_PENDING;

        DownloadTrack::dispatch($this);

        return $this->save();
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->status === static::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function inProcess()
    {
        return $this->status === static::STATUS_PROCESSING;
    }

    /**
     * @param string $file
     * @return \Tuna\FileSystem\File
     */
    public function getFileAttribute($file)
    {
        return new File($file);
    }

    /**
     * @return \Tuna\YouTube\Video|null
     */
    public function getVideoAttribute()
    {
        /** @var \Tuna\YouTube\Api $yt */
        $yt = app(YouTube::class);

        return $yt->getVideoByUrl($this->url);
    }


    /**
     * @param string $album
     * @return string
     */
    public function getAlbumAttribute($album)
    {
        return $album ?: "{$this->title} - Single";
    }

    /**
     * @param int $track
     * @return int
     */
    public function getTrackAttribute($track)
    {
        return $track ?: 1;
    }

    /**
     * @return array
     */
    public function getDownloadAttribute()
    {
        return [
            'status' => $this->status,
            'name' => $this->filenameSanitizer("{$this->artist} - {$this->title}").'.mp3',
            'url' => route('APIv2.download', ['url' => $this->url]),
        ];
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function filenameSanitizer($string)
    {
        $string = mb_ereg_replace('([^\w\s\d\-_~,;\[\]\(\).])', ' ', $string);
        $string = mb_ereg_replace('([\.\s]{2,})', ' ', $string);

        return trim($string);
    }

    /**
     * @return bool
     */
    public function hasMetadata()
    {
        return ! empty($this->title);
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        $metadata = array_only(
            $this->toArray(),
            ['title', 'artist', 'album', 'year', 'genre', 'track', 'cover', 'lyrics']
        );

        $metadata['cover'] = app(Client::class)->request('GET', $metadata['cover'])->getBody()->getContents();

        return array_filter($metadata);
    }
}