<?php namespace Tuna\Charts;


class Single implements SingleInterface
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Single constructor.
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes($attributes);
    }

    /**
     * @return string
     */
    public function getCover()
    {
        return $this->getAttribute('cover');
    }

    /**
     * @param string key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return array_get($this->getAttributes(), $key, $default);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->getAttribute('position');
    }

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->getAttribute('term', "{$this->getArtist()} - {$this->getTitle()}");
    }

    /**
     * @return string
     */
    public function getArtist()
    {
        return $this->getAttribute('artist');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttribute('title');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setAttribute($key, $value)
    {
        array_set($this->attributes, $key, $value);
    }
}