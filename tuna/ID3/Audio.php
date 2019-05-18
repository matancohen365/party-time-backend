<?php namespace Tuna\ID3;

use getID3;
use getid3_writetags;
use Tuna\FileSystem\FileInterface;

class Audio
{
    /**
     * @var \Tuna\FileSystem\FileInterface
     */
    protected $file;

    /**
     * @var \getID3
     */
    protected $reader;

    /**
     * @var \getid3_writetags
     */
    protected $writer;

    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $artist;

    /**
     * @var string
     */
    protected $album;

    /**
     * @var integer
     */
    protected $year;

    /**
     * id3v1: track
     * id2v2: track_number
     *
     * @var string
     */
    protected $track;

    /**
     * @var string
     */
    protected $genre;

    /**
     * id3v1: none
     * id2v2: attached_picture
     *
     * @var string
     */
    protected $cover;

    /**
     * id3v1: none
     * id2v2: unsychronised_lyric
     *
     * @var string
     */
    protected $lyrics;

    /**
     * AudioFile constructor.
     *
     * @param \Tuna\FileSystem\FileInterface $file
     */
    public function __construct(FileInterface $file)
    {
        $this->setFile($file);
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->clearTags();

        if ($this->writeID3v1Tags()) {

            $this->getWriter()->remove_other_tags = false;

            return $this->writeID3v2Tags();
        }

        return false;
    }

    public function __debugGetErrors()
    {
        return $this->getWriter()->errors;
    }

    /**
     * @return bool
     */
    private function clearTags()
    {
        $this->getWriter()->remove_other_tags = true;

        return $this->getWriter()->DeleteTags(['id3v1', 'id3v2']);
    }

    /**
     * @return \getid3_writetags
     */
    protected function getWriter()
    {
        if (is_null($this->writer)) {

            $this->getReader(); // the only way to fix bug in the id3Tag package.

            $this->writer = new getid3_writetags();

            $this->writer->filename = $this->getFilename();
            $this->writer->tag_encoding = $this->getEncoding();

        }

        return $this->writer;
    }

    /**
     * @return \getID3
     */
    protected function getReader()
    {
        if (is_null($this->reader)) {

            $this->reader = new getID3();

        }

        return $this->reader;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->getFile()->getFile();
    }

    /**
     * @return \Tuna\FileSystem\FileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param \Tuna\FileSystem\FileInterface $file
     */
    protected function setFile(FileInterface $file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @return bool
     */
    private function writeID3v1Tags()
    {
        $this->getWriter()->tagformats = ['id3v1'];

        $this->getWriter()->tag_data = [
            'title' => [$this->getTitle()],
            'artist' => [$this->getArtist()],
            'album' => [$this->getAlbum()],
            'year' => [$this->getYear()],
            'track' => [$this->getTrack()],
            'genre' => [$this->getGenre()],
        ];

        return $this->getWriter()->WriteTags();
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param string $artist
     *
     * @return $this
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @param string $album
     *
     * @return $this
     */
    public function setAlbum($album)
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param integer $year
     *
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return string
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param string $track
     *
     * @return $this
     */
    public function setTrack($track)
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     *
     * @return $this
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * @return bool
     */
    private function writeID3v2Tags()
    {
        $this->getWriter()->tagformats = ['id3v2.3'];

        $this->getWriter()->tag_data = [
            'title' => [$this->getTitle()],
            'artist' => [$this->getArtist()],
            'album' => [$this->getAlbum()],
            'year' => [$this->getYear()],
            'track_number' => [$this->getTrack()],
            'genre' => [$this->getGenre()],
            'attached_picture' => [
                [
                    'data' => $this->getCover(),
                    'picturetypeid' => 0x03,
                    'description' => 'Front cover',
                    'mime' => getimagesizefromstring($this->getCover())['mime'],
                ],
            ],
            'unsychronised_lyric' => [$this->getLyrics()],
        ];

        return $this->getWriter()->WriteTags();
    }

    /**
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * @param string $cover
     *
     * @return $this
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * @return string
     */
    public function getLyrics()
    {
        return $this->lyrics;
    }

    /**
     * @param string $lyrics
     *
     * @return $this
     */
    public function setLyrics($lyrics)
    {
        $this->lyrics = $lyrics;

        return $this;
    }
}