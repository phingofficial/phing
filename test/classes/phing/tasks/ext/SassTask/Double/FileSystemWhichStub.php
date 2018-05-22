<?php

declare(strict_types=1);

class FileSystemWhichStub extends UnixFileSystem
{

    private $isWhichSuccessful;

    public function __construct(bool $isWhichSuccessful)
    {
        $this->isWhichSuccessful = $isWhichSuccessful;
    }

    public function which($executable, $fallback = false)
    {
        if ($this->isWhichSuccessful) {
            return $executable;
        }
        return $fallback;
    }
}
