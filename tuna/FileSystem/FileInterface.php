<?php namespace Tuna\FileSystem;


interface FileInterface
{
    /**
     * @return string
     */
    public function getFile();

    /**
     * @return bool
     */
    public function exists();

    /**
     * @return bool
     */
    public function delete();
}