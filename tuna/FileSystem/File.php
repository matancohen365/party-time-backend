<?php namespace Tuna\FileSystem;


use Tuna\ID3\Audio;

class File implements FileInterface
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var \Tuna\ID3\Audio
     */
    protected $tags;

    /**
     * File constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->setFile($file);
    }

    /**
     * @return bool
     */
    public function exists()
    {
        clearstatcache();

        return file_exists($this->getFile());
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    protected function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        return unlink($this->getFile());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toResponse()
    {
        return response()->download(
            $this->getFile(),
            $name = str_replace(
                ['/', '\\'],
                '',
                "{$this->getTags()->getArtist()} - {$this->getTags()->getTitle()}.mp3"
            ),
            [
                'X-Name' => rawurlencode($name),
            ]
        )->deleteFileAfterSend(false);
    }

    /**
     * @return \Tuna\ID3\Audio
     */
    public function getTags()
    {
        if (is_null($this->tags)) {
            $this->tags = new Audio($this);
        }

        return $this->tags;
    }

    /**
     * @param $tags
     *
     * @return $this
     * @throws \Exception
     */
    public function setTags($tags)
    {
        foreach ($tags as $tag => $value) {

            if (method_exists($this->getTags(), $method = 'set'.camel_case($tag))) {
                $this->getTags()->$method($value);
            } else {
                throw new \BadMethodCallException('ID3::'.$method);
            }

        }

        if ( ! $this->getTags()->save()) {
            throw new \Exception('ID3: ' . implode(', ', $this->getTags()->__debugGetErrors()));
        }

        return $this;
    }

    /**
     * @return string
     * @magic
     */
    public function __toString()
    {
        return $this->getFile();
    }
}