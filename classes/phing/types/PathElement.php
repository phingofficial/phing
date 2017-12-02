<?php

/**
 * Helper class, holds the nested <code>&lt;pathelement&gt;</code> values.
 *
 * @package phing.types
 */
class PathElement
{
    /** @var array $parts */
    private $parts = [];

    /** @var Path $outer */
    private $outer;

    /**
     * @param Path $outer
     */
    public function __construct(Path $outer)
    {
        $this->outer = $outer;
    }

    /**
     * @param PhingFile $loc
     *
     * @return void
     */
    public function setDir(PhingFile $loc)
    {
        $this->parts = [Path::translateFile($loc->getAbsolutePath())];
    }

    /**
     * @param $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->parts = Path::translatePath($this->outer->getProject(), $path);
    }

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }
}
